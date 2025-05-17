<?php 
session_start();
error_reporting(0);
include('includes/config.php');

// Verifica se o usuário está logado
if(strlen($_SESSION['login'])==0) {   
    header('location:login.php');
    exit();
} else {
if (isset($_POST['submit'])) {
    $paymethod = $_POST['paymethod'];
    $userId = $_SESSION['id'];

    // Use switch para validar o método de pagamento e atualizar o pedido
    switch ($paymethod) {
        case 'Boleto':
        case 'PIX':
        case 'Cartão de Crédito':
            // Atualiza o pedido com o método de pagamento usando prepared statement
            $stmt = $con->prepare("UPDATE orders SET paymentMethod=? WHERE userId=? AND paymentMethod IS NULL");
            $stmt->bind_param("si", $paymethod, $userId);
            $stmt->execute();
            $stmt->close();

            // Mensagens simuladas
            switch ($paymethod) {
                case 'Boleto':
                    $mensagem = "Seu boleto foi gerado! <a href='#'>Clique aqui</a> para visualizar o boleto (simulado).";
                    break;
                case 'PIX':
                    $mensagem = "Use o código abaixo para pagar via PIX:<br><strong>0002012633BR.GOV.BCB.PIX0123pagamento@exemplo.com520400005303986540510.005802BR5913Fulano de Tal6009Sao Paulo62180515OBSPAGAMENTO6304ABCD</strong>";
                    break;
                case 'Cartão de Crédito':
                default:
                    $mensagem = "Simulando pagamento com cartão de crédito...<br><em>Transação aprovada! Obrigado pela compra.</em>";
                    break;
            }
            break;
        default:
            $mensagem = "Método de pagamento inválido.";
    }
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
                                        <label><input type="radio" name="paymethod" value="Cartão de Crédito"> Cartão de Crédito</label><br><br>
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
	<!-- For demo purposes – can be removed on production : End -->

	

</body>
</html>
