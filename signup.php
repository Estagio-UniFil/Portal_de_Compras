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
    public function createAccount($name, $email, $contactno, $password) {
        $passwordHash = md5($password);
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
        $passwordHash = md5($password);
        $stmt = $this->con->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $passwordHash);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
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

    if ($user->createAccount($name, $email, $contactno, $password)) {
        echo "<script>alert('Você foi registrado com sucesso');</script>";
    } else {
        echo "<script>alert('O registro falhou. Algo deu errado');</script>";
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
        $_SESSION['errmsg'] = "Invalid email ID or password";
        header("location:login.php");
        exit();
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
        <title>Portal de Compras | Inscrição de Usuário</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/jquery.min.js"></script>
       <!--  <link href="css/bootstrap.min.css" rel="stylesheet" /> -->
    </head>
            <script>
function emailAvailability() {
$("#loaderIcon").show();
jQuery.ajax({
url: "check_availability.php",
data:'email='+$("#emailid").val(),
type: "POST",
success:function(data){
$("#user-email-status").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
}


function checkContactAvailability() {
    $("#loaderIcon").show();
    jQuery.ajax({
        url: "check_availability.php",
        data: { contact: $("#contactno").val() },
        type: "POST",
        success: function(data) {
            $("#contact-status").html(data);
            $("#loaderIcon").hide();
        },
        error: function() {}
    });
}

</script>
</script>
<style type="text/css"></style>
    <body>
<?php include_once('includes/header.php');?>
        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">


                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder">Inscrição de Usuário</h1>
                    <p class="lead fw-normal text-white-50 mb-0">É necessário um registro único para fazer compras</p>
                </div>

            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4  mt-5">
     

<form method="post" name="signup">
     <div class="row">
         <div class="col-2">Nome Completo</div>
         <div class="col-6"><input type="text" name="fullname" class="form-control" required ></div>
     </div>
       <div class="row mt-3">
         <div class="col-2">ID de e-mail</div>
         <div class="col-6"><input type="email" name="emailid" id="emailid" class="form-control" onBlur="emailAvailability()" required>
 <span id="user-email-status" style="font-size:12px;"></span>
         </div>
          
     </div>

     <input type="tel" name="contactno" id="contactno" class="form-control" required 
    placeholder="(43) 91234-5678"
    pattern="^\([1-9]{2}\)\s9[0-9]{4}-[0-9]{4}$"
    title="Formato válido: (43) 91234-5678"
    onblur="checkContactAvailability()" />
<span id="contact-status" style="font-size:12px;"></span>


          <div class="row mt-3">
         <div class="col-2">Senha</div>
         <div class="col-6"><input type="password" name="inputuserpwd" class="form-control" required></div>
     </div>

               <div class="row mt-3">
                 <div class="col-4">&nbsp;</div>
         <div class="col-6"><input type="submit" name="submit" id="submit" class="btn btn-primary" required></div>
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
