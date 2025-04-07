<?php
namespace Models;
session_start();
error_reporting(0);
include_once('includes/config.php');

// Classe Users
class Users {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    // Método para redefinir senha
    public function resetPassword($email, $phone, $newPassword) {
        $hashedPassword = md5($newPassword); // Senha criptografada

        // Verifica se o usuário existe
        $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ? AND contactno = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Atualiza a senha
            $updateStmt = $this->con->prepare("UPDATE users SET password = ? WHERE email = ? AND contactno = ?");
            $updateStmt->bind_param("sss", $hashedPassword, $email, $phone);
            if ($updateStmt->execute()) {
                return "Sucesso";
            } else {
                return "Erro";
            }
        } else {
            return "Inválido";
        }
    }
}

// Criando instância da classe Users
$user = new Users($con);

// Verifica se o formulário foi enviado
if (isset($_POST['submit'])) {
    $email = $_POST['emailid'];
    $phone = $_POST['phoneno'];
    $newPassword = $_POST['inputPassword'];

    $result = $user->resetPassword($email, $phone, $newPassword);

    if ($result === "success") {
        echo "<script>alert('Senha redefinida com sucesso.');</script>";
        echo "<script type='text/javascript'> document.location ='login.php'; </script>";
    } elseif ($result === "invalid") {
        echo "<script>alert('Email ou número de telefone inválidos.');</script>";
        echo "<script type='text/javascript'> document.location ='password-recovery.php'; </script>";
    } else {
        echo "<script>alert('Ocorreu um erro. Tente novamente.');</script>";
        echo "<script type='text/javascript'> document.location ='password-recovery.php'; </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Portal de Compras | Cadastro de usuário</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/jquery.min.js"></script>
       <!--  <link href="css/bootstrap.min.css" rel="stylesheet" /> -->
             <script type="text/javascript">
function valid()
{
 if(document.passwordrecovery.inputPassword.value!= document.passwordrecovery.cinputPassword.value)
{
alert("Password and Confirm Password Field do not match  !!");
document.passwordrecovery.cinputPassword.focus();
return false;
}
return true;
}
</script>
    </head>
<style type="text/css">
    input { border:solid 1px #000;

    }

</style>
    <body>
<?php include_once('includes/header.php');?>
        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">


                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder">Recuperação de senha do usuário </h1>
                   <!--  <p class="lead fw-normal text-white-50 mb-0">Login is required to make the order</p> -->
                </div>

            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4  mt-5">
     

<form method="post" name="passwordrecovery" onSubmit="return valid();">

       <div class="row mt-3">
         <div class="col-2">ID de e-mail</div>
         <div class="col-6"><input type="email" name="emailid" id="emailid" class="form-control"  required>
         </div>
          
     </div>

         <div class="row mt-3">
         <div class="col-2">Número de contato de registro.</div>
         <div class="col-6"><input type="text" name="phoneno" id="phoneno" class="form-control" required>
         </div>
          
     </div>


          <div class="row mt-3">
         <div class="col-2">Senha</div>
         <div class="col-6"><input type="password" name="inputPassword" id="inputPassword" class="form-control" required></div>
     </div>

               <div class="row mt-3">
         <div class="col-2">Recuperação de senha</div>
         <div class="col-6"><input type="password" name="cinputPassword" id="cinputPassword" class="form-control" required></div>
     </div>

               <div class="row mt-3">
                 <div class="col-4">&nbsp;</div>
         <div class="col-6"><input type="submit" name="submit" id="submit" class="btn btn-primary" value="Submit" required></div>
     </div>
 </form>
              
            </div>

 
</div>
        </section>
        <!-- Footer-->
   <?php include_once('includes/footer.php'); ?>
        <!-- Bootstrap core JS-->
        <script src="js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
    </body>
</html>
