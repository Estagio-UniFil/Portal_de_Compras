<?php
session_start();
error_reporting(0);
include('includes/config.php');
require_once __DIR__ . '/load_env.php';

define('ASAAS_API_KEY', getenv('ASAAS_API_KEY'));
define('ASAAS_API_URL', getenv('ASAAS_API_URL'));

// Função para gerar CPF válido aleatório
function gerarCPF() {
    $n = [];
    for ($i = 0; $i < 9; $i++) {
        $n[$i] = rand(0, 9);
    }

    $d1 = 0;
    for ($i = 0, $peso = 10; $i < 9; $i++, $peso--) {
        $d1 += $n[$i] * $peso;
    }
    $d1 = 11 - ($d1 % 11);
    if ($d1 >= 10) $d1 = 0;

    $d2 = 0;
    for ($i = 0, $peso = 11; $i < 9; $i++, $peso--) {
        $d2 += $n[$i] * $peso;
    }
    $d2 += $d1 * 2;
    $d2 = 11 - ($d2 % 11);
    if ($d2 >= 10) $d2 = 0;

    return implode('', $n) . $d1 . $d2;
}

// Verifica se o usuário está logado
if (strlen($_SESSION['login']) == 0) {
    header('location:login.php');
    exit();
}

if (isset($_POST['submit'])) {
    $paymethod = $_POST['paymethod'];
    $userId = $_SESSION['id'];

    // Busca dados do usuário
    $stmt = $con->prepare("SELECT name, email, asaas_customer_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($nome, $email, $asaasCustomerId);
    $stmt->fetch();
    $stmt->close();

    // Se o cliente não existir no Asaas, cria agora
    if (empty($asaasCustomerId)) {
        $cpf = gerarCPF();
        $dadosCliente = [
            'name' => $nome,
            'email' => $email,
            'cpfCnpj' => $cpf
        ];

        $curl = curl_init(ASAAS_API_URL . '/customers');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($dadosCliente),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: Portal de Compras',
                'access_token: ' . ASAAS_API_KEY
            ],
        ]);
        $respCliente = curl_exec($curl);
        $clienteData = json_decode($respCliente, true);
        curl_close($curl);

        if (isset($clienteData['id'])) {
            $asaasCustomerId = $clienteData['id'];
            // Salva no banco
            $stmt = $con->prepare("UPDATE users SET asaas_customer_id = ? WHERE id = ?");
            $stmt->bind_param("si", $asaasCustomerId, $userId);
            $stmt->execute();
            $stmt->close();
        } else {
            echo json_encode(['erro' => 'Falha ao criar cliente', 'resposta' => $clienteData]);
            exit;
        }
    }

    // Calcula valor dos pedidos não pagos
    $total = 0.0;
   $sql = "
    SELECT o.quantity, p.productPrice, p.shippingCharge
    FROM orders o
    JOIN products p ON o.productId = p.id
    WHERE o.userId = ? AND o.paymentMethod IS NULL
";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
   while ($row = $result->fetch_assoc()) {
    $valorProduto = $row['quantity'] * $row['productPrice'];
    $valorFrete = $row['quantity'] * $row['shippingCharge'];
    $total += $valorProduto + $valorFrete;
}

    $stmt->close();

    if ($total <= 0) {
        echo "Nenhum item para cobrança.";
        exit;
    }

    $validPaymentMethods = ['Boleto' => 'BOLETO', 'PIX' => 'PIX'];
    if (!array_key_exists($paymethod, $validPaymentMethods)) {
        $paymethod = 'Boleto';
    }
    $billingType = $validPaymentMethods[$paymethod];

    // Cria a cobrança
    $dadosCobranca = [
        'customer' => $asaasCustomerId,
        'billingType' => $billingType,
        'value' => number_format($total, 2, '.', ''),
        'dueDate' => date('Y-m-d', strtotime('+3 days'))
    ];

    $curl = curl_init(ASAAS_API_URL . '/payments');
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($dadosCobranca),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'User-Agent: Portal de Compras',
            'access_token: ' . ASAAS_API_KEY
        ],
    ]);
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $pagamento = json_decode($response, true);

    // Verifica se o pagamento foi criado com sucesso
    if ($http_code == 200 && isset($pagamento['invoiceUrl'])) {
    // Marca os pedidos como pagos com o método escolhido
    $update = $con->prepare("
        UPDATE orders
        SET paymentMethod = ?
        WHERE userId = ? AND paymentMethod IS NULL
    ");
    $update->bind_param("si", $paymethod, $userId);
    $update->execute();
    $update->close();

    // ✅ Zera o carrinho apenas após o pagamento ser criado
    unset($_SESSION['cart']);
    $_SESSION['msg_success'] = "Pedido realizado com sucesso!";

    // Redireciona para o link do boleto ou PIX
    header("Location: " . $pagamento['invoiceUrl']);
    exit;
}

    // Exibe erro amigável
    echo "<h3>Erro ao processar o pagamento.</h3>";
    if (isset($pagamento['errors'])) {
        foreach ($pagamento['errors'] as $erro) {
            echo "<p><strong>{$erro['description']}</strong></p>";
        }
    } else {
        echo "<pre>";
        print_r($pagamento);
        echo "</pre>";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Portal de Compras | Método de Pagamento</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/red.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body class="cnt-home">

<!-- Cabeçalho -->
<?php include('includes/top-header.php');?>
<?php include('includes/main-header.php');?>
<?php include('includes/menu-bar.php');?>



<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <div class="breadcrumb-inner">
            <ul class="list-inline list-unstyled">
                <li><a href="home.html">Home</a></li>
                <li class='active'>Método de Pagamento</li>
            </ul>
        </div>
    </div>
</div>

<!-- Conteúdo principal -->
<div class="body-content outer-top-bd">
    <div class="container">
        <div class="checkout-box faq-page inner-bottom-sm">
            <div class="row">
                <div class="col-md-12">
                    <h2>Escolha o método de pagamento</h2>

                    <?php if (isset($mensagem)) {
                        echo "<div style='padding:20px; background:#dff0d8; color:#3c763d; font-size:18px; margin:20px 0;'>$mensagem</div>";
                    } ?>

                    <div class="panel-group checkout-steps" id="accordion">
                        <div class="panel panel-default checkout-step-01">
                            <div class="panel-heading">
                                <h4 class="unicase-checkout-title">
                                    <a data-toggle="collapse" class="" data-parent="#accordion" href="#collapseOne">
                                        Selecione seu método de pagamento
                                    </a>
                                </h4>
                            </div>
                            <div id="collapseOne" class="panel-collapse collapse in">
                                <div class="panel-body">
                                    <form name="payment" method="post">
                                        <label><input type="radio" name="paymethod" value="Boleto" checked="checked"> Boleto</label><br>
                                        <label><input type="radio" name="paymethod" value="PIX"> PIX</label><br>
                                        <button type="submit" name="submit" class="btn btn-primary">Pagar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.checkout-steps -->
                </div>
            </div><!-- /.row -->
        </div><!-- /.checkout-box -->



    </div><!-- /.container -->
</div><!-- /.body-content -->

<!-- Rodapé -->
<?php include('includes/footer.php');?>

<!-- Scripts -->
<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/scripts.js"></script>

	<!-- For demo purposes – can be removed on production -->
	
	<script src="switchstylesheet/switchstylesheet.js"></script>
	
	<script>
    $(document).ready(function(){ 
        $(".changecolor").switchstylesheet({seperator: "color"});
        $('.show-theme-options').click(function(){
            $(this).parent().toggleClass('open');
            return false;
        });
    });

    $(window).bind("load", function() {
        $('.show-theme-options').delay(2000).trigger('click');
    });
</script>

<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<?php if (!empty($mensagem_toast)) : ?>
<script>
    $(document).ready(function () {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            timeOut: 4000,
            positionClass: "toast-top-right"
        };
        toastr.success("<?php echo addslashes($mensagem_toast); ?>");
    });
</script>
<?php endif; ?>


</body>
</html>
