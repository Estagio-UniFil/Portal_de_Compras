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
        return $query->execute();
    }
}

if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
    header("Location: logout.php");
    exit;
}

$users = new Users($con);

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['fullname'];
    $contactno = $_POST['contactnumber'];
    $userId = $_SESSION['id'];

    if ($users->updateProfile($userId, $name, $contactno)) {
        $_SESSION['toast_message'] = ['type' => 'success', 'text' => 'Perfil atualizado com sucesso!'];
    } else {
        $_SESSION['toast_message'] = ['type' => 'error', 'text' => 'Erro ao atualizar o perfil.'];
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Carrega dados do usuário
$query = mysqli_query($con, "SELECT * FROM users WHERE id='" . $_SESSION['id'] . "'");
$user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <style>
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    min-width: 250px;
    padding: 15px 20px;
    border-radius: 4px;
    color: #fff;
    z-index: 9999;
    display: none;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.toast.success {
    background-color: #28a745;
}
.toast.error {
    background-color: #dc3545;
}
</style>
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
    <?php if (isset($_SESSION['toast_message'])): ?>
    <div class="toast <?php echo $_SESSION['toast_message']['type']; ?>" id="toast">
        <?php echo htmlentities($_SESSION['toast_message']['text']); ?>
    </div>
    <?php unset($_SESSION['toast_message']); ?>
<?php endif; ?>

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
     
        <form id="updateForm" class="register-form" role="form">
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
    <div class="col-6">
        <div class="input-group">
            <input type="password" 
                   name="contactnumber" 
                   id="contactnumber" 
                   class="form-control" 
                   value="<?php echo htmlentities($result['contactno']);?>" 
                   placeholder="(43) 91234-5678" 
                   required />
            <button type="button" class="btn btn-outline-secondary" id="toggleContact">
                <i class="fa fa-eye" id="contactToggleIcon"></i>
            </button>
        </div>
    </div>
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

        <!-- jQuery Mask -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<!-- Toggle Visualização do Contato -->
<script>
$(document).ready(function() {
    // Aplica a máscara
    $('#contactnumber').mask('(00) 00000-0000');

    // Alternar visualização
    $('#toggleContact').click(function() {
        const input = $('#contactnumber');
        const icon = $('#contactToggleIcon');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});
</script>

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("toast");
    if (toast) {
        toast.style.display = "block";
        setTimeout(() => {
            toast.style.display = "none";
        }, 4000);
    }
});
</script>
    </body>
</html>
<?php  ?>
