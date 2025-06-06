<?php
session_start();
include('include/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

if (isset($_GET['id'])) {
    $pid = intval($_GET['id']);
} else {
    header('Location: manage-products.php');
    exit();
}

// Classe Product
class Product {
    private $db;

    public function __construct($con) {
        $this->db = $con;
    }

    public function updateProduct($id, $data) {
        $stmt = $this->db->prepare("UPDATE products SET 
            category = ?, 
            subCategory = ?, 
            productName = ?, 
            productCompany = ?, 
            productPrice = ?, 
            productDescription = ?, 
            shippingCharge = ?, 
            productAvailability = ?, 
            productPriceBeforeDiscount = ?
            WHERE id = ?");

        if (!$stmt) {
            throw new Exception("Erro ao preparar a query: " . $this->db->error);
        }

        $stmt->bind_param(
    	"iissdsssdi", 
    	$data['category'],                  // i
    	$data['subcategory'],              // i
    	$data['productName'],              // s
    	$data['productCompany'],           // s
   	 	$data['productPrice'],             // d
    	$data['productDescription'],       // s
    	$data['shippingCharge'],           // d ← aqui estava s (errado!)
    	$data['productAvailability'],      // s
    	$data['productPriceBeforeDiscount'], // d
    	$id                                // i
);


        return $stmt->execute();
    }
}

// Lógica do formulário
if (isset($_POST['submit']) && isset($_GET['id'])) {
    $pid = intval($_GET['id']);
    $product = new Product($con);

    $data = [
        'category' => intval($_POST['category']),
        'subcategory' => intval($_POST['subcategory']),
        'productName' => $_POST['productName'],
        'productCompany' => $_POST['productCompany'],
        'productPrice' => floatval($_POST['productprice']),
        'productDescription' => $_POST['productDescription'],
        'shippingCharge' => floatval($_POST['productShippingcharge']),
        'productAvailability' => $_POST['productAvailability'],
        'productPriceBeforeDiscount' => floatval($_POST['productpricebd'])
    ];

    try {
        if ($product->updateProduct($pid, $data)) {
            $_SESSION['msg'] = "Produto atualizado com sucesso !!";
        } else {
            $_SESSION['msg'] = "Erro ao atualizar o produto.";
        }
    } catch (Exception $e) {
        $_SESSION['msg'] = "Erro: " . $e->getMessage();
    }

    header("Location: manage-products.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin| Editar Produto</title>
	<link type="text/css" href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link type="text/css" href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
	<link type="text/css" href="css/theme.css" rel="stylesheet">
	<link type="text/css" href="images/icons/css/font-awesome.css" rel="stylesheet">
	<link type="text/css" href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600' rel='stylesheet'>
<script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
<script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);</script>

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
function selectCountry(val) {
$("#search-box").val(val);
$("#suggesstion-box").hide();
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
								<h3>Editar Produto</h3>
							</div>
							<div class="module-body">

									<?php if(isset($_POST['submit']))
{?>
									<div class="alert alert-success">
										<button type="button" class="close" data-dismiss="alert">×</button>
									<strong>Bom trabalho!</strong>	<?php echo htmlentities($_SESSION['msg']);?><?php echo htmlentities($_SESSION['msg']="");?>
									</div>
<?php } ?>


									<?php if(isset($_GET['del']))
{?>
									<div class="alert alert-error">
										<button type="button" class="close" data-dismiss="alert">×</button>
									<strong>Algo deu Errado!</strong> 	<?php echo htmlentities($_SESSION['delmsg']);?><?php echo htmlentities($_SESSION['delmsg']="");?>
									</div>
<?php } ?>

									<br />

			<form class="form-horizontal row-fluid" name="insertproduct" method="post" enctype="multipart/form-data">

<?php 

$query=mysqli_query($con,"select products.*,category.categoryName as catname,category.id as cid,subcategory.subcategory as subcatname,subcategory.id as subcatid from products join category on category.id=products.category join subcategory on subcategory.id=products.subCategory where products.id='$pid'");
$cnt=1;
while($row=mysqli_fetch_array($query))
{
  


?>


<div class="control-group">
<label class="control-label" for="basicinput">Categoria</label>
<div class="controls">
<select name="category" class="span8 tip" onChange="getSubcat(this.value);"  required>
<option value="<?php echo htmlentities($row['cid']);?>"><?php echo htmlentities($row['catname']);?></option> 
<?php $query=mysqli_query($con,"select * from category");
while($rw=mysqli_fetch_array($query))
{
	if($row['catname']==$rw['categoryName'])
	{
		continue;
	}
	else{
	?>

<option value="<?php echo $rw['id'];?>"><?php echo $rw['categoryName'];?></option>
<?php }} ?>
</select>
</div>
</div>

									
<div class="control-group">
<label class="control-label" for="basicinput">Subcategoria</label>
<div class="controls">

<select   name="subcategory"  id="subcategory" class="span8 tip" required>
<option value="<?php echo htmlentities($row['subcatid']);?>"><?php echo htmlentities($row['subcatname']);?></option>
</select>
</div>
</div>


<div class="control-group">
<label class="control-label" for="basicinput">Nome do Produto</label>
<div class="controls">
<input type="text"    name="productName"  placeholder="Digite o nome do produto" value="<?php echo htmlentities($row['productName']);?>" class="span8 tip" >
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Marca do Produto</label>
<div class="controls">
<input type="text"    name="productCompany"  placeholder="Digite a marca do produto" value="<?php echo htmlentities($row['productCompany']);?>" class="span8 tip" required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="basicinput">Preço do produto antes do desconto</label>
<div class="controls">
<input type="text"    name="productpricebd"  placeholder="Digite o preço do produto" value="<?php echo htmlentities($row['productPriceBeforeDiscount']);?>"  class="span8 tip" required>
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Preço do Produto</label>
<div class="controls">
<input type="text"    name="productprice"  placeholder="Digite o preço do Produto" value="<?php echo htmlentities($row['productPrice']);?>" class="span8 tip" required>
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Descrição do Produto </label>
<div class="controls">
<textarea  name="productDescription"  placeholder="Insira a descrição do produto" rows="6" class="span8 tip">
<?php echo htmlentities($row['productDescription']);?>
</textarea>  
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Taxa de Frete do Produto</label>
<div class="controls">
<input type="text"    name="productShippingcharge"  placeholder="Insira o custo de envio do produto" value="<?php echo htmlentities($row['shippingCharge']);?>" class="span8 tip" required>
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Disponibilidade do Produto</label>
<div class="controls">
<select name="productAvailability" id="productAvailability" class="span8 tip" required>
    <option value="In Stock" <?php if($row['productAvailability'] == 'In Stock') echo 'selected'; ?>>Em Estoque</option>
    <option value="Out of Stock" <?php if($row['productAvailability'] == 'Out of Stock') echo 'selected'; ?>>Fora de Estoque</option>
</select>
</div>
</div>



<div class="control-group">
<label class="control-label" for="basicinput">Imagem 1</label>
<div class="controls">
<img src="productimages/<?php echo htmlentities($pid);?>/<?php echo htmlentities($row['productImage1']);?>" width="200" height="100"> <a href="update-image1.php?id=<?php echo $row['id'];?>">Mudar Imagem</a>
</div>
</div>


<div class="control-group">
<label class="control-label" for="basicinput">Imagem 2</label>
<div class="controls">
<img src="productimages/<?php echo htmlentities($pid);?>/<?php echo htmlentities($row['productImage2']);?>" width="200" height="100"> <a href="update-image2.php?id=<?php echo $row['id'];?>">Mudar Imagem</a>
</div>
</div>



<div class="control-group">
<label class="control-label" for="basicinput">Imagem 3</label>
<div class="controls">
<img src="productimages/<?php echo htmlentities($pid);?>/<?php echo htmlentities($row['productImage3']);?>" width="200" height="100"> <a href="update-image3.php?id=<?php echo $row['id'];?>">Mudar Imagem</a>
</div>
</div>
<?php } ?>
	<div class="control-group">
											<div class="controls">
												<button type="submit" name="submit" class="btn btn-primary">Atualizar</button>
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
	<script src="scripts/datatables/jquery.dataTables.js"></script>
	<script>
		$(document).ready(function() {
			$('.datatable-1').dataTable();
			$('.dataTables_paginate').addClass("btn-group datatable-pagination");
			$('.dataTables_paginate > a').wrapInner('<span />');
			$('.dataTables_paginate > a:first-child').append('<i class="icon-chevron-left shaded"></i>');
			$('.dataTables_paginate > a:last-child').append('<i class="icon-chevron-right shaded"></i>');
		} );
	</script>
</body>
<?php  ?>