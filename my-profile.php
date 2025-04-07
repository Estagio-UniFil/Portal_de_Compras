<?php 
namespace Models;
session_start();
include('includes/config.php');
include('login.php');

class Users {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function updateProfile($userId, $name, $contactNumber) {
        $query = $this->con->prepare("UPDATE users SET name = ?, contactno = ? WHERE id = ?");
        $query->bind_param("ssi", $name, $contactNumber, $userId);

        if ($query->execute()) {
            return "Perfil atualizado com sucesso!";
        } else {
            return "Algo deu errado. Por favor, tente novamente.";
        }
    }
}

// Verifica se o usuário está logado
if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

// Criando instância da classe Users
$users = new Users($con);

// Atualiza o perfil do usuário
if (isset($_POST['update'])) {
    $name = $_POST['fullname'];
    $contactno = $_POST['contactnumber'];
    $userId = $_SESSION['id'];

    $message = $users->updateProfile($userId, $name, $contactno);
    echo "<script>alert('$message');</script>";
    echo "<script type='text/javascript'> document.location ='my-profile.php'; </script>";
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
    </head>
<style type="text/css"></style>
    <body>
<?php include_once('includes/header.php');?>
        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">

<?php 
$uid=$_SESSION['id'];
$query=mysqli_query($con,"select * from users where id='$uid'");
while($result=mysqli_fetch_array($query)){

?>
                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder"><?php echo htmlentities($result['name']);?>'s Profile</h1>
                </div>

            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4  mt-5">
     
<form method="post" name="profile">
     <div class="row">
         <div class="col-2">Nome Completo</div>
         <div class="col-6"><input type="text" name="fullname" value="<?php echo htmlentities($result['name']);?>" class="form-control" required ></div>
     </div>
       <div class="row mt-3">
         <div class="col-2">ID de e-mail</div>
         <div class="col-6"><input type="email" name="emailid" id="emailid" class="form-control" value="<?php echo htmlentities($result['email']);?>" readonly>
         </div>
          
     </div>

       <div class="row mt-3">
         <div class="col-2">Número de contato</div>
         <div class="col-6"><input type="text" name="contactnumber" value="<?php echo htmlentities($result['contactno']);?>" pattern="[0-9]{10}" title="10 numeric characters only" class="form-control" required></div>
     </div>



               <div class="row mt-3">
                 <div class="col-4">&nbsp;</div>
         <div class="col-6"><input type="submit" name="update" id="update" class="btn btn-primary" value="Atualizar" required></div>
     </div>
 </form>
              
            </div>
<?php } ?>
 
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
<?php  ?>
