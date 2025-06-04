<?php
namespace Models;
session_start();
error_reporting(0);
include_once('includes/config.php');
include_once("models.login.php");

class Users {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function forgetPassword($email, $contact, $newPassword) {
        $query = $this->con->prepare("SELECT id FROM users WHERE email = ? AND contactno = ?");
        $query->bind_param("ss", $email, $contact);
        $query->execute();
        $query->store_result();

        if ($query->num_rows > 0) {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $this->con->prepare("UPDATE users SET password = ? WHERE email = ? AND contactno = ?");
            $update->bind_param("sss", $passwordHash, $email, $contact);

            if ($update->execute()) {
                $_SESSION['msg_success'] = "Senha alterada com sucesso!";
                return true;
            } else {
                $_SESSION['errmsg'] = "Erro ao alterar senha!";
                return false;
            }
        } else {
            $_SESSION['errmsg'] = "ID de e-mail ou número de contato inválidos!";
            return false;
        }
    }
}



$user = new Users($con);

if (isset($_POST['change'])) {
    $email = $_POST['email'];
    $contact = preg_replace('/[^0-9]/', '', $_POST['contact']); // Remove não dígitos
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirmpassword'];

    // Validação de número de contato
    if (!preg_match('/^\d{11}$/', $contact)) {
        $_SESSION['errmsg'] = "O número de contato deve conter DDD + número (11 dígitos).";
        header("Location: forgot-password.php");
        exit();
    }

    // Validação de senha
	if ($newPassword !== $confirmPassword) {
		$_SESSION['errmsg'] = "As senhas digitadas não são iguais. Por favor, verifique e tente novamente.";
		header("Location: forgot-password.php");
		exit();
	}

    // Troca a senha se tudo estiver correto
    if ($user->forgetPassword($email, $contact, $newPassword)) {
       $_SESSION['msg_success'] = "Senha alterada com sucesso!";
        header("Location: forgot-password.php");
        exit();
    } else {
        header("Location: forgot-password.php");
        exit();
    }
}

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

	    <title>Portal de Compras | Esqueceu sua senha</title>

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
		<!-- Demo Purpose Only. Should be removed in production : END -->

		
		<!-- Icons/Glyphs -->
		<link rel="stylesheet" href="assets/css/font-awesome.min.css">

        <!-- Fonts --> 
		<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet' type='text/css'>
		
		<!-- Favicon -->
		<link rel="shortcut icon" href="assets/images/favicon.ico">
<script type="text/javascript">
function valid() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirmpassword").value;
	var flash = document.getElementById("flash-message");
	if (password !== confirmPassword) {
		if (!flash) {
			flash = document.createElement("div");
			flash.id = "flash-message";
			flash.className = "flash-error";
			document.body.appendChild(flash);
		}
		flash.textContent = "As senhas digitadas não são iguais. Por favor, verifique e tente novamente.";
		flash.style.display = "block";
		document.getElementById("confirmpassword").focus();
		setTimeout(function() {
			flash.style.display = "none";
		}, 5000);
		return false;
	}

	
	return true;
}
</script>
	</head>
    <body class="cnt-home">
	
		
	
		<!-- ============================================== HEADER ============================================== -->
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
				<li><a href="home.html">Home</a></li>
				<li class='active'>Esqueceu sua senha</li>
			</ul>
		</div><!-- /.breadcrumb-inner -->
	</div><!-- /.container -->
</div><!-- /.breadcrumb -->

<div class="body-content outer-top-bd">
	<div class="container">
		<div class="sign-in-page inner-bottom-sm">
			<div class="row">
				<!-- Sign-in -->			
<div class="col-md-6 col-sm-6 sign-in">
	<h4 class="">Esqueceu sua senha</h4>
	<form class="register-form outer-top-xs" name="register" method="post" onsubmit="return valid();">

	<?php if (!empty($_SESSION['errmsg'])): ?>
<div id="flash-message" class="flash-error">
    <?= htmlentities($_SESSION['errmsg']); ?>
</div>
<?php $_SESSION['errmsg'] = ""; ?>
<?php endif; ?>

<?php if (!empty($_SESSION['msg_success'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var flash = document.createElement("div");
    flash.id = "flash-message";
    flash.className = "flash-success";
    flash.textContent = "<?= htmlentities($_SESSION['msg_success']); ?>";
    document.body.appendChild(flash);
    flash.style.display = "block";
    setTimeout(function () {
        flash.style.display = "none";
    }, 5000);
});
</script>
<?php $_SESSION['msg_success'] = ""; ?>
<?php endif; ?>

<style>
.flash-success {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #4CAF50;
    color: white;
    padding: 15px 25px;
    border-radius: 5px;
    font-weight: bold;
    z-index: 9999;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
    animation: fadeOut 2s ease-in-out forwards;
    animation-delay: 2s;
}
</style>


<style>
.flash-error {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #f44336;
    color: white;
    padding: 15px 25px;
    border-radius: 5px;
    font-weight: bold;
    z-index: 9999;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
    animation: fadeOut 2s ease-in-out forwards;
    animation-delay: 2s;
}
@keyframes fadeOut {
    to {
        opacity: 0;
        visibility: hidden;
    }
}
</style>
		<div class="form-group">
		    <label class="info-title" for="exampleInputEmail1">Endereço de Email <span>*</span></label>
		    <input type="email" name="email" class="form-control unicase-form-control text-input" id="exampleInputEmail1" required >
		</div>


		<div class="form-group">
    <label class="info-title" for="contactno">Número de Contato <span>*</span></label>
    <input type="text"
    class="form-control unicase-form-control text-input"
    id="contactno"
    name="contact"
    placeholder="(43) 99999-9999"
    maxlength="15"
    required>
    <small class="text-muted">Digite DDD + número (ex: 43999999999)</small>
</div>



<div class="form-group">
	    	<label class="info-title" for="password">Senha<span>*</span></label>
	    	<input type="password" class="form-control unicase-form-control text-input" id="password" name="password"  required >
	  	</div>

<div class="form-group">
	    	<label class="info-title" for="confirmpassword">Confirmar Senha. <span>*</span></label>
	    	<input type="password" class="form-control unicase-form-control text-input" id="confirmpassword" name="confirmpassword" required >
	  	</div>


		
	  	<button type="submit" class="btn-upper btn btn-primary checkout-page-button" name="change">Mudar</button>
	</form>					
</div>
<!-- Sign-in -->


<!-- create a new account -->			</div><!-- /.row -->
		</div>
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
	<!-- For demo purposes – can be removed on production : End -->
<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
$(document).ready(function(){
    $('#contactno').mask('(00) 00000-0000');
});
</script>
	

<script>
setTimeout(function() {
    var flash = document.getElementById("flash-message");
    if (flash) {
        flash.style.display = "none";
    }
}, 5000);
</script>


</body>
</html>