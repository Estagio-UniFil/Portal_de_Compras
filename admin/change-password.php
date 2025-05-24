
<?php
session_start();
include('include/config.php');

class Admin {
    private $con;
    private $username;

    public function __construct($db, $username) {
        $this->con = $db;
        $this->username = $username;
    }

    // Método para resetar a senha
    public function resetPassword($oldPassword, $newPassword) {
        // Verifica se a senha antiga está correta
        $stmt = $this->con->prepare("SELECT password FROM admin WHERE username = ? AND password = ?");
        $oldPasswordHash = md5($oldPassword);
        $stmt->bind_param("ss", $this->username, $oldPasswordHash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Senha antiga correta, atualiza para nova senha
            $newPasswordHash = md5($newPassword);
            $currentTime = date('Y-m-d H:i:s');

            $updateStmt = $this->con->prepare("UPDATE admin SET password = ?, updationDate = ? WHERE username = ?");
            $updateStmt->bind_param("sss", $newPasswordHash, $currentTime, $this->username);

            if ($updateStmt->execute()) {
                return true; // Sucesso na atualização
            } else {
                return false; // Falha na atualização
            }
        } else {
            return false; // Senha antiga incorreta
        }
    }
}

// Verifica se o admin está logado
if (empty($_SESSION['alogin'])) {
    header('location:index.php');
    exit();
}

// Instancia a classe Admin
$admin = new Admin($con, $_SESSION['alogin']);

if (isset($_POST['submit'])) {
    $oldPassword = $_POST['password'] ?? '';
    $newPassword = $_POST['newpassword'] ?? '';

    if ($admin->resetPassword($oldPassword, $newPassword)) {
        $_SESSION['msg'] = "Senha alterada com sucesso !!";
    } else {
        $_SESSION['msg'] = "Senha antiga não corresponde ou erro ao alterar.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin| Mudar Senha</title>
	<link type="text/css" href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link type="text/css" href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
	<link type="text/css" href="css/theme.css" rel="stylesheet">
	<link type="text/css" href="images/icons/css/font-awesome.css" rel="stylesheet">
	<link type="text/css" href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600' rel='stylesheet'>
	<script type="text/javascript">
function valid()
{
if(document.chngpwd.password.value=="")
{
alert("O Campo de senha atual está vazio!!");
document.chngpwd.password.focus();
return false;
}
else if(document.chngpwd.newpassword.value=="")
{
alert("O Campo Nova senha está vazio!!");
document.chngpwd.newpassword.focus();
return false;
}
else if(document.chngpwd.confirmpassword.value=="")
{
alert("O Campo Confirmar senha está vazio!!");
document.chngpwd.confirmpassword.focus();
return false;
}
else if(document.chngpwd.newpassword.value!= document.chngpwd.confirmpassword.value)
{
alert("Os Campos Senha e Confirmar Senha não correspondem!!");
document.chngpwd.confirmpassword.focus();
return false;
}
return true;
}
</script>
</head>
<body>
<?php include('include/header.php');?>

	<div class="wrapper">
		<div class="container">
			<div class="row">
<?php include('include/sidebar.php');?>				
			<div class="span9">
					<div class="content">

						<div class="module">
							<div class="module-head">
								<h3>Alterar Senha do Administrador</h3>
							</div>
							<div class="module-body">

									<?php if(isset($_POST['submit']))
{?>
									<div class="alert alert-success">
										<button type="button" class="close" data-dismiss="alert">×</button>
										<?php echo htmlentities($_SESSION['msg']);?><?php echo htmlentities($_SESSION['msg']="");?>
									</div>
<?php } ?>
									<br />

			<form class="form-horizontal row-fluid" name="chngpwd" method="post" onSubmit="return valid();">
									
<div class="control-group">
<label class="control-label" for="basicinput">Senha Atual</label>
<div class="controls">
<input type="password" placeholder="Digite sua senha atual"  name="password" class="span8 tip" required>
</div>
</div>


<div class="control-group">
<label class="control-label" for="basicinput">Nova Senha</label>
<div class="controls">
<input type="password" placeholder="Digite sua nova senha atual"  name="newpassword" class="span8 tip" required>
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Repita a Nova Senha</label>
<div class="controls">
<input type="password" placeholder="Digite sua nova senha novamente"  name="confirmpassword" class="span8 tip" required>
</div>
</div>




										

										<div class="control-group">
											<div class="controls">
												<button type="submit" name="submit" class="btn">Atualizar</button>
											</div>
										</div>
									</form>
							</div>
						</div>

						
						
					</div><!--/.content-->
				</div><!--/.span9-->
			</div>
		</div><!--/.container-->
	</div><!--/.wrapper-->

<?php include('include/footer.php');?>

	<script src="scripts/jquery-1.9.1.min.js" type="text/javascript"></script>
	<script src="scripts/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
	<script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="scripts/flot/jquery.flot.js" type="text/javascript"></script>
</body>
<?php  ?>