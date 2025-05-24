<?php
session_start();
include('include/config.php');

// Verifica login
if(strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit;
}

// Classe de Produto
class Products {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    private function sanitizeFileName($filename) {
        return preg_replace('/[^a-zA-Z0-9.\-_]/', '', $filename);
    }

    public function addProduct($data, $files) {
        $category = $data['category'];
        $subcat = $data['subcategory'];
        $productname = $data['productName'];
        $productcompany = $data['productCompany'];
        $productprice = $data['productprice'];
        $productpricebd = $data['productpricebd'];
        $productdescription = $data['productDescription'];
        $productscharge = $data['productShippingcharge'];
        $productavailability = $data['productAvailability'];

        $productimage1 = $this->sanitizeFileName($files["productimage1"]["name"]);
        $productimage2 = $this->sanitizeFileName($files["productimage2"]["name"]);
        $productimage3 = $this->sanitizeFileName($files["productimage3"]["name"]);

        $sql = mysqli_query($this->con, "
            INSERT INTO products (
                category, subCategory, productName, productCompany, productPrice,
                productDescription, shippingCharge, productAvailability,
                productImage1, productImage2, productImage3, productPriceBeforeDiscount
            ) VALUES (
                '$category', '$subcat', '$productname', '$productcompany', '$productprice',
                '$productdescription', '$productscharge', '$productavailability',
                '$productimage1', '$productimage2', '$productimage3', '$productpricebd'
            )
        ");

        if ($sql) {
            $productid = mysqli_insert_id($this->con);
            $dir = "productimages/$productid";
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $upload1 = move_uploaded_file($files["productimage1"]["tmp_name"], "$dir/$productimage1");
            $upload2 = move_uploaded_file($files["productimage2"]["tmp_name"], "$dir/$productimage2");
            $upload3 = move_uploaded_file($files["productimage3"]["tmp_name"], "$dir/$productimage3");

            if ($upload1 && $upload2) {
                $_SESSION['msg'] = "Produto registrado com sucesso!";
            } else {
                $_SESSION['msg'] = "Produto registrado, mas houve erro ao fazer upload das imagens.";
            }
        } else {
            $_SESSION['msg'] = "Erro ao registrar produto.";
        }
    }
}

// Chama a função se o formulário for enviado
if(isset($_POST['submit'])) {
    $product = new Products($con);
    $product->addProduct($_POST, $_FILES);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin| Registrar Produto</title>
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
								<h3>Registrar Produto</h3>
							</div>
							<div class="module-body">

							<?php if(isset($_SESSION['msg']) && $_SESSION['msg'] != "") { ?>
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Sucesso!</strong> 
        <?php echo htmlentities($_SESSION['msg']); ?>
        <?php $_SESSION['msg'] = ""; ?>
    </div>
<?php } ?>

<?php if(isset($_GET['del']) && isset($_SESSION['delmsg']) && $_SESSION['delmsg'] != "") { ?>
    <div class="alert alert-warning"> <!-- Alterado de "alert-error" para "alert-warning" -->
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong><i class="fa fa-exclamation-triangle"></i> Atenção:</strong>  
        <?php echo htmlentities($_SESSION['delmsg']); ?> 
        <?php $_SESSION['delmsg'] = ""; ?>
    </div>
<?php } ?>

									<br />

			<form class="form-horizontal row-fluid" name="insertproduct" method="post" enctype="multipart/form-data">

<div class="control-group">
<label class="control-label" for="basicinput">Categoria</label>
<div class="controls">
<select name="category" class="span8 tip" onChange="getSubcat(this.value);"  required>
<option value="">Selecionar Categoria</option> 
<?php $query=mysqli_query($con,"select * from category");
while($row=mysqli_fetch_array($query))
{?>

<option value="<?php echo $row['id'];?>"><?php echo $row['categoryName'];?></option>
<?php } ?>
</select>
</div>
</div>

									
<div class="control-group">
<label class="control-label" for="basicinput">Subcategoria</label>
<div class="controls">
<select   name="subcategory"  id="subcategory" class="span8 tip" required>
</select>
</div>
</div>


<div class="control-group">
<label class="control-label" for="basicinput">Nome do Produto</label>
<div class="controls">
<input type="text"    name="productName"  placeholder="Digite o Nome do Produto" class="span8 tip" required>
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Marca do Produto</label>
<div class="controls">
<input type="text"    name="productCompany"  placeholder="Digite o Nome da Marca do Produto" class="span8 tip" required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="basicinput">Preço do Produto antes do Desconto</label>
<div class="controls">
<input type="text"    name="productpricebd"  placeholder="Digite o Preço do Produto" class="span8 tip" required>
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Preço do Produto após Desconto(Valor de Venda)</label>
<div class="controls">
<input type="text"    name="productprice"  placeholder="Digite o Preço do Produto" class="span8 tip" required>
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Descrição do Produto</label>
<div class="controls">
<textarea  name="productDescription"  placeholder="Digite a Descrição do Produto" rows="6" class="span8 tip">
</textarea>  
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Preço de Frete</label>
<div class="controls">
<input type="text"    name="productShippingcharge"  placeholder="Preço de Frete" class="span8 tip" required>
</div>
</div>

<div class="control-group">
<label class="control-label" for="basicinput">Disponibilidade de Prouto</label>
<div class="controls">
<select   name="productAvailability"  id="productAvailability" class="span8 tip" required>
<option value="">Selecionar</option>
<option value="In Stock">Em Estoque</option>
<option value="Out of Stock">Fora de Estoque</option>
</select>
</div>
</div>



<div class="control-group">
<label class="control-label" for="basicinput">Imagem 1</label>
<div class="controls">
<input type="file" name="productimage1" id="productimage1" value="" class="span8 tip" required>
</div>
</div>


<div class="control-group">
<label class="control-label" for="basicinput">Imagem 2</label>
<div class="controls">
<input type="file" name="productimage2"  class="span8 tip" required>
</div>
</div>



<div class="control-group">
<label class="control-label" for="basicinput">Imagem 3</label>
<div class="controls">
<input type="file" name="productimage3"  class="span8 tip">
</div>
</div>

	<div class="control-group">
											<div class="controls">
												<button type="submit" name="submit" class="btn btn-primary">Registrar</button>
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