
<?php
session_start();
include('include/config.php');
if(strlen($_SESSION['alogin'])==0)
	{	
header('location:index.php');
}
else{
date_default_timezone_set('America/Sao_Paulo');// change according timezone
$currentTime = date( 'd-m-Y h:i:s A', time () );


class Category {
    private mysqli $con;

    public function __construct(mysqli $db) {
        $this->con = $db;
    }

    public function updateCategory(int $id, string $name, string $description): bool {
        $currentTime = date('Y-m-d H:i:s');
        $stmt = $this->con->prepare("UPDATE category SET categoryName = ?, categoryDescription = ?, updationDate = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $description, $currentTime, $id);
        return $stmt->execute();
    }
}


if (isset($_POST['submit'])) {
    $categoryName = $_POST['category'];
    $description = $_POST['description'];
    $id = intval($_GET['id']);

    $categoryManager = new Category($con);
    if ($categoryManager->updateCategory($id, $categoryName, $description)) {
        $_SESSION['msg'] = "Categoria atualizada com sucesso!";
    } else {
        $_SESSION['msg'] = "Erro ao atualizar categoria.";
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin| Categoria</title>
	<link type="text/css" href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link type="text/css" href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
	<link type="text/css" href="css/theme.css" rel="stylesheet">
	<link type="text/css" href="images/icons/css/font-awesome.css" rel="stylesheet">
	<link type="text/css" href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600' rel='stylesheet'>
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
								<h3>Categoria</h3>
							</div>
							<div class="module-body">

									<?php if(isset($_POST['submit']))
{?>
									<div class="alert alert-success">
										<button type="button" class="close" data-dismiss="alert">×</button>
									<strong>Bom trabalho!</strong>	<?php echo htmlentities($_SESSION['msg']);?><?php echo htmlentities($_SESSION['msg']="");?>
									</div>
<?php } ?>


									<br />

			<form class="form-horizontal row-fluid" name="Category" method="post" >
<?php
$id=intval($_GET['id']);
$query=mysqli_query($con,"select * from category where id='$id'");
while($row=mysqli_fetch_array($query))
{
?>									
<div class="control-group">
<label class="control-label" for="basicinput">Nome da Categoria</label>
<div class="controls">
<input type="text" placeholder="Enter category Name"  name="category" value="<?php echo  htmlentities($row['categoryName']);?>" class="span8 tip" required>
</div>
</div>


<div class="control-group">
											<label class="control-label" for="basicinput">Descrição</label>
											<div class="controls">
												<textarea class="span8" name="description" rows="5"><?php echo  htmlentities($row['categoryDescription']);?></textarea>
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
<?php } ?>