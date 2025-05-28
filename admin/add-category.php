<?php
namespace Models;
session_start();
include_once('includes/config.php');
error_reporting(0);

// Verifica se o admin está logado
if (strlen($_SESSION["aid"]) == 0) {
    header('location:logout.php');
    exit();
}


// Classe para manipular categorias
class Category {
    private $con;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
    }

    public function addCategory($name, $description, $createdBy) {
        $stmt = $this->con->prepare("INSERT INTO category (categoryName, categoryDescription, createdBy) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $createdBy);
        return $stmt->execute();
    }
}

// Processa o envio do formulário
if (isset($_POST['submit'])) {
    $categoryName = $_POST['category'];
    $categoryDescription = $_POST['description'];
    $createdBy = $_SESSION['aid'];

    $category = new Category($con);
    if ($category->addCategory($categoryName, $categoryDescription, $createdBy)) {
        echo "<script>alert('Categoria foi adicionada com Sucesso');</script>";
        echo "<script>window.location.href='manage-categories.php'</script>";
    } else {
        echo "<script>alert('Erro ao adicionar categoria');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Portal de Compras | Adicionar Categoria</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body>
   <?php include_once('includes/header.php');?>
        <div id="layoutSidenav">
   <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Adicionar Categoria</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Adicionar Cateogria</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-body">
<form  method="post">                                
<div class="row">
<div class="col-2">Nome da Categoria</div>
<div class="col-4"><input type="text" placeholder="Enter category Name"  name="category" class="form-control" required></div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Descrição da Categoria</div>
<div class="col-4"><textarea placeholder="Enter category Name"  name="description" class="form-control" required></textarea></div>
</div>

<div class="row">
<div class="col-2"><button type="submit" name="submit" class="btn btn-primary">Enviar</button></div>
</div>

</form>
                            </div>
                        </div>
                    </div>
                </main>
          <?php include_once('includes/footer.php');?>
            </div>
        </div>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
        
    </body>
</html>
<?php  ?>
