<?php
session_start();
include('includes/config.php');


if (isset($_POST['ordersubmit'])) {
    $userId = intval($_SESSION['id']);
    $showToast = true;
    

    if (!empty($_SESSION['cart'])) {
        // Limpa pedidos pendentes antigos do usuário
        $con->query("DELETE FROM pending_orders WHERE userId = $userId");
        
    
    }
}

class Users {
    private $con;
    private $userId;

    public function __construct($db, $userId) {
        $this->con = $db;
        $this->userId = $userId;
    }

    // Obtém os dados do usuário
    public function getUserDetails() {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Atualiza endereço de cobrança
    public function updateBillingAddress($address, $state, $city, $pincode) {
		$pincode = preg_replace('/\D/', '', $pincode);
		if (strlen($pincode) === 8) {
			$pincode = substr($pincode, 0, 5) . '-' . substr($pincode, 5, 3);
		}
        $stmt = $this->con->prepare("UPDATE users SET billingAddress=?, billingState=?, billingCity=?, billingPincode=? WHERE id=?");
        $stmt->bind_param("ssssi", $address, $state, $city, $pincode, $this->userId);
        return $stmt->execute();
    }

    // Atualiza endereço de envio
	public function updateShippingAddress($address, $state, $city, $pincode) {
		// Garante que o CEP está no formato 00000-000
		$pincode = preg_replace('/\D/', '', $pincode);
		if (strlen($pincode) === 8) {
			$pincode = substr($pincode, 0, 5) . '-' . substr($pincode, 5, 3);
		}
		$stmt = $this->con->prepare("UPDATE users SET shippingAddress=?, shippingState=?, shippingCity=?, shippingPincode=? WHERE id=?");
		$stmt->bind_param("ssssi", $address, $state, $city, $pincode, $this->userId);
		return $stmt->execute();
	}
	}

// Criar instância da classe Users
if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];
    $userProfile = new Users($con, $userId);
} else {
    $userProfile = null;
}

// Atualizar endereço de cobrança
if (isset($_POST['update'])) {
    // Dados cobrança
    $billingAddress = $_POST['billingaddress'] ?? '';
    $billingState = $_POST['billingstate'] ?? '';
    $billingCity = $_POST['billingcity'] ?? '';
    $billingPincode = $_POST['billingpincode'] ?? '';

    // Dados envio
    $shippingAddress = $_POST['shippingaddress'] ?? '';
    $shippingState = $_POST['shippingstate'] ?? '';
    $shippingCity = $_POST['shippingcity'] ?? '';
    $shippingPincode = $_POST['shippingpincode'] ?? '';

    // Atualiza cobrança
    $successBilling = $userProfile->updateBillingAddress($billingAddress, $billingState, $billingCity, $billingPincode);
    // Atualiza envio
    $successShipping = $userProfile->updateShippingAddress($shippingAddress, $shippingState, $shippingCity, $shippingPincode);

    if ($successBilling && $successShipping) {
        $_SESSION['msg_success'] = "Endereços de Cobrança e Envio atualizados com sucesso!";
    } elseif ($successBilling) {
        $_SESSION['msg_success'] = "Endereço de Cobrança atualizado com sucesso! Erro no Endereço de Envio.";
    } elseif ($successShipping) {
        $_SESSION['msg_success'] = "Endereço de Envio atualizado com sucesso! Erro no Endereço de Cobrança.";
    } else {
        $_SESSION['msg_error'] = "Erro ao atualizar os endereços.";
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}




error_reporting(0);
include('includes/config.php');

if(isset($_POST['submit'])) {
    if (!empty($_SESSION['cart'])) {
        foreach ($_POST['quantity'] as $key => $val) {
            if ($val == 0) {
                unset($_SESSION['cart'][$key]);
            } else {
                $_SESSION['cart'][$key]['quantity'] = $val;
            }
        }
        $_SESSION['msg_success'] = "Seu carrinho foi atualizado com sucesso!";
        header("Location: my-cart.php");
        exit();
    }
}

// Remover itens selecionados do carrinho
if (isset($_POST['remove_selected']) && isset($_POST['remove_code']) && !empty($_SESSION['cart'])) {
    foreach ($_POST['remove_code'] as $key) {
        unset($_SESSION['cart'][$key]);
    }
    $_SESSION['msg_success'] = "Itens removidos do carrinho com sucesso!";
    header("Location: my-cart.php");
    exit();
}

// Submeter pedido
if (isset($_POST['ordersubmit'])) {
    if (strlen($_SESSION['login']) == 0) {
        header('location:login.php');
        exit();
    }

    if (!empty($_SESSION['cart'])) {
        $userId = intval($_SESSION['id']);
        $stmt = $con->prepare("INSERT INTO orders(userId, productId, quantity) VALUES (?, ?, ?)");

        foreach ($_SESSION['cart'] as $productId => $item) {
            $quantity = intval($item['quantity']);
            $stmt->bind_param("iii", $userId, $productId, $quantity);
            $stmt->execute();
        }

    } else {
        $_SESSION['msg_error'] = "Seu carrinho está vazio!";
        header('location:payment-method.php');
        exit();
    }
}

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

// Função para atualizar cliente Asaas
function atualizarClienteAsaas($customerId, $novoNome) {
    $url = "https://api-sandbox.asaas.com/v3/customers/" . $customerId;
    $dados = json_encode(['name' => $novoNome]);

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $dados,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'access_token: ' . ASAAS_API_KEY
        ],
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Retorna true se atualização OK (código 200)
    return ($http_code === 200);
}

// Verifica se o usuário está logado
if (strlen($_SESSION['login']) == 0) {
    header('location:login.php');
    exit();
}

if (isset($_POST['submit'])) {
    $paymethod = $_POST['paymethod'];
    $userId = $_SESSION['id'];
    $nomeAtualizado = trim($_POST['nome']);

    // Busca dados atuais do usuário
    $stmt = $con->prepare("SELECT name, email, asaas_customer_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($nomeAtual, $email, $asaasCustomerId);
    $stmt->fetch();
    $stmt->close();

    // Atualiza nome no banco se necessário
    if (!empty($nomeAtualizado) && $nomeAtualizado !== $nomeAtual) {
        $stmt = $con->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $nomeAtualizado, $userId);
        $stmt->execute();
        $stmt->close();

        // Atualiza a variável para usar depois
        $nomeAtual = $nomeAtualizado;
    }

    error_log("Nome do formulário: " . $nomeAtualizado);
    error_log("Nome usado para atualização: " . $nomeAtual);

    $cpf = gerarCPF(); // Supondo que gera um CPF válido

    // Se não tem asaas_customer_id, cria cliente no Asaas
    if (empty($asaasCustomerId)) {
        $dadosCliente = [
            'name' => $nomeAtual,
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
            $stmt = $con->prepare("UPDATE users SET asaas_customer_id = ? WHERE id = ?");
            $stmt->bind_param("si", $asaasCustomerId, $userId);
            $stmt->execute();
            $stmt->close();
        } else {
            echo json_encode(['erro' => 'Falha ao criar cliente', 'resposta' => $clienteData]);
            exit;
        }
    }

    // Atualiza nome do cliente no Asaas caso já tenha ID
    if (!empty($asaasCustomerId)) {
        $resultadoAtualizacao = atualizarClienteAsaas($asaasCustomerId, $nomeAtual);
        if (!$resultadoAtualizacao) {
            error_log("Falha na atualização do cliente no Asaas para o usuário $userId");
            echo json_encode(['erro' => 'Falha ao atualizar cliente no Asaas. Verifique os logs.']);
            exit;
        }
    }

    sleep(3); // Dá um tempo para o Asaas processar

    // Calcula total dos pedidos sem pagamento
    $total = 0.0;
    $sql = "
        SELECT o.quantity, p.productPrice
        FROM orders o
        JOIN products p ON o.productId = p.id
        WHERE o.userId = ? AND o.paymentMethod IS NULL
    ";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $total += $row['quantity'] * $row['productPrice'];
    }
    $stmt->close();

    if ($total <= 0) {
        echo "Nenhum item para cobrança.";
        exit;
    }

    // Valida método de pagamento
    $validPaymentMethods = ['Boleto' => 'BOLETO', 'PIX' => 'PIX'];
    if (!array_key_exists($paymethod, $validPaymentMethods)) {
        $paymethod = 'Boleto';
    }
    $billingType = $validPaymentMethods[$paymethod];

    // Dados para criar cobrança no Asaas
    $dadosCobranca = [
        'name' => "Cobrança para $nomeAtual",  // Corrigido para usar $nomeAtual
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

    header('Content-Type: application/json');
    echo json_encode([
        'http_code' => $http_code,
        'request' => $dadosCobranca,
        'response' => $pagamento
    ], JSON_PRETTY_PRINT);
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
	<head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
		<!-- Meta -->
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">
	    <meta name="keywords" content="MediaCenter, Template, eCommerce">
	    <meta name="robots" content="all">

	    <title>Meu Carrinho</title>
	    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
	    <link rel="stylesheet" href="assets/css/main.css">
	    <link rel="stylesheet" href="assets/css/red.css">
	    <link rel="stylesheet" href="assets/css/owl.carousel.css">
		<link rel="stylesheet" href="assets/css/owl.transitions.css">
		<!--<link rel="stylesheet" href="assets/css/owl.theme.css">-->
		<link href="assets/css/lightbox.css" rel="stylesheet">
		<link rel="stylesheet" href="assets/css/animate.min.css">
		<link rel="stylesheet" href="assets/css/rateit.css">
		<link rel="stylesheet" href="assets/css/bootstrap-select.min.css">

		<!-- Demo Purpose Only. Should be removed in production -->
		<link rel="stylesheet" href="assets/css/config.css">

		<link href="assets/css/green.css" rel="alternate stylesheet" title="Green color">
		<link href="assets/css/blue.css" rel="alternate stylesheet" title="Blue color">
		<link href="assets/css/red.css" rel="alternate stylesheet" title="Red color">
		<link href="assets/css/orange.css" rel="alternate stylesheet" title="Orange color">
		<link href="assets/css/dark-green.css" rel="alternate stylesheet" title="Darkgreen color">
		<!-- Demo Purpose Only. Should be removed in production : END -->

		
		<!-- Icons/Glyphs -->
		<link rel="stylesheet" href="assets/css/font-awesome.min.css">

        <!-- Fonts --> 
		<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet' type='text/css'>
		
		<!-- Favicon -->
		<link rel="shortcut icon" href="assets/images/favicon.ico">

		

	</head>
    <body class="cnt-home">

<?php if (isset($_SESSION['msg_success'])): ?>
<script>
    $(document).ready(function() {
        toastr.success("<?php echo addslashes($_SESSION['msg_success']); ?>", null, {timeOut: 2000});
    });
</script>
<?php unset($_SESSION['msg_success']); endif; ?>

<?php if (isset($_SESSION['msg_error'])): ?>
<script>
    $(document).ready(function() {
        toastr.error("<?php echo addslashes($_SESSION['msg_error']); ?>", null, {timeOut: 2000});
    });
</script>
<?php unset($_SESSION['msg_error']); endif; ?>




<script>
$(document).ready(function () {
  $('.update-cart-btn').click(function () {
    const productId = $(this).data('id');
    const quantity = $(`.quantity-input[data-id="${productId}"]`).val();

    $.post('update-cart.php', { id: productId, quantity: quantity }, function (data) {
      if (data.success) {
        toastr.success(data.msg);
        setTimeout(() => {
          location.reload(); // ou você pode atualizar somente o total sem reload
        }, 1000);
      } else {
        toastr.error(data.msg);
      }
    }, 'json');
  });
});
</script>
	
		<!-- ============================================== HEADER ============================================== -->
<header class="header-style-1">
<?php include('includes/top-header.php');?>
<?php include('includes/main-header.php');?>
<?php include('includes/menu-bar.php');?>
</header>
<!-- ============================================== HEADER : END ============================================== -->
<div class="breadcrumb">
	<div class="container">
		<div class="breadcrumb-inner">
			<ul class="list-inline list-unstyled">
				<li><a href="#">Home</a></li>
				<li class='active'>Carrinho de Compras</li>
			</ul>
		</div><!-- /.breadcrumb-inner -->
	</div><!-- /.container -->
</div><!-- /.breadcrumb -->

<div class="body-content outer-top-xs">
	<div class="container">
		<div class="row inner-bottom-sm">
			<div class="shopping-cart">
				<div class="col-md-12 col-sm-12 shopping-cart-table ">
	<div class="table-responsive">
<form name="cart" method="post">	
<?php
if(!empty($_SESSION['cart'])){
	?>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th class="cart-romove item">Remover</th>
					<th class="cart-description item">Imagem</th>
					<th class="cart-product-name item">Nome do Produto</th>
			
					<th class="cart-qty item">Quantidade</th>
					<th class="cart-sub-total item">Preço por Unidade</th>
					<th class="cart-sub-total item">Taxa de Frete</th>
					<th class="cart-total last-item">Total Geral</th>
				</tr>
			</thead><!-- /thead -->
			<tfoot>
				<tr>
					<td colspan="7">
						<div class="shopping-cart-btn">
							<span class="">
								<a href="index.php" class="btn btn-upper btn-primary outer-left-xs">Continuar Comprando</a>
								<div class="pull-right">
                                <input type="submit" name="submit" value="Atualizar Carrinho" class="btn btn-upper btn-primary outer-right-xs" style="margin-right: 10px;">
                            <input type="submit" name="remove_selected" value="Remover Selecionados" class="btn btn-upper btn-primary outer-right-xs">
                            </div>



							</span>
						</div><!-- /.shopping-cart-btn -->
					</td>
				</tr>
			</tfoot>
			<tbody>
 <?php
 $pdtid=array();
    $sql = "SELECT * FROM products WHERE id IN(";
			foreach($_SESSION['cart'] as $id => $value){
			$sql .=$id. ",";
			}
			$sql=substr($sql,0,-1) . ") ORDER BY id ASC";
			$query = mysqli_query($con,$sql);
			$totalprice=0;
			$totalqunty=0;
			if(!empty($query)){
			while($row = mysqli_fetch_array($query)){
				$quantity=$_SESSION['cart'][$row['id']]['quantity'];
				$subtotal= $_SESSION['cart'][$row['id']]['quantity']*$row['productPrice']+$row['shippingCharge'];
				$totalprice += $subtotal;
				$_SESSION['qnty']=$totalqunty+=$quantity;

				array_push($pdtid,$row['id']);
//print_r($_SESSION['pid'])=$pdtid;exit;
	?>

				<tr>
					<td class="romove-item"><input type="checkbox" name="remove_code[]" value="<?php echo htmlentities($row['id']);?>" /></td>
					<td class="cart-image">
						<a class="entry-thumbnail" href="detail.html">
						    <img src="admin/productimages/<?php echo $row['id'];?>/<?php echo $row['productImage1'];?>" alt="" width="114" height="146">
						</a>
					</td>
					<td class="cart-product-name-info">
						<h4 class='cart-product-description'><a href="product-details.php?pid=<?php echo htmlentities($pd=$row['id']);?>" ><?php echo $row['productName'];

$_SESSION['sid']=$pd;
						 ?></a></h4>
						<div class="row">
    <div class="col-sm-4">
        <div class="rating rateit-small"></div>

        <?php
        $rt = mysqli_query($con, "select * from productreviews where productId='$pd'");
        $num = mysqli_num_rows($rt);
        {
        ?>
            <div class="reviews" style="white-space: nowrap; display: inline-block; font-size: 14px; color: #555;">
                (<?php echo htmlentities($num); ?> Reviews)
            </div>
        <?php } ?>
    </div>

    <div class="col-sm-8">
        <!-- Qualquer outro conteúdo que esteja aqui -->
    </div>
</div><!-- /.row -->

						
					</td>
					<td class="cart-product-quantity">
  <div class="quant-input d-flex align-items-center">
  <input type="number" min="0" class="form-control" name="quantity[<?php echo $row['id']; ?>]" value="<?php echo $_SESSION['cart'][$row['id']]['quantity']; ?>" />
  </div>
</td>
					<!-- Preço unitário do produto -->
<td class="cart-product-sub-total" style="vertical-align: middle;">
    <span class="cart-sub-total-price" style="white-space: nowrap;">
        R$ <?php echo number_format($row['productPrice'], 2, ',', '.'); ?>
    </span>
</td>


<!-- Valor do frete -->
<td class="cart-product-shipping">
    <span class="cart-shipping-price">
        R$ <?php echo number_format($row['shippingCharge'], 2, ',', '.'); ?>
    </span>
</td>

<!-- Total geral do item (quantidade * preço + frete) -->
<td class="cart-product-grand-total">
    <span class="cart-grand-total-price">
        R$ <?php echo number_format($_SESSION['cart'][$row['id']]['quantity'] * $row['productPrice'] + $row['shippingCharge'], 2, ',', '.'); ?>
    </span>
</td>
</tr>

				<?php } }
$_SESSION['pid']=$pdtid;
				?>
				
			</tbody><!-- /tbody -->
		</table><!-- /table -->
		
	</div>
</div><!-- /.shopping-cart-table -->			<div class="col-md-4 col-sm-12 estimate-ship-tax">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>
					<span class="estimate-title">Endereço para Cobrança</span>
				</th>
			</tr>
		</thead>
		<tbody>
				<tr>
					<td>
						<div class="form-group">
<?php
$query=mysqli_query($con,"select * from users where id='".$_SESSION['id']."'");
while($row=mysqli_fetch_array($query))
{
?>

<div class="form-group">
					    <label class="info-title" for="Billing Address">Endereço de Cobrança<span>*</span></label>
					    <textarea class="form-control unicase-form-control text-input"  name="billingaddress" required="required"><?php echo $row['billingAddress'];?></textarea>
					  </div>



					  <div class="form-group">
    <label class="info-title" for="billingstate">Estado de Cobrança <span>*</span></label>
    <select class="form-control unicase-form-control text-input" id="billingstate" name="billingstate" required>
        <?php
        $estados = [
			"Acre" => "AC",
            "Alagoas" => "AL",
            "Amapá" => "AP",
            "Amazonas" => "AM",
            "Bahia" => "BA",
            "Ceará" => "CE",
            "Distrito Federal" => "DF",
            "Espírito Santo" => "ES",
            "Goiás" => "GO",
            "Maranhão" => "MA",
            "Mato Grosso" => "MT",
            "Mato Grosso do Sul" => "MS",
            "Minas Gerais" => "MG",
            "Pará" => "PA",
            "Paraíba" => "PB",
            "Paraná" => "PR",
            "Pernambuco" => "PE",
            "Piauí" => "PI",
            "Rio de Janeiro" => "RJ",
            "Rio Grande do Norte" => "RN",
            "Rio Grande do Sul" => "RS",
            "Rondônia" => "RO",
            "Roraima" => "RR",
            "Santa Catarina" => "SC",
            "São Paulo" => "SP",
            "Sergipe" => "SE",
            "Tocantins" => "TO"
        ];

        foreach ($estados as $sigla => $nome) {
            $selected = ($row['billingState'] == $sigla) ? 'selected' : '';
            echo "<option value=\"$sigla\" $selected>$nome</option>";
        }
        ?>
    </select>
</div>
					  <div class="form-group">
					    <label class="info-title" for="Billing City">Cidade de Cobrança<span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" id="billingcity" name="billingcity" required="required" value="<?php echo $row['billingCity'];?>" >
					  </div>

					<div class="form-group">
    <label class="info-title" for="billingpincode">CEP de Cobrança <span>*</span></label>
    <input 
		type="text" 
        class="form-control unicase-form-control text-input" 
        id="billingpincode" 
        name="billingpincode" 
        required 
        value="<?php echo $row['billingPincode']; ?>" 
        pattern="\d{5}-\d{3}" 
        title="Digite o CEP no formato 00000-000"
        oninput="mascaraCep(this)"
		inputmode="numeric"
		maxlength="9"
		style="-webkit-text-security: disc; text-security: disc;"
		
       
    >
</div>
						
					</div>

					  <button type="submit" name="update" class="btn-upper btn btn-primary checkout-page-button">Atualizar</button>
			
					<?php } ?>
		
						</div>
					
					</td>
				</tr>
		</tbody><!-- /tbody -->
	</table><!-- /table -->
</div>

<div class="col-md-4 col-sm-12 estimate-ship-tax">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>
					<span class="estimate-title">Endereço de Envio</span>
				</th>
			</tr>
		</thead>
		<tbody>
				<tr>
					<td>
						<div class="form-group">
		<?php
$query=mysqli_query($con,"select * from users where id='".$_SESSION['id']."'");
while($row=mysqli_fetch_array($query))
{
?>

                      <div class="form-group">
					    <label class="info-title" for="Shipping Address">Endereço para Envio<span>*</span></label>
					    <textarea class="form-control unicase-form-control text-input"  name="shippingaddress" required="required"><?php echo $row['shippingAddress'];?></textarea>
					  </div>



						<div class="form-group">
    <label class="info-title" for="shippingstate">Estado de Envio <span>*</span></label>
    <select class="form-control unicase-form-control text-input" id="shippingtate" name="shippingstate" required>
        <?php
        $estados = [
            "Acre" => "AC",
            "Alagoas" => "AL",
            "Amapá" => "AP",
            "Amazonas" => "AM",
            "Bahia" => "BA",
            "Ceará" => "CE",
            "Distrito Federal" => "DF",
            "Espírito Santo" => "ES",
            "Goiás" => "GO",
            "Maranhão" => "MA",
            "Mato Grosso" => "MT",
            "Mato Grosso do Sul" => "MS",
            "Minas Gerais" => "MG",
            "Pará" => "PA",
            "Paraíba" => "PB",
            "Paraná" => "PR",
            "Pernambuco" => "PE",
            "Piauí" => "PI",
            "Rio de Janeiro" => "RJ",
            "Rio Grande do Norte" => "RN",
            "Rio Grande do Sul" => "RS",
            "Rondônia" => "RO",
            "Roraima" => "RR",
            "Santa Catarina" => "SC",
            "São Paulo" => "SP",
            "Sergipe" => "SE",
            "Tocantins" => "TO"
        ];

        foreach ($estados as $nome => $sigla) {
            $selected = ($row['shippingState'] == $nome) ? 'selected' : '';
            echo "<option value=\"$nome\" $selected>$sigla</option>";
        }
        ?>
    </select>
</div>

					 <div class="form-group">
    <label class="info-title" for="Shipping City">Cidade de Envio <span>*</span></label>
    <input type="text" class="form-control unicase-form-control text-input" id="shippingcity" name="shippingcity" required="required" value="<?php echo $row['shippingCity'];?>" pattern="[A-Za-zÀ-ÿ\s]+" title="A cidade deve conter apenas letras e espaços.">
</div>

					<div class="form-group">
    <label class="info-title" for="shippingpincode">CEP de Envio <span>*</span></label>
    <input 
		type="text" 
		class="form-control unicase-form-control text-input" 
		id="shippingpincode" 
		name="shippingpincode" 
		required 
		value="<?php echo $row['shippingPincode']; ?>" 
		pattern="\d{5}-\d{3}" 
		title="Digite o CEP no formato 00000-000"
		oninput="mascaraCep(this)"
		inputmode="numeric"
		maxlength="9"
		style="-webkit-text-security: disc; text-security: disc;"
		
 
       
    >
	</div>


					<button type="submit" name="update" class="btn-upper btn btn-primary checkout-page-button">Atualizar</button>
					<?php } ?>

		
						</div>
					
					</td>
				</tr>
		</tbody><!-- /tbody -->
	</table><!-- /table -->
</div>
<div class="col-md-4 col-sm-12 cart-shopping-total">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>
					
					<div class="cart-grand-total">
						Total Geral R$<span class="inner-left-md"><?php echo $_SESSION['tp']="$totalprice". ""; ?></span>
					</div>
				</th>
			</tr>
		</thead><!-- /thead -->
		<tbody>
				<tr>
  <td>
    <div class="cart-checkout-btn pull-right">
      <form method="post" action="payment-method.php">
        
        <!-- Checkbox para confirmação -->
        <input type="checkbox" id="confirm-check" style="display:none;">
        
        <!-- Label estilizado como botão para abrir confirmação -->
        <label for="confirm-check" class="btn btn-primary" style="cursor:pointer;">
          FAZER O CHECKOUT
        </label>


       <style>
.confirm-message {
  max-width: 600px;
  margin: 20px auto;
  padding: 15px;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #f9f9f9;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.confirm-message p {
  flex-basis: 100%;
  margin-bottom: 15px;
  text-align: center;
  font-size: 1.1rem;
}

.confirm-message button,
.confirm-message label {
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 15px;
}
</style>

<div class="confirm-message">
  <p>Você deseja realmente prosseguir para o pagamento?</p>

 <button type="submit" name="ordersubmit" class="btn btn-success">
        Confirmar e Enviar
    </button>
</form>


<?php if ($showToast): ?>
<script>
    toastr.success("Pedido enviado com sucesso! Acesse <strong>Minha Conta &gt; Pagamentos</strong> e clique em <strong>Prosseguir para pagamento</strong>", null, {timeOut: 10000});
</script>
<?php endif; ?>
<?php
// Zera o carrinho após confirmação do pedido
if (isset($_POST['ordersubmit'])) {
    $_SESSION['cart'] = [];
    // Remove o POST para evitar reenvio do formulário ao dar reload
    echo "<script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
        // Limpa visualmente o carrinho mantendo a identidade visual
        var cartTable = document.querySelector('.shopping-cart-table .table-responsive');
        if (cartTable) {
            cartTable.innerHTML = `
                <table class='table table-bordered'>
                    <thead>
                        <tr>
                            <th class='cart-romove item'>Remover</th>
                            <th class='cart-description item'>Imagem</th>
                            <th class='cart-product-name item'>Nome do Produto</th>
                            <th class='cart-qty item'>Quantidade</th>
                            <th class='cart-sub-total item'>Preço por Unidade</th>
                            <th class='cart-sub-total item'>Taxa de Frete</th>
                            <th class='cart-total last-item'>Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan='7'>
                                <div class='alert alert-info text-center' style='margin: 30px 0; font-size: 1.2em;'>
                                    Seu carrinho de compras está vazio
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            `;
        }
        // Zera o total geral mantendo o layout
        var totalSpan = document.querySelector('.cart-grand-total .inner-left-md');
        if (totalSpan) totalSpan.textContent = '0,00';
    </script>";
}
?>


<!-- Inclusão do toastr -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


  <label for="confirm-check" class="btn btn-secondary" style="cursor:pointer;">
    Cancelar
  </label>
</div>


        </div>
      </form>
    </div>
  </td>
</tr>

<style>
  .confirm-message {
    display: none;
    margin-top: 10px;
  }
  /* Quando o checkbox está marcado, mostra a confirmação */
  #confirm-check:checked ~ .confirm-message {
    display: block;
  }
  /* Esconde o label "Fazer o Checkout" quando está confirmado */
  #confirm-check:checked + label {
    display: none;
  }
</style>

		</tbody><!-- /tbody -->
	</table>
	<?php } else {
echo "Seu carrinho de compras está vazio";
		}?>
</div>			</div>
		</div> 
		</form>
<?php include('includes/brands-slider.php');?>
</div>
</div>
<?php include('includes/footer.php');?>

	<script src="assets/js/jquery-1.11.1.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
	<script src="assets/js/owl.carousel.min.js"></script>
	<script src="assets/js/echo.min.js"></script>
	<script src="assets/js/jquery.easing-1.3.min.js"></script>
	<script src="assets/js/bootstrap-slider.min.js"></script>
    <script src="assets/js/jquery.rateit.min.js"></script>
    <script type="text/javascript" src="assets/js/lightbox.min.js"></script>
    <script src="assets/js/bootstrap-select.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
	<script src="assets/js/scripts.js"></script>

	
	<script src="switchstylesheet/switchstylesheet.js"></script>
	
	<script>
		$(document).ready(function(){ 
			$(".changecolor").switchstylesheet( { seperator:"color"} );
			$('.show-theme-options').click(function(){
				$(this).parent().toggleClass('open');
				return false;
			});
		});

		$(window).bind("load", function() {
		   $('.show-theme-options').delay(2000).trigger('click');	
		});
	</script>

	<?php if (isset($_SESSION['msg_success']) || isset($_SESSION['msg_error'])): ?>
<script>
    $(document).ready(function () {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": 5000,
            "positionClass": "toast-top-right"
        };
        <?php if (isset($_SESSION['msg_success'])): ?>
            toastr.success("<?php echo addslashes($_SESSION['msg_success']); ?>");
            <?php unset($_SESSION['msg_success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['msg_error'])): ?>
            toastr.error("<?php echo addslashes($_SESSION['msg_error']); ?>");
            <?php unset($_SESSION['msg_error']); ?>
        <?php endif; ?>
    });
</script>
<?php endif; ?>

<script>


document.getElementById('billingcity').addEventListener('input', function (e) {
	this.value = this.value.replace(/[^A-Za-zÀ-ÿ\s]/g, '');
});
</script>
<?php if (isset($_SESSION['msg_success']) || isset($_SESSION['msg_error'])): ?>
<script>
    $(document).ready(function () {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            timeOut: 5000,
            positionClass: 'toast-top-right'
        };
        <?php if (isset($_SESSION['msg_success'])): ?>
            toastr.success("<?php echo addslashes($_SESSION['msg_success']); ?>");
            <?php unset($_SESSION['msg_success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['msg_error'])): ?>
            toastr.error("<?php echo addslashes($_SESSION['msg_error']); ?>");
            <?php unset($_SESSION['msg_error']); ?>
        <?php endif; ?>
    });


<script>
$(document).ready(function() {
    $('#billingpincode').mask('*****-***');
	$('#billingpincode').on('input', function() {
		mascaraCep(this);
	});
    $('#shippingpincode').mask('*****-***');
	$('#shippingpincode').on('input', function() {
	
		mascaraCep(this);
	})};


</script>

function mascaraCep(input) {
    let value = input.value.replace(/\D/g, ''); // remove tudo que não é número
    if (value.length > 5) {
        input.value = value.slice(0, 5) + '-' + value.slice(5, 8);
    } else {
        input.value = value;
    }
}

</script>
<?php endif; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</body>

</html>