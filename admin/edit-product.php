<?php   
namespace Admin;
session_start();
include_once('includes/config.php');

// Verifica login
if (strlen($_SESSION["aid"]) == 0) {
    header('location:logout.php');
    exit();
}

// Classe Products com método updateProduct
class Products {
    private $con;
    private $adminId;

    public function __construct($dbConnection, $adminId) {
        $this->con = $dbConnection;
        $this->adminId = $adminId;
    }

    public function updateProduct($id, $data) {
        $query = "UPDATE products 
                  SET category=?, subCategory=?, productName=?, productCompany=?, 
                      productPrice=?, productDescription=?, shippingCharge=?, 
                      productAvailability=?, productPriceBeforeDiscount=?, lastUpdatedBy=? 
                  WHERE id=?";

        $stmt = $this->con->prepare($query);
        if (!$stmt) {
            throw new \Exception("Erro ao preparar: " . $this->con->error);
        }

        $stmt->bind_param(
            "iissdssdiii",
            $data['category'],
            $data['subcategory'],
            $data['productName'],
            $data['productCompany'],
            $data['productprice'],
            $data['productDescription'],
            $data['productShippingcharge'],
            $data['productAvailability'],
            $data['productpricebd'],
            $this->adminId,
            $id
        );

        return $stmt->execute();
    }
}

// Lógica ao enviar o formulário
if (isset($_POST['submit'])) {
    $pid = intval($_GET['id']);
    $product = new Products($con, $_SESSION['aid']);

    $data = [
        'category' => $_POST['category'],
        'subcategory' => $_POST['subcategory'],
        'productName' => $_POST['productName'],
        'productCompany' => $_POST['productCompany'],
        'productprice' => $_POST['productprice'],
        'productDescription' => $_POST['productDescription'],
        'productShippingcharge' => $_POST['productShippingcharge'],
        'productAvailability' => $_POST['productAvailability'],
        'productpricebd' => $_POST['productpricebd']
    ];

    try {
        if ($product->updateProduct($pid, $data)) {
            echo "<script>alert('Detalhes do produto atualizados com sucesso');</script>";
            echo "<script>window.location.href='manage-products.php'</script>";
            exit();
        } else {
            echo "<script>alert('Erro ao atualizar o produto.');</script>";
        }
    } catch (\Exception $e) {
        echo "<script>alert('Erro: " . $e->getMessage() . "');</script>";
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
        <title>Portal de Compras | Editar Produto</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/all.min.js" crossorigin="anonymous"></script>
        <script src="js/jquery-3.5.1.min.js"></script>
   <script>
function getSubcat(val) {
    $.ajax({
    type: "POST",
    url: "get_subcat.php",
    data:'cat_id='+val,
    success: function(data){
        $("#subcategory").html(data);
    }
    });
}
</script>   

    </head>
    <body>
   <?php include_once('includes/header.php');?>
        <div id="layoutSidenav">
   <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Editar Produto</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Editar Produto</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-body">


<?php 
$pid=intval($_GET['id']);
$query=mysqli_query($con,"select products.id as pid,products.productImage1,products.productImage2,products.productImage3,products.productName,category.categoryName,subcategory.subcategoryName as subcatname,products.postingDate,products.updationDate,subcategory.id as subid,tbladmin.username,category.id as catid,products.productCompany,products.productPrice,products.productPriceBeforeDiscount,products.productAvailability,products.productDescription,products.shippingCharge from products join subcategory on products.subCategory=subCategory.id join category on products.category=category.id join tbladmin on tbladmin.id=products.addedBy where  products.id='$pid' order by pid desc");
while($row=mysqli_fetch_array($query))
{
?>                                 
<form  method="post" enctype="multipart/form-data">                                
<div class="row">
<div class="col-2">Nome da Categoria</div>
<div class="col-6">
<select name="category" id="category" class="form-control" onChange="getSubcat(this.value);" required>
<option value="<?php echo htmlentities($row['catid']);?>"><?php echo htmlentities($row['categoryName']);?></option> 
<?php $ret=mysqli_query($con,"select * from category");
while($result=mysqli_fetch_array($ret))
{?>

<option value="<?php echo $result['id'];?>"><?php echo $result['categoryName'];?></option>
<?php } ?>
</select>    
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Nome da Subcategoria</div>
<div class="col-6"><select   name="subcategory"  id="subcategory" class="form-control" required>
    <option value="<?php echo htmlentities($row['subid']);?>"><?php echo htmlentities($row['subcatname']);?>
</select>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Nome do Produto</div>
<div class="col-6"><input type="text"    name="productName"  value="<?php echo htmlentities($row['productName']);?>" class="form-control" required>
</select>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Marca do Produto</div>
<div class="col-6"><input type="text"    name="productCompany"  value="<?php echo htmlentities($row['productCompany']);?>" class="form-control" required>
</select>
</div>
</div>

<div class="row" style="margin-top:1%;">
    <div class="col-2">Preço do produto antes do desconto</div>
    <div class="col-6">
        <input type="text" name="productpricebd"  
               value="<?php echo number_format((float)$row['productPriceBeforeDiscount'], 2, ',', '.'); ?>" 
               class="form-control" required>
    </div>
</div>

<div class="row" style="margin-top:1%;">
    <div class="col-2">Preço do produto após desconto (preço de venda)</div>
    <div class="col-6">
        <input type="text" name="productprice"  
               value="<?php echo number_format((float)$row['productPrice'], 2, ',', '.'); ?>" 
               class="form-control" required>
    </div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Descrição do Produto</div>
<div class="col-6"><textarea  name="productDescription"  placeholder="Enter Product Description" rows="6" class="form-control"><?php echo $row['productDescription'];?></textarea>
</div>
</div>

<div class="row" style="margin-top:1%;">
    <div class="col-2">Taxa de Frete do Produto</div>
    <div class="col-4">
        <div class="input-group">
            <span class="input-group-text">R$</span>
            <input type="text" name="productShippingcharge" placeholder="Digite a Taxa de Frete" class="form-control" required>
        </div>
    </div>
</div>

<div class="row" style="margin-top:1%;">
    <div class="col-2">Disponibilidade do Produto</div>
    <div class="col-6">
        <select name="productAvailability" id="productAvailability" class="form-control" required>
            <?php $pa = $row['productAvailability']; ?>
            <option value="In Stock" <?php echo ($pa == 'In Stock') ? 'selected' : ''; ?>>Em Estoque</option>
            <option value="Out of Stock" <?php echo ($pa == 'Out of Stock') ? 'selected' : ''; ?>>Fora de Estoque</option>
        </select>
    </div>
</div>


<div class="row" style="margin-top:1%;">
<div class="col-2">Imagem 1</div>
<div class="col-6"><img src="productimages/<?php echo htmlentities($row['productImage1']);?>" width="250"><br />
    <a href="change-image1.php?id=<?php echo $row['pid'];?>">Mudar Imagem</a>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Imagem 2</div>
<div class="col-6"><img src="productimages/<?php echo htmlentities($row['productImage2']);?>" width="250"><br />
    <a href="change-image2.php?id=<?php echo $row['pid'];?>">Mudar Imagem</a>
</div>
</div>


<div class="row" style="margin-top:1%;">
<div class="col-2">Imagem 3</div>
<div class="col-6"><img src="productimages/<?php echo htmlentities($row['productImage3']);?>" width="250"><br />
    <a href="change-image3.php?id=<?php echo $row['pid'];?>">Mudar Imagem</a>
</div>
</div>

<div class="row">
<div class="col-2"><button type="submit" name="submit" class="btn btn-primary">Atualizar</button></div>
</div>

</form>
      
      <?php } ?>                      </div>
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
