<?php
session_start();
include('includes/config.php');
error_reporting(0);

if(isset($_POST['ordersubmit'])) {
	if(strlen($_SESSION['login']) == 0) {   
		header('location:login.php');
		exit();
	} else {
		$quantity = $_POST['quantity'];
		$pdd = $_SESSION['pid'];
		$value = array_combine($pdd, $quantity);

		foreach($value as $qty => $val34){
			mysqli_query($con, "INSERT INTO orders(userId, productId, quantity) VALUES('".$_SESSION['id']."', '$qty', '$val34')");
		}

		unset($_SESSION['cart']);
		header('location:payment-method.php');
		exit();
	}
}



if (isset($_SESSION['msg_success'])): ?>
	<script>
	  window.addEventListener('DOMContentLoaded', function() {
		toastr.success("<?php echo addslashes($_SESSION['msg_success']); ?>");
	  });
	</script>
	<?php unset($_SESSION['msg_success']); ?>
	<?php endif; 
error_reporting(0);
include('includes/config.php');
if(isset($_POST['submit']))
		if (!empty($_SESSION['cart'])) {
			foreach ($_POST['quantity'] as $key => $val) {
				if ($val == 0) {
					unset($_SESSION['cart'][$key]);
				} else {
					$_SESSION['cart'][$key]['quantity'] = $val;
				}
			}
			$_SESSION['msg_success'] = "Seu carrinho foi atualizado com sucesso!";
			header("Location: my-cart.php");
			exit();
		}
// Code for Remove a Product from Cart
if (isset($_POST['remove_selected']) && isset($_POST['remove_code']) && !empty($_SESSION['cart'])) {
	foreach ($_POST['remove_code'] as $key) {
		unset($_SESSION['cart'][$key]);
	}
	$_SESSION['msg_success'] = "Itens removidos do carrinho com sucesso!";
	header("Location: my-cart.php");
	exit();
}
// code for insert product in order table


if(isset($_POST['ordersubmit'])) 
{
	
if(strlen($_SESSION['login'])==0)
    {   
header('location:login.php');
}
else{

	$quantity=$_POST['quantity'];
	$pdd=$_SESSION['pid'];
	$value=array_combine($pdd,$quantity);


		foreach($value as $qty=> $val34){

			unset($_SESSION['cart']);

			header('location:payment-method.php');
		exit();
	}
}



mysqli_query($con,"insert into orders(userId,productId,quantity) values('".$_SESSION['id']."','$qty','$val34')");
header('location:payment-method.php');
}



// code for billing address updation
if(isset($_POST['update']))
{
    $baddress = $_POST['billingaddress'];
    $bstate = $_POST['bilingstate'];
    $bcity = $_POST['billingcity'];
    $bpincode = $_POST['billingpincode'];
    
    $query = mysqli_query($con,"UPDATE users SET billingAddress='$baddress', billingState='$bstate', billingCity='$bcity', billingPincode='$bpincode' WHERE id='".$_SESSION['id']."'");

    if($query) {
        $_SESSION['msg_success'] = "Endereço de cobrança atualizado com sucesso!";
    } else {
        $_SESSION['msg_error'] = "Erro ao atualizar o endereço de cobrança.";
    }

    header("Location: my-cart.php");
    exit();
}


// code for Shipping address updation
if(isset($_POST['shipupdate']))
{
    $saddress = $_POST['shippingaddress'];
    $sstate = $_POST['shippingstate'];
    $scity = $_POST['shippingcity'];
    $spincode = $_POST['shippingpincode'];
    
    $query = mysqli_query($con,"UPDATE users SET shippingAddress='$saddress', shippingState='$sstate', shippingCity='$scity', shippingPincode='$spincode' WHERE id='".$_SESSION['id']."'");

    if($query) {
        $_SESSION['msg_success'] = "Endereço de envio atualizado com sucesso!";
    } else {
        $_SESSION['msg_error'] = "Erro ao atualizar o endereço de envio.";
    }

    header("Location: my-cart.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
		<!-- Meta -->
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">
	    <meta name="keywords" content="MediaCenter, Template, eCommerce">
	    <meta name="robots" content="all">

	    <title>Meu Carrinho</title>
	    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
	    <link rel="stylesheet" href="assets/css/main.css">
	    <link rel="stylesheet" href="assets/css/red.css">
	    <link rel="stylesheet" href="assets/css/owl.carousel.css">
		<link rel="stylesheet" href="assets/css/owl.transitions.css">
		<!--<link rel="stylesheet" href="assets/css/owl.theme.css">-->
		<link href="assets/css/lightbox.css" rel="stylesheet">
		<link rel="stylesheet" href="assets/css/animate.min.css">
		<link rel="stylesheet" href="assets/css/rateit.css">
		<link rel="stylesheet" href="assets/css/bootstrap-select.min.css">

		<!-- Demo Purpose Only. Should be removed in production -->
		<link rel="stylesheet" href="assets/css/config.css">

		<link href="assets/css/green.css" rel="alternate stylesheet" title="Green color">
		<link href="assets/css/blue.css" rel="alternate stylesheet" title="Blue color">
		<link href="assets/css/red.css" rel="alternate stylesheet" title="Red color">
		<link href="assets/css/orange.css" rel="alternate stylesheet" title="Orange color">
		<link href="assets/css/dark-green.css" rel="alternate stylesheet" title="Darkgreen color">
		<!-- Demo Purpose Only. Should be removed in production : END -->

		
		<!-- Icons/Glyphs -->
		<link rel="stylesheet" href="assets/css/font-awesome.min.css">

        <!-- Fonts --> 
		<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet' type='text/css'>
		
		<!-- Favicon -->
		<link rel="shortcut icon" href="assets/images/favicon.ico">

		<!-- HTML5 elements and media queries Support for IE8 : HTML5 shim and Respond.js -->
		<!--[if lt IE 9]>
			<script src="assets/js/html5shiv.js"></script>
			<script src="assets/js/respond.min.js"></script>
		<![endif]-->

	</head>
    <body class="cnt-home">
		<!-- jQuery + Toastr -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function () {
  $('.update-cart-btn').click(function () {
    const productId = $(this).data('id');
    const quantity = $(`.quantity-input[data-id="${productId}"]`).val();

    $.post('update-cart.php', { id: productId, quantity: quantity }, function (data) {
      if (data.success) {
        toastr.success(data.msg);
        setTimeout(() => {
          location.reload(); // ou você pode atualizar somente o total sem reload
        }, 1000);
      } else {
        toastr.error(data.msg);
      }
    }, 'json');
  });
});
</script>
	
		<!-- ============================================== HEADER ============================================== -->
<header class="header-style-1">
<?php include('includes/top-header.php');?>
<?php include('includes/main-header.php');?>
<?php include('includes/menu-bar.php');?>
</header>
<!-- ============================================== HEADER : END ============================================== -->
<div class="breadcrumb">
	<div class="container">
		<div class="breadcrumb-inner">
			<ul class="list-inline list-unstyled">
				<li><a href="#">Home</a></li>
				<li class='active'>Carrinho de Compras</li>
			</ul>
		</div><!-- /.breadcrumb-inner -->
	</div><!-- /.container -->
</div><!-- /.breadcrumb -->

<div class="body-content outer-top-xs">
	<div class="container">
		<div class="row inner-bottom-sm">
			<div class="shopping-cart">
				<div class="col-md-12 col-sm-12 shopping-cart-table ">
	<div class="table-responsive">
<form name="cart" method="post">	
<?php
if(!empty($_SESSION['cart'])){
	?>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th class="cart-romove item">Remover</th>
					<th class="cart-description item">Imagem</th>
					<th class="cart-product-name item">Nome do Produto</th>
			
					<th class="cart-qty item">Quantidade</th>
					<th class="cart-sub-total item">Preço por Unidade</th>
					<th class="cart-sub-total item">Taxa de Frete</th>
					<th class="cart-total last-item">Total Geral</th>
				</tr>
			</thead><!-- /thead -->
			<tfoot>
				<tr>
					<td colspan="7">
						<div class="shopping-cart-btn">
							<span class="">
								<a href="index.php" class="btn btn-upper btn-primary outer-left-xs">Continuar Comprando</a>
								<input type="submit" name="submit" value="Atualizar Carrinho" class="btn btn-upper btn-primary pull-right outer-right-xs">
								<input type="submit" name="remove_selected" value="Remover Selecionados" class="btn btn-upper btn-primary pull-right outer-right-xs">

							</span>
						</div><!-- /.shopping-cart-btn -->
					</td>
				</tr>
			</tfoot>
			<tbody>
 <?php
 $pdtid=array();
    $sql = "SELECT * FROM products WHERE id IN(";
			foreach($_SESSION['cart'] as $id => $value){
			$sql .=$id. ",";
			}
			$sql=substr($sql,0,-1) . ") ORDER BY id ASC";
			$query = mysqli_query($con,$sql);
			$totalprice=0;
			$totalqunty=0;
			if(!empty($query)){
			while($row = mysqli_fetch_array($query)){
				$quantity=$_SESSION['cart'][$row['id']]['quantity'];
				$subtotal= $_SESSION['cart'][$row['id']]['quantity']*$row['productPrice']+$row['shippingCharge'];
				$totalprice += $subtotal;
				$_SESSION['qnty']=$totalqunty+=$quantity;

				array_push($pdtid,$row['id']);
//print_r($_SESSION['pid'])=$pdtid;exit;
	?>

				<tr>
					<td class="romove-item"><input type="checkbox" name="remove_code[]" value="<?php echo htmlentities($row['id']);?>" /></td>
					<td class="cart-image">
						<a class="entry-thumbnail" href="detail.html">
						    <img src="admin/productimages/<?php echo $row['id'];?>/<?php echo $row['productImage1'];?>" alt="" width="114" height="146">
						</a>
					</td>
					<td class="cart-product-name-info">
						<h4 class='cart-product-description'><a href="product-details.php?pid=<?php echo htmlentities($pd=$row['id']);?>" ><?php echo $row['productName'];

$_SESSION['sid']=$pd;
						 ?></a></h4>
						<div class="row">
							<div class="col-sm-4">
								<div class="rating rateit-small"></div>
							</div>
							<div class="col-sm-8">
<?php $rt=mysqli_query($con,"select * from productreviews where productId='$pd'");
$num=mysqli_num_rows($rt);
{
?>
								<div class="reviews">
									( <?php echo htmlentities($num);?> Reviews )
								</div>
								<?php } ?>
							</div>
						</div><!-- /.row -->
						
					</td>
					<td class="cart-product-quantity">
  <div class="quant-input d-flex align-items-center">
  <input type="number" min="0" class="form-control" name="quantity[<?php echo $row['id']; ?>]" value="<?php echo $_SESSION['cart'][$row['id']]['quantity']; ?>" />
  </div>
</td>
					<td class="cart-product-sub-total">
    <span class="cart-sub-total-price">
        R$ <?php echo number_format($row['productPrice'], 2, ',', '.'); ?>
    </span>
</td>

<td class="cart-product-sub-total">
    <span class="cart-sub-total-price">
        R$ <?php echo number_format($row['shippingCharge'], 2, ',', '.'); ?>
    </span>
</td>


					<td class="cart-product-grand-total"><span class="cart-grand-total-price"><?php echo ($_SESSION['cart'][$row['id']]['quantity']*$row['productPrice']+$row['shippingCharge']); ?></span></td>
				</tr>

				<?php } }
$_SESSION['pid']=$pdtid;
				?>
				
			</tbody><!-- /tbody -->
		</table><!-- /table -->
		
	</div>
</div><!-- /.shopping-cart-table -->			<div class="col-md-4 col-sm-12 estimate-ship-tax">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>
					<span class="estimate-title">Endereço para Envio</span>
				</th>
			</tr>
		</thead>
		<tbody>
				<tr>
					<td>
						<div class="form-group">
<?php
$query=mysqli_query($con,"select * from users where id='".$_SESSION['id']."'");
while($row=mysqli_fetch_array($query))
{
?>

<div class="form-group">
					    <label class="info-title" for="Billing Address">Endereço de Cobrança<span>*</span></label>
					    <textarea class="form-control unicase-form-control text-input"  name="billingaddress" required="required"><?php echo $row['billingAddress'];?></textarea>
					  </div>



						<div class="form-group">
					    <label class="info-title" for="Billing State ">Estado de Cobrança <span>*</span></label>
			 <input type="text" class="form-control unicase-form-control text-input" id="bilingstate" name="bilingstate" value="<?php echo $row['billingState'];?>" required>
					  </div>
					  <div class="form-group">
					    <label class="info-title" for="Billing City">Cidade de Cobrança<span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" id="billingcity" name="billingcity" required="required" value="<?php echo $row['billingCity'];?>" >
					  </div>
 <div class="form-group">
					    <label class="info-title" for="Billing Pincode">CEP de Cobrança <span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" id="billingpincode" name="billingpincode" required="required" value="<?php echo $row['billingPincode'];?>" >
					  </div>


					  <button type="submit" name="update" class="btn-upper btn btn-primary checkout-page-button">Atualizar</button>
			
					<?php } ?>
		
						</div>
					
					</td>
				</tr>
		</tbody><!-- /tbody -->
	</table><!-- /table -->
</div>

<div class="col-md-4 col-sm-12 estimate-ship-tax">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>
					<span class="estimate-title">Endereço de Cobrança</span>
				</th>
			</tr>
		</thead>
		<tbody>
				<tr>
					<td>
						<div class="form-group">
		<?php
$query=mysqli_query($con,"select * from users where id='".$_SESSION['id']."'");
while($row=mysqli_fetch_array($query))
{
?>

<div class="form-group">
					    <label class="info-title" for="Shipping Address">Endereço para Envio<span>*</span></label>
					    <textarea class="form-control unicase-form-control text-input"  name="shippingaddress" required="required"><?php echo $row['shippingAddress'];?></textarea>
					  </div>



						<div class="form-group">
					    <label class="info-title" for="Billing State ">Estado de Envio <span>*</span></label>
			 <input type="text" class="form-control unicase-form-control text-input" id="shippingstate" name="shippingstate" value="<?php echo $row['shippingState'];?>" required>
					  </div>
					  <div class="form-group">
					    <label class="info-title" for="Billing City">Cidade de Envio <span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" id="shippingcity" name="shippingcity" required="required" value="<?php echo $row['shippingCity'];?>" >
					  </div>
 <div class="form-group">
					    <label class="info-title" for="Billing Pincode">CEP de envio <span>*</span></label>
					    <input type="text" class="form-control unicase-form-control text-input" id="shippingpincode" name="shippingpincode" required="required" value="<?php echo $row['shippingPincode'];?>" >
					  </div>


					  <button type="submit" name="shipupdate" class="btn-upper btn btn-primary checkout-page-button">Atualizar</button>
					<?php } ?>

		
						</div>
					
					</td>
				</tr>
		</tbody><!-- /tbody -->
	</table><!-- /table -->
</div>
<div class="col-md-4 col-sm-12 cart-shopping-total">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>
					
					<div class="cart-grand-total">
						Total Geral R$<span class="inner-left-md"><?php echo $_SESSION['tp']="$totalprice". ""; ?></span>
					</div>
				</th>
			</tr>
		</thead><!-- /thead -->
		<tbody>
				<tr>
					<td>
						<div class="cart-checkout-btn pull-right">
							<button type="submit" name="ordersubmit" class="btn btn-primary">FAZER O CHECKOUT</button>
							
						
						</div>
					</td>
				</tr>
		</tbody><!-- /tbody -->
	</table>
	<?php } else {
echo "Seu carrinho de compras está vazio";
		}?>
</div>			</div>
		</div> 
		</form>
<?php echo include('includes/brands-slider.php');?>
</div>
</div>
<?php include('includes/footer.php');?>

	<script src="assets/js/jquery-1.11.1.min.js"></script>
	
	<script src="assets/js/bootstrap.min.js"></script>
	
	<script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
	<script src="assets/js/owl.carousel.min.js"></script>
	
	<script src="assets/js/echo.min.js"></script>
	<script src="assets/js/jquery.easing-1.3.min.js"></script>
	<script src="assets/js/bootstrap-slider.min.js"></script>
    <script src="assets/js/jquery.rateit.min.js"></script>
    <script type="text/javascript" src="assets/js/lightbox.min.js"></script>
    <script src="assets/js/bootstrap-select.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
	<script src="assets/js/scripts.js"></script>

	<!-- For demo purposes – can be removed on production -->
	
	<script src="switchstylesheet/switchstylesheet.js"></script>
	
	<script>
		$(document).ready(function(){ 
			$(".changecolor").switchstylesheet( { seperator:"color"} );
			$('.show-theme-options').click(function(){
				$(this).parent().toggleClass('open');
				return false;
			});
		});

		$(window).bind("load", function() {
		   $('.show-theme-options').delay(2000).trigger('click');	
		});
	</script>
	<!-- For demo purposes – can be removed on production : End -->
	<?php if (isset($_SESSION['msg_success']) || isset($_SESSION['msg_error'])): ?>
<script>
    $(document).ready(function () {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "4000",
            "positionClass": "toast-top-right"
        };
        <?php if (isset($_SESSION['msg_success'])): ?>
            toastr.success("<?php echo addslashes($_SESSION['msg_success']); ?>");
            <?php unset($_SESSION['msg_success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['msg_error'])): ?>
            toastr.error("<?php echo addslashes($_SESSION['msg_error']); ?>");
            <?php unset($_SESSION['msg_error']); ?>
        <?php endif; ?>
    });
</script>
<?php endif; ?>

</body>
</html>