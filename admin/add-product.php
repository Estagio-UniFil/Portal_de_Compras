<?php
namespace Models;
session_start();
include_once('includes/config.php');
error_reporting(0);

if (strlen($_SESSION["aid"]) == 0) {
    header('location:logout.php');
    exit();
}

// Classe Products com todas as funcionalidades
class Products {
    private $con;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
    }

    private function renameImage($filename) {
        $extension = substr($filename, -4);
        return md5($filename . time()) . $extension;
    }

    private function uploadImages($files) {
        $uploaded = [];

        for ($i = 1; $i <= 3; $i++) {
            $key = "productimage{$i}";
            $originalName = $files[$key]["name"];
            $newName = $this->renameImage($originalName);
            move_uploaded_file($files[$key]["tmp_name"], "productimages/" . $newName);
            $uploaded[] = $newName;
        }

        return $uploaded;
    }

    public function addProduct($data, $files, $addedBy) {
        list($img1, $img2, $img3) = $this->uploadImages($files);

        $stmt = $this->con->prepare("
            INSERT INTO products (
                category, subCategory, productName, productCompany, 
                productPrice, productDescription, shippingCharge, 
                productAvailability, productImage1, productImage2, productImage3, 
                productPriceBeforeDiscount, addedBy
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "iissdssssssdi",
            $data['category'],
            $data['subcategory'],
            $data['productName'],
            $data['productCompany'],
            $data['productprice'],
            $data['productDescription'],
            $data['productShippingcharge'],
            $data['productAvailability'],
            $img1,
            $img2,
            $img3,
            $data['productpricebd'],
            $addedBy
        );

        return $stmt->execute();
    }
}

// Se o formulário foi enviado
if (isset($_POST['submit'])) {
    $addedBy = $_SESSION['aid'];

    $productData = [
        'category' => $_POST['category'],
        'subcategory' => $_POST['subcategory'],
        'productName' => $_POST['productName'],
        'productCompany' => $_POST['productCompany'],
        'productprice' => $_POST['productprice'],
        'productpricebd' => $_POST['productpricebd'],
        'productDescription' => $_POST['productDescription'],
        'productShippingcharge' => $_POST['productShippingcharge'],
        'productAvailability' => $_POST['productAvailability']
    ];

    $product = new Products($con);

    if ($product->addProduct($productData, $_FILES, $addedBy)) {
        echo "<script>alert('Produto adicionado com Sucesso');</script>";
        echo "<script>window.location.href='manage-subcategories.php'</script>";
    } else {
        echo "<script>alert('Erro ao adicionar o produto.');</script>";
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
        <title>Portal de Compras | Adicionar Produto</title>
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
                        <h1 class="mt-4">Adicionar Produto</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Adicionar Produto</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-body">
<form  method="post" enctype="multipart/form-data">                                
<div class="row">
<div class="col-2">Nome da Categoria</div>
<div class="col-4">
<select name="category" id="category" class="form-control" onChange="getSubcat(this.value);" required>
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
<div class="col-2">Nome da Subcategoria</div>
<div class="col-4"><select   name="subcategory"  id="subcategory" class="form-control" required>
</select>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Nome do Produto</div>
<div class="col-4"><input type="text"    name="productName"  placeholder="Digite o Nome do Produto" class="form-control" required>
</select>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Nome da Marca</div>
<div class="col-4"><input type="text"    name="productCompany"  placeholder="Digite a Marca do Produto" class="form-control" required>
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
<div class="col-4"><textarea  name="productDescription"  placeholder="Digite a Descrição do Produto" rows="6" class="form-control"></textarea>
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
<div class="col-4"><select   name="productAvailability"  id="productAvailability" class="form-control" required>
<option value="Em Estoque">Em Estoque</option>
<option value="Fora de Estoque">Fora de Estoque</option>
</select>
</select>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Imagem em Destaque do Produto</div>
<div class="col-4"><input type="file" name="productimage1" id="productimage1"  class="form-control" accept="image/*" title="Aceitar apenas imagens" required>
</div>
</div>

<div class="row" style="margin-top:1%;">
<div class="col-2">Imagem do Produto 2</div>
<div class="col-4"><input type="file" name="productimage2"  class="form-control" accept="image/*" title="Aceitar apenas imagens" required>
</div>
</div>


<div class="row" style="margin-top:1%;">
<div class="col-2">Imagem do Produto 3</div>
<div class="col-4"><input type="file" name="productimage3"  class="form-control" accept="image/*" title="Aceitar apenas imagens" required>
</div>
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
