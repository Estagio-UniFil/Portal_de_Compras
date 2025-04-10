<?php
namespace Models;
session_start();
error_reporting(0);
include_once('includes/config.php');


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
        $stmt = $this->con->prepare("UPDATE users SET billingAddress=?, billingState=?, billingCity=?, billingPincode=? WHERE id=?");
        $stmt->bind_param("ssssi", $address, $state, $city, $pincode, $this->userId);
        return $stmt->execute();
    }

    // Atualiza endereço de envio
    public function updateShippingAddress($address, $state, $city, $pincode) {
        $stmt = $this->con->prepare("UPDATE users SET shippingAddress=?, shippingState=?, shippingCity=?, shippingPincode=? WHERE id=?");
        $stmt->bind_param("ssssi", $address, $state, $city, $pincode, $this->userId);
        return $stmt->execute();
    }
}

// Verifica se o usuário está logado
if (strlen($_SESSION['login']) == 0) {
    header('location:login.php');
    exit();
}

// Criar instância da classe Users
$userId = $_SESSION['id'];
$userProfile = new Users($con, $userId);

// Atualizar endereço de cobrança
if (isset($_POST['update'])) {
    $billingAddress = $_POST['billingaddress'] ?? '';
    $billingState = $_POST['billingstate'] ?? '';
    $billingCity = $_POST['billingcity'] ?? '';
    $billingPincode = $_POST['billingpincode'] ?? '';

    if ($userProfile->updateBillingAddress($billingAddress, $billingState, $billingCity, $billingPincode)) {
        $_SESSION['msg_success'] = "Endereço de cobrança atualizado com sucesso!";
    } else {
        $_SESSION['msg_error'] = "Erro ao atualizar o endereço de cobrança.";
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}




// Atualizar endereço de envio
if (isset($_POST['shipupdate'])) {
    $shippingAddress = $_POST['shippingaddress'] ?? '';
    $shippingState = $_POST['shippingstate'] ?? '';
    $shippingCity = $_POST['shippingcity'] ?? '';
    $shippingPincode = $_POST['shippingpincode'] ?? '';

	if ($userProfile->updateShippingAddress($shippingAddress, $shippingState, $shippingCity, $shippingPincode)) {
		$_SESSION['msg_success'] = "Endereço de envio atualizado com sucesso!";
	} else {
		$_SESSION['msg_error'] = "Erro ao atualizar o endereço de envio.";
	}
	header("Location: ".$_SERVER['PHP_SELF']);
	exit();
}	
	

// Obtém os dados do usuário
$userDetails = $userProfile->getUserDetails();
?>

<!DOCTYPE html>
<html lang="en">
	<head>
	<style>
.toast-custom {
    position: fixed;
    top: 20px;
    right: 20px;
    min-width: 250px;
    background-color: #333;
    color: #fff;
    padding: 12px 20px;
    border-radius: 5px;
    z-index: 9999;
    display: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
}
.toast-success {
    background-color: #28a745;
}
.toast-error {
    background-color: #dc3545;
}
</style>
		<!-- Meta -->
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">
	    <meta name="keywords" content="MediaCenter, Template, eCommerce">
	    <meta name="robots" content="all">

	    <title>Minha Conta</title>

	    <!-- Bootstrap Core CSS -->
	    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
	    
	    <!-- Customizable CSS -->
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
		<link rel="stylesheet" href="assets/css/font-awesome.min.css">
		<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet' type='text/css'>
		<link rel="shortcut icon" href="assets/images/favicon.ico">
		

	</head>
    <body class="cnt-home">
	<?php if (!empty($message)) : ?>
    <div id="customToast" class="toast-custom <?php echo stripos($message, 'erro') !== false ? 'toast-error' : 'toast-success'; ?>">
        <?php echo htmlentities($message); ?>
    </div>
<?php endif; ?>



<header class="header-style-1">

	<!-- ============================================== TOP MENU ============================================== -->
<?php include('includes/top-header.php');?>
<!-- ============================================== TOP MENU : END ============================================== -->
<?php include('includes/main-header.php');?>
	<!-- ============================================== NAVBAR ============================================== -->
<?php include('includes/menu-bar.php');?>
<!-- ============================================== NAVBAR : END ============================================== -->

</header>
<!-- ============================================== HEADER : END ============================================== -->
<div class="breadcrumb">
	<div class="container">
		<div class="breadcrumb-inner">
			<ul class="list-inline list-unstyled">
				<li><a href="#">Home</a></li>
				<li class='active'>Checkout</li>
			</ul>
		</div><!-- /.breadcrumb-inner -->
	</div><!-- /.container -->
</div><!-- /.breadcrumb -->

<div class="body-content outer-top-bd">
	<div class="container">
		<div class="checkout-box inner-bottom-sm">
			<div class="row">
				<div class="col-md-8">
					<div class="panel-group checkout-steps" id="accordion">
						<!-- checkout-step-01  -->
<div class="panel panel-default checkout-step-01">

	<!-- panel-heading -->
		<div class="panel-heading">
    	<h4 class="unicase-checkout-title">
	        <a data-toggle="collapse" class="" data-parent="#accordion" href="#collapseOne">
	          <span>1</span>Endereço de Cobrança
	        </a>
	     </h4>
    </div>
    <!-- panel-heading -->

	<div id="collapseOne" class="panel-collapse collapse in">

		<!-- panel-body  -->
	    <div class="panel-body">
			<div class="row">		
				<div class="col-md-12 col-sm-12 already-registered-login">

<?php
$query=mysqli_query($con,"select * from users where id='".$_SESSION['id']."'");
while($row=mysqli_fetch_array($query))
{
?>

					<form class="register-form" role="form" method="post">
<div class="form-group">
					    <label class="info-title" for="Billing Address">Endereço de Cobrança<span>*</span></label>
					    <textarea class="form-control unicase-form-control text-input"  name="billingaddress" required="required"><?php echo $row['billingAddress'];?></textarea>
					  </div>



						<div class="form-group">
					    <label class="info-title" for="Billing State ">Estado de Cobrança  <span>*</span></label>
			 <input type="text" class="form-control unicase-form-control text-input" id="billingstate" name="billingstate" value="<?php echo $row['billingState'];?>" required>
					  </div>
					  <div class="form-group">
					    <label class="info-title" for="Billing City">Cidade de Cobrança <span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" id="billingcity" name="billingcity" required="required" value="<?php echo $row['billingCity'];?>" >
					  </div>
 <div class="form-group">
					    <label class="info-title" for="Billing Pincode">CEP de Cobrança <span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" id="billingpincode" name="billingpincode" required="required" value="<?php echo $row['billingPincode'];?>" >
					  </div>


					  <button type="submit" name="update" class="btn-upper btn btn-primary checkout-page-button">Atualizar</button>
					</form>
					<?php }?>
				</div>	
				<!-- already-registered-login -->		

			</div>			
		</div>
		<!-- panel-body  -->

	</div><!-- row -->
</div>
<!-- checkout-step-01  -->
					    <!-- checkout-step-02  -->
					  	<div class="panel panel-default checkout-step-02">
						    <div class="panel-heading">
						      <h4 class="unicase-checkout-title">
						        <a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#collapseTwo">
						          <span>2</span>Endereço de Envio
						        </a>
						      </h4>
						    </div>
						    <div id="collapseTwo" class="panel-collapse collapse">
						      <div class="panel-body">
						     
				<?php
$query=mysqli_query($con,"select * from users where id='".$_SESSION['id']."'");
while($row=mysqli_fetch_array($query))
{
?>

					<form class="register-form" role="form" method="post">
<div class="form-group">
					    <label class="info-title" for="Shipping Address">Endereço de Envio<span>*</span></label>
					    <textarea class="form-control unicase-form-control text-input" " name="shippingaddress" required="required"><?php echo $row['shippingAddress'];?></textarea>
					  </div>



						<div class="form-group">
					    <label class="info-title" for="Billing State ">Estado de Envio  <span>*</span></label>
			 <input type="text" class="form-control unicase-form-control text-input" id="shippingstate" name="shippingstate" value="<?php echo $row['shippingState'];?>" required>
					  </div>
					  <div class="form-group">
					    <label class="info-title" for="Billing City">Cidade de Envip <span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" id="shippingcity" name="shippingcity" required="required" value="<?php echo $row['shippingCity'];?>" >
					  </div>
 <div class="form-group">
					    <label class="info-title" for="Billing Pincode">CEP de Envio <span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" id="shippingpincode" name="shippingpincode" required="required" value="<?php echo $row['shippingPincode'];?>" >
					  </div>


					  <button type="submit" name="shipupdate" class="btn-upper btn btn-primary checkout-page-button">Atualizar</button>
					</form>
					<?php } ?>




						      </div>
						    </div>
					  	</div>
					  	<!-- checkout-step-02  -->
					  	
					</div><!-- /.checkout-steps -->
				</div>
			<?php include('includes/myaccount-sidebar.php');?>
			</div><!-- /.row -->
		</div><!-- /.checkout-box -->
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
	<!-- jQuery (apenas uma vez) -->
<script src="assets/js/jquery-1.11.1.min.js"></script>

<!-- Toastr -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Toastr Config -->
<script>
toastr.options = {
  "closeButton": true,
  "progressBar": true,
  "positionClass": "toast-top-right",
  "timeOut": "4000"
};
</script>

<!-- Mensagens dinâmicas -->
<script>
$(document).ready(function() {
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
</body>
</html>
<?php ?>