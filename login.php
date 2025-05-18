<?php
namespace Models;
session_start();
error_reporting(0);
include('includes/config.php');


class Users {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    // Método para registrar um novo usuário
	public function register($name, $email, $contactno, $password) {
		// Usando password_hash() para gerar um hash seguro
		$passwordHash = password_hash($password, PASSWORD_DEFAULT);
		$stmt = $this->con->prepare("INSERT INTO users (name, email, contactno, password) VALUES (?, ?, ?, ?)");
		$stmt->bind_param("ssss", $name, $email, $contactno, $passwordHash);
		if ($stmt->execute()) {
			return true;
		} else {
			return false;
		}
	}

    // Método para login do usuário
	public function login($email, $password) {
		// Remover o uso de md5 e usar o banco de dados diretamente
		$stmt = $this->con->prepare("SELECT * FROM users WHERE email = ?");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$result = $stmt->get_result();
		$user = $result->fetch_assoc();
	
		if ($user && password_verify($password, $user['password'])) {
			// Senha correta
			$_SESSION['login'] = $email;
			$_SESSION['id'] = $user['id'];
			$_SESSION['username'] = $user['name'];
	
			// Registra o log de login do usuário
			$uip = $_SERVER['REMOTE_ADDR'];
			$status = 1;
			$log = $this->con->prepare("INSERT INTO userlog (userEmail, userip, status) VALUES (?, ?, ?)");
			$log->bind_param("ssi", $email, $uip, $status);
			$log->execute();
	
			return true;
		} else {
			// Registra tentativa de login falha
			$uip = $_SERVER['REMOTE_ADDR'];
			$status = 0;
			$log = $this->con->prepare("INSERT INTO userlog (userEmail, userip, status) VALUES (?, ?, ?)");
			$log->bind_param("ssi", $email, $uip, $status);
			$log->execute();
	
			return false;
		}
	}
}

// Criando um objeto da classe User
$user = new Users($con);

// Cadastro de usuário
if (isset($_POST['submit'])) {
	$name = $_POST['fullname'];
	$email = $_POST['emailid'];
	$contactno = $_POST['contactno'];
	$password = $_POST['password'];

	// Validação: aceitar apenas formato 9XXXX-XXXX
	if (preg_match('/^9\d{4}-\d{4}$/', $contactno)) {
		// Armazena o número mascarado (******-****) no banco, se desejar
		// $maskedContact = '******-****'; // Se quiser salvar mascarado
		if ($user->register($name, $email, $contactno, $password)) {
			echo "<script>alert('Você está registrado com sucesso');</script>";
		} else {
			echo "<script>alert('O registro falhou. Algo deu errado');</script>";
		}
	} else {
		echo "<script>alert('O número de contato deve estar no formato 9XXXX-XXXX');</script>";
	}
}

// Login do usuário
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($user->login($email, $password)) {
        header("location:my-cart.php");
        exit();
    } else {
        $_SESSION['errmsg'] = "ID de e-mail ou senha inválidos";
        header("location:login.php");
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

	    <title>Portal de Compras | Entrar | Inscrever-se</title>

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
function valid()
{
 if(document.register.password.value!= document.register.confirmpassword.value)
{
alert("Os campos Senha e Confirmar Senha não correspondem  !!");
document.register.confirmpassword.focus();
return false;
}
return true;
}
</script>
    	<script>
function userAvailability() {
$("#loaderIcon").show();
jQuery.ajax({
url: "check_availability.php",
data:'email='+$("#email").val(),
type: "POST",
success:function(data){
$("#user-availability-status1").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
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
				<li><a href="index.php">Home</a></li>
				<li class='active'>Autenticação</li>
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
	<h4 class="">Entrar</h4>
	<p class="">Olá, bem-vindo à sua conta.</p>
	<form class="register-form outer-top-xs" method="post">
	<span style="color:red;" >
<?php
echo htmlentities($_SESSION['errmsg']);
?>
<?php
echo htmlentities($_SESSION['errmsg']="");
?>
	</span>
		<div class="form-group">
		    <label class="info-title" for="exampleInputEmail1">Endereço de Email <span>*</span></label>
		    <input type="email" name="email" class="form-control unicase-form-control text-input" id="exampleInputEmail1" >
		</div>
	  	<div class="form-group">
		    <label class="info-title" for="exampleInputPassword1">Senha <span>*</span></label>
		 <input type="password" name="password" class="form-control unicase-form-control text-input" id="exampleInputPassword1" >
		</div>
		<div class="radio outer-xs">
		  	<a href="forgot-password.php" class="forgot-password pull-right">Esqueceu sua Senha?</a>
		</div>
	  	<button type="submit" class="btn-upper btn btn-primary checkout-page-button" name="login">Login</button>
	</form>					
</div>
<!-- Sign-in -->

<!-- create a new account -->
<div class="col-md-6 col-sm-6 create-new-account">
	<h4 class="checkout-subtitle">Criar Conta</h4>
	<p class="text title-tag-line">Crie sua própria conta de compras.</p>
	<form class="register-form outer-top-xs" role="form" method="post" name="register" onSubmit="return valid();">
<div class="form-group">
	    	<label class="info-title" for="fullname">Nome Completo <span>*</span></label>
	    	<input type="text" class="form-control unicase-form-control text-input" id="fullname" name="fullname" required="required">
	  	</div>


		<div class="form-group">
	    	<label class="info-title" for="exampleInputEmail2">Endreço de Email <span>*</span></label>
			<input type="email" class="form-control unicase-form-control text-input" id="email" onBlur="userAvailability()" name="emailid" required >
				<span id="user-availability-status1" style="font-size:12px;"></span>
		</div>

		<div class="form-group">
			<label class="info-title" for="contactno">Número de Contato <span>*</span></label>
			<input 
				type="password" 
				class="form-control unicase-form-control text-input" 
				id="contactno" 
				name="contactno" 
				pattern="9\d{4}-\d{4}" 
				title="Formato: 9XXXX-XXXX" 
				onblur="checkContactAvailability()" 
				required
				inputmode="text"
				maxlength="9"
			>
			<span id="contact-status" style="font-size:12px;"></span>
		</div>
		<script>
		$(document).ready(function(){
			$('#contactno').inputmask('9\\9999-9999');
		});
		// Mascara visual de asteriscos
		$('#contactno').on('input', function() {
			let val = $(this).val();
			let masked = val.replace(/./g, '*');
			this.setAttribute('data-real', val);
			this.value = masked;
		}).on('focus', function() {
			let real = this.getAttribute('data-real') || '';
			this.value = real;
		}).on('blur', function() {
			let val = this.value;
			let masked = val.replace(/./g, '*');
			this.setAttribute('data-real', val);
			this.value = masked;
		});
		</script>

<!-- No final da página, antes de </body>: -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
<script>
$(document).ready(function(){
    $('#contactno').inputmask('(99) 99999-9999');
});
</script>





<div class="form-group">
	    	<label class="info-title" for="password">Senha. <span>*</span></label>
	    	<input type="password" class="form-control unicase-form-control text-input" id="password" name="password"  required >
	  	</div>

<div class="form-group">
	    	<label class="info-title" for="confirmpassword">Confirmar Senha. <span>*</span></label>
	    	<input type="password" class="form-control unicase-form-control text-input" id="confirmpassword" name="confirmpassword" required >
	  	</div>


	  	<button type="submit" name="submit" class="btn-upper btn btn-primary checkout-page-button" id="submit">Criar</button>
	</form>
	<span class="checkout-subtitle outer-top-xs">Inscreva-se hoje e você poderá :  </span>
	<div class="checkbox">
	  	<label class="checkbox">
		  Acelere sua finalização de compra.
		</label>
		<label class="checkbox">
		Rastreie seus pedidos facilmente.
		</label>
		<label class="checkbox">
		Mantenha um registro de todas as suas compras.
		</label>
	</div>
</div>	
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
	<script>
$(document).ready(function(){
    $('#contactno').mask('(00) 00000-0000');
});
</script>

<script>
function checkContactAvailability() {
    $("#contact-status").html('<span style="color:blue;">Verificando...</span>');
    $.ajax({
        url: "check_contact.php",
        method: "POST",
        data: { contactno: $("#contactno").val() },
        success: function(data){
            $("#contact-status").html(data);
        }
    });
}
</script>



</body>
</html>