<?php
namespace Models;
session_start();
include_once('includes/config.php');
error_reporting(0);

if (strlen($_SESSION["aid"]) == 0) {
    header('location:logout.php');
    exit();
}

// Classe Products com método para adicionar subcategoria
class Products {
    private $con;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
    }

    public function addSubcategory($categoryId, $subcategoryName, $createdBy) {
        $stmt = $this->con->prepare("INSERT INTO subcategory (categoryid, subcategoryName, createdBy) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $categoryId, $subcategoryName, $createdBy);
        return $stmt->execute();
    }
}

// Se o formulário foi enviado
if (isset($_POST['submit'])) {
    $category = $_POST['category'];
    $subcat = $_POST['subcategory'];
    $createdBy = $_SESSION['aid'];

    $product = new Products($con);
    if ($product->addSubcategory($category, $subcat, $createdBy)) {
        echo "<script>alert('Subcategoria adicionada com sucesso');</script>";
        echo "<script>window.location.href='manage-subcategories.php'</script>";
    } else {
        echo "<script>alert('Erro ao adicionar subcategoria.');</script>";
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
        <title>Portal de Compras | Adicionar Subcategorias</title>
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
                        <h1 class="mt-4">Adicionar Subcategoria</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Adicionar Subcategoria</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-body">
<form  method="post">                                
<div class="row">
<div class="col-2">Nome da Categoria</div>
<div class="col-4">
<select name="category" class="form-control" required>
<option value="">Selecionar Categoria</option> 
<?php $query=mysqli_query($con,"select * from category");
while($row=mysqli_fetch_array($query))
{?>

<option value="<?php echo $row['id'];?>"><?php echo $row['categoryName'];?></option>
<?php } ?>
</select>    
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">None da Subcategoria</div>
<div class="col-4"><input type="text" placeholder="Enter SubCategory Name"  name="subcategory" class="form-control" required></div>
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