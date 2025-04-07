<?php 
session_start();
error_reporting(0);
include('includes/config.php');

// Verifica se o usuário está logado
if(strlen($_SESSION['login'])==0)
{   
    header('location:login.php');
    exit();
}
else {

    if (isset($_POST['submit'])) {

        // Atualiza método de pagamento no banco local
        mysqli_query($con,"UPDATE orders SET paymentMethod='".$_POST['paymethod']."' WHERE userId='".$_SESSION['id']."' AND paymentMethod IS NULL");

        // Pega dados do usuário logado
        $userId = $_SESSION['id'];
        $queryUser = mysqli_query($con,"SELECT * FROM users WHERE id='$userId'");
        $user = mysqli_fetch_array($queryUser);

        // Busca o pedido mais recente com pagamento não definido
        $orderQuery = mysqli_query($con,"SELECT * FROM orders WHERE userId='$userId' AND paymentMethod IS NOT NULL ORDER BY id DESC LIMIT 1");
        $order = mysqli_fetch_array($orderQuery);

        if(!$order){
            echo "Nenhum pedido encontrado!";
            exit();
        }

        // Informações do pedido
        $orderId = $order['id'];
        $orderValue = $order['order_total'];
        $description = "Pagamento do Pedido #" . $orderId;

        // Integração com Asaas
        $api_key = 'SUA_API_KEY_DO_ASAAS'; // Altere aqui para sua chave
        $base_url = 'https://sandbox.asaas.com/api/v3'; // Produção: https://www.asaas.com/api/v3

        // 1. Cria o cliente no Asaas
        $customerData = [
            "name" => $user['name'],
            "email" => $user['email'],
            "mobilePhone" => $user['contactno'],
        ];

        $ch = curl_init("$base_url/customers");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "access_token: $api_key"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerData));

        $customerResponse = curl_exec($ch);
        curl_close($ch);

        $customerResult = json_decode($customerResponse, true);

        if(isset($customerResult['id'])){
            $customerIdAsaas = $customerResult['id'];
        } else {
            echo "Erro ao criar cliente no Asaas!";
            exit();
        }

        // 2. Define tipo de cobrança
        $billingType = '';
        if ($_POST['paymethod'] == 'Boleto') {
            $billingType = 'BOLETO';
        } elseif ($_POST['paymethod'] == 'PIX') {
            $billingType = 'PIX';
        } elseif ($_POST['paymethod'] == 'Cartão de Crédito') {
            $billingType = 'CREDIT_CARD';
        }

        // 3. Cria a cobrança/pagamento no Asaas
        $dueDate = date('Y-m-d', strtotime('+3 days')); // Ajuste do vencimento

        $paymentData = [
            "customer" => $customerIdAsaas,
            "billingType" => $billingType,
            "value" => $orderValue,
            "dueDate" => $dueDate,
            "description" => $description
        ];

        $ch = curl_init("$base_url/payments");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "access_token: $api_key"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));

        $paymentResponse = curl_exec($ch);
        curl_close($ch);

        $paymentResult = json_decode($paymentResponse, true);

        if(isset($paymentResult['invoiceUrl'])){
            $paymentLink = $paymentResult['invoiceUrl'];

            // Limpa o carrinho (opcional)
            unset($_SESSION['cart']);

            // Redireciona o usuário para o link de pagamento do Asaas
            header("Location: $paymentLink");
            exit();

        } else {
            echo "Erro ao criar pagamento no Asaas!";
            print_r($paymentResult);
            exit();
        }

    } // Fim do isset($_POST['submit'])
} // Fim do else

// FECHA AQUI O PHP PARA EXIBIR O HTML
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Meta -->
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">
	    <meta name="keywords" content="MediaCenter, Template, eCommerce">
	    <meta name="robots" content="all">

	    <title>Portal de Compras | Método de Pagamento</title>
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
		<link rel="stylesheet" href="assets/css/config.css">
		<link href="assets/css/green.css" rel="alternate stylesheet" title="Green color">
		<link href="assets/css/blue.css" rel="alternate stylesheet" title="Blue color">
		<link href="assets/css/red.css" rel="alternate stylesheet" title="Red color">
		<link href="assets/css/orange.css" rel="alternate stylesheet" title="Orange color">
		<link href="assets/css/dark-green.css" rel="alternate stylesheet" title="Darkgreen color">
		<link rel="stylesheet" href="assets/css/font-awesome.min.css">
		<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet' type='text/css'>
		<link rel="shortcut icon" href="assets/images/favicon.ico">
	</head>
    <body class="cnt-home">
	
		
<header class="header-style-1">
<?php include('includes/top-header.php');?>
<?php include('includes/main-header.php');?>
<?php include('includes/menu-bar.php');?>
</header>
<div class="breadcrumb">
	<div class="container">
		<div class="breadcrumb-inner">
			<ul class="list-inline list-unstyled">
				<li><a href="home.html">Home</a></li>
				<li class='active'>Método de Pagamento</li>
			</ul>
		</div><!-- /.breadcrumb-inner -->
	</div><!-- /.container -->
</div><!-- /.breadcrumb -->

<div class="body-content outer-top-bd">
	<div class="container">
		<div class="checkout-box faq-page inner-bottom-sm">
			<div class="row">
				<div class="col-md-12">
					<h2>Escolha o método de pagamento</h2>
					<div class="panel-group checkout-steps" id="accordion">
						<!-- checkout-step-01  -->
<div class="panel panel-default checkout-step-01">

	<!-- panel-heading -->
		<div class="panel-heading">
    	<h4 class="unicase-checkout-title">
	        <a data-toggle="collapse" class="" data-parent="#accordion" href="#collapseOne">
            Selecione seu método de pagamento
	        </a>
	     </h4>
    </div>
    <!-- panel-heading -->

	<div id="collapseOne" class="panel-collapse collapse in">

		<!-- panel-body  -->
	    <div class="panel-body">
	    <form name="payment" method="post">
	    <input type="radio" name="paymethod" value="Boleto" checked="checked"> Boleto
	     <input type="radio" name="paymethod" value="PIX"> PIX
	     <input type="radio" name="paymethod" value="Cartão de Crédito"> Cartão de Crédito<br /><br />
         <button type="submit" name="submit" class="btn btn-primary">Pagar</button>
	    	

	    </form>		
		</div>
		<!-- panel-body  -->

	</div><!-- row -->
</div>
<!-- checkout-step-01  -->
					  
					  	
					</div><!-- /.checkout-steps -->
				</div>
			</div><!-- /.row -->
		</div><!-- /.checkout-box -->
		<!-- ============================================== BRANDS CAROUSEL ============================================== -->
<?php echo include('includes/brands-slider.php');?>
<!-- ============================================== BRANDS CAROUSEL : END ============================================== -->	</div><!-- /.container -->
</div><!-- /.body-content -->
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
