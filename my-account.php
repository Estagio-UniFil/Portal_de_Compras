<?php
namespace Models;
session_start();
error_reporting(0);
include_once('includes/config.php');
include_once('models.login.php');


if (strlen($_SESSION['login']) == 0) {   
    header('location:login.php');
    exit();
}

// Classe Users para manipulação de usuários
class Users {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function updateUserInfo($userId, $name, $contactNo) {
        $query = $this->con->prepare("UPDATE users SET name = ?, contactno = ? WHERE id = ?");
        $query->bind_param("ssi", $name, $contactNo, $userId);

        if ($query->execute()) {
            return "Suas informações foram atualizadas!";
        } else {
            return "Erro ao atualizar as informações!";
        }
    }
}

// Criando instância da classe Users
$users = new Users($con);

// Atualizando informações do usuário
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $contactNo = $_POST['contactno'];
    $userId = $_SESSION['id'];

    $result = $users->updateUserInfo($userId, $name, $contactNo);

    $_SESSION['toast'] = $result;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Configuração de fuso horário
date_default_timezone_set('America/Sao_Paulo'); // Corrigido para o fuso horário correto
$currentTime = date('d-m-Y h:i:s A', time());
?>


<!DOCTYPE html>
<html lang="en">
	<head>
	<style>
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #28a745;
    color: #fff;
    padding: 12px 20px;
    border-radius: 5px;
    z-index: 9999;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    animation: fadein 0.5s, fadeout 0.5s 3.5s;
}

@keyframes fadein {
    from {opacity: 0;}
    to {opacity: 1;}
}

@keyframes fadeout {
    from {opacity: 1;}
    to {opacity: 0;}
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
<script type="text/javascript">
function valid()
{
if(document.chngpwd.cpass.value=="")
{
alert("O campo de senha atual está vazio!!");
document.chngpwd.cpass.focus();
return false;
}
else if(document.chngpwd.newpass.value=="")
{
alert("O campo Nova senha está vazio !!");
document.chngpwd.newpass.focus();
return false;
}
else if(document.chngpwd.cnfpass.value=="")
{
alert("O campo Confirmar senha está vazio!!");
document.chngpwd.cnfpass.focus();
return false;
}
else if(document.chngpwd.newpass.value!= document.chngpwd.cnfpass.value)
{
alert("Os campos Senha e Confirmar Senha não correspondem !!");
document.chngpwd.cnfpass.focus();
return false;
}
return true;
}
</script>

	</head>
    <body class="cnt-home">
	<?php if (!empty($_SESSION['toast'])): ?>
    <div id="toast" class="toast">
        <?php echo htmlentities($_SESSION['toast']); ?>
    </div>
    <?php unset($_SESSION['toast']); ?>
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
	          <span>1</span>Meu Perfil
	        </a>
	     </h4>
    </div>
    <!-- panel-heading -->

	<div id="collapseOne" class="panel-collapse collapse in">

		<!-- panel-body  -->
	    <div class="panel-body">
			<div class="row">		
<h4>Informações Pessoais</h4>
				<div class="col-md-12 col-sm-12 already-registered-login">
					

<?php
$query=mysqli_query($con,"select * from users where id='".$_SESSION['id']."'");
while($row=mysqli_fetch_array($query))
{
?>

					<form class="register-form" role="form" method="post">
<div class="form-group">
					    <label class="info-title" for="name">Nome<span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" value="<?php echo $row['name'];?>" id="name" name="name" required="required">
					  </div>



						<div class="form-group">
					    <label class="info-title" for="exampleInputEmail1">Endereço de Email<span>*</span></label>
			 <input type="email" class="form-control unicase-form-control text-input" id="exampleInputEmail1" value="<?php echo $row['email'];?>" readonly>
					  </div>
					  
					  <div class="form-group">
    <label class="info-title" for="contactno">Número de Contato <span>*</span></label>
    <input type="password"
           class="form-control unicase-form-control text-input"
           id="contactno"
           name="contactno"
           required
           value="<?php echo htmlentities($row['contactno']); ?>"
           placeholder="(99) 99999-9999" />
</div>

					  <button type="submit" name="update" class="btn-upper btn btn-primary checkout-page-button">Atualizar</button>
					</form>
					<?php } ?>
				</div>	
				<!-- already-registered-login -->		

			</div>			
		</div>
		<!-- panel-body  -->

	</div><!-- row -->
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function () {
    $('#contactno').mask('(00) 00000-0000');
});
</script>

<script>
setTimeout(() => {
    var toast = document.getElementById('toast');
    if (toast) {
        toast.style.display = 'none';
    }
}, 4000);
</script>


</body>
</html>
<?php  ?>