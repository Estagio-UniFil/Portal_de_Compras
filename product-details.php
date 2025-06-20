<?php
namespace Models;
session_start();
error_reporting(0);
include('includes/config.php');

$orders = new Orders($con);
if (isset($_GET['action']) && $_GET['action'] === "add" && isset($_GET['id'])) {
   $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
	$status = $orders->addItem($_GET['id'], $quantity);



    if ($status === "adicionado" || $status === "incrementado") {
        $_SESSION['successmsg'] = "Produto adicionado ao carrinho com sucesso!";
        header("Location: product-details.php?pid=" . $_GET['id']);
        exit();
    } elseif ($status === "invalido") {
        $_SESSION['errormsg'] = "Produto inválido.";
        header("Location: index.php");
        exit();
    }
}


if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    echo "<h3 style='color:red; text-align:center;'>Produto inválido ou não especificado.</h3>";
    exit();
}

$pid = intval($_GET['pid']); // Força para número inteiro
$query = mysqli_query($con, "SELECT * FROM products WHERE id = '$pid'");

// Verifica se o produto existe
if (!$query || mysqli_num_rows($query) == 0) {
    echo "<h3 style='color:red; text-align:center;'>Produto não encontrado.</h3>";
    exit();
}

$product = mysqli_fetch_array($query);


class Orders {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function addItem($productId, $quantity = 1) {
    $id = intval($productId);
    $quantity = intval($quantity);
    if ($quantity < 1) {
        $quantity = 1; // Garantir que a quantidade mínima é 1
    }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $quantity;
        return "incrementado";
    } else {
        $query = $this->con->prepare("SELECT id, productPrice FROM products WHERE id = ?");
        $query->bind_param("i", $id);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['cart'][$row['id']] = [
                "quantity" => $quantity,
                "price" => $row['productPrice']
            ];
            return "adicionado";
        } else {
            return "invalido";
        }
    }
}
}

class Wishlist {
	private $con;

	public function __construct($db) {
		$this->con = $db;
	}

	public function addToWishlist($productId) {
		if (strlen($_SESSION['login']) == 0) {
			header('location:login.php');
			exit();
		}

		$pid = intval($productId);
		$userId = intval($_SESSION['id']);

		// Verifica se já está na wishlist
		$query = $this->con->prepare("SELECT id FROM wishlist WHERE userId = ? AND productId = ?");
		$query->bind_param("ii", $userId, $pid);
		$query->execute();
		$result = $query->get_result();

		if ($result->num_rows > 0) {
			// Já está na wishlist, mostra mensagem de erro em vermelho
			$_SESSION['errormsg'] = "<span style='color: #a94442;'>Produto já está na sua lista de desejos!</span>";
			header('location:product-details.php?pid=' . $pid);
			exit();
		}

		// Não está na wishlist, adiciona normalmente
		$query = $this->con->prepare("INSERT INTO wishlist (userId, productId) VALUES (?, ?)");
		$query->bind_param("ii", $userId, $pid);

		if ($query->execute()) {
			$_SESSION['successmsg'] = "Produto adicionado à sua lista de desejos com sucesso!";
			header('location:product-details.php?pid=' . $pid);
			exit();
		}
	}
}

class Reviews {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function addReview($productId, $data) {
        $query = $this->con->prepare("
            INSERT INTO productreviews (productId, quality, price, value, name, summary, review) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $query->bind_param(
            "iiissss",
            $productId, 
            $data['quality'], 
            $data['price'], 
            $data['value'], 
            $data['name'], 
            $data['summary'], 
            $data['review']
        );

        if ($query->execute()) {
            return "Avaliação adicionada com sucesso!";
        } else {
            return "Erro ao adicionar a avaliação.";
        }
    }
}

// Criando instâncias das classes
$wishlist = new Wishlist($con);
$reviews = new Reviews($con);

// Adicionar ao carrinho
if (isset($_GET['action']) && $_GET['action'] == "add" && isset($_GET['id'])) {
    $orders->addItem($_GET['id']);
}

// Adicionar à wishlist
if (isset($_GET['pid']) && $_GET['action'] == "wishlist") {
    $wishlist->addToWishlist($_GET['pid']);
}

// Adicionar avaliação
if (isset($_POST['submit'])) {
    $reviewData = [
        'quality' => $_POST['quality'],
        'price' => $_POST['price'],
        'value' => $_POST['value'],
        'name' => $_POST['name'],
        'summary' => $_POST['summary'],
        'review' => $_POST['review']
    ];
    $message = $reviews->addReview($_GET['pid'], $reviewData);
    echo "<script>alert('$message');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<style>
.modal-custom {
  display: block;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.4);
  animation: fadein 0.4s;
}

.modal-content-custom {
  background-color: #dff0d8;
  margin: 15% auto;
  padding: 20px;
  border: 1px solid #3c763d;
  width: 80%;
  max-width: 400px;
  color: #3c763d;
  border-radius: 5px;
  position: relative;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
}

.close-custom {
  color: #3c763d;
  position: absolute;
  top: 8px;
  right: 12px;
  font-size: 22px;
  font-weight: bold;
  cursor: pointer;
}

.stock-box {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.stock-box .label {
    min-width: 100px; /* Mesma largura para todas as labels */
    font-weight: bold;
    margin-right: 0px;
}

.stock-box .value {
    display: inline-block;
}

</style>



	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">
	    <meta name="keywords" content="MediaCenter, Template, eCommerce">
	    <meta name="robots" content="all">
	    <title>Detalhes do Produto</title>
	    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
	    <link rel="stylesheet" href="assets/css/main.css">
	    <link rel="stylesheet" href="assets/css/red.css">
	    <link rel="stylesheet" href="assets/css/owl.carousel.css">
		<link rel="stylesheet" href="assets/css/owl.transitions.css">
		<link href="assets/css/lightbox.css" rel="stylesheet">
		<link rel="stylesheet" href="assets/css/animate.min.css">
		<link rel="stylesheet" href="assets/css/rateit.css">
		<link rel="stylesheet" href="assets/css/bootstrap-select.min.css">
		<link rel="stylesheet" href="assets/css/config.css">

		<link href="assets/css/green.css" rel="alternate stylesheet" title="Green color">
		<link href="assets/css/blue.css" rel="alternate stylesheet" title="Blue color">
		<link href="assets/css/red.css" rel="alternate stylesheet" title="Red color">
		<link href="assets/css/orange.css" rel="alternate stylesheet" title="Orange color">
		<link href="assets/css/dark-green.css" rel="alternate stylesheet" title="Darkgreen color">
		<link rel="stylesheet" href="assets/css/font-awesome.min.css">

        <!-- Fonts --> 
		<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet' type='text/css'>
		<link rel="shortcut icon" href="assets/images/favicon.ico">
	</head>
    <body class="cnt-home">
<?php if (isset($_SESSION['successmsg'])): ?>
<!-- Modal de Sucesso -->

<div id="customModal" class="modal-custom">
  <div class="modal-content-custom">
	<span class="close-custom" onclick="document.getElementById('customModal').style.display='none'">&times;</span>
	<p><?php echo $_SESSION['successmsg']; unset($_SESSION['successmsg']); ?></p>
  </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['errormsg'])): ?>
<!-- Modal de Erro -->
<style>
.modal-content-error {
  background-color: #f2dede;
  border: 1px solid #a94442;
  color: #a94442;
}
.close-error {
  color: #a94442;
  position: absolute;
  top: 8px;
  right: 12px;
  font-size: 22px;
  font-weight: bold;
  cursor: pointer;
}
</style>
<div id="errorModal" class="modal-custom">
  <div class="modal-content-custom modal-content-error">
	<span class="close-error" onclick="document.getElementById('errorModal').style.display='none'">&times;</span>
	<p><?php echo $_SESSION['errormsg']; unset($_SESSION['errormsg']); ?></p>
  </div>
</div>
<?php endif; ?>


	
<header class="header-style-1">

	<!-- ============================================== TOP MENU ============================================== -->
<?php include('includes/top-header.php');?>
<!-- ============================================== TOP MENU : END ============================================== -->
<?php include('includes/main-header.php');?>
	<!-- ============================================== NAVBAR ============================================== -->
<?php include('includes/menu-bar.php');?>
<!-- ============================================== NAVBAR : END ============================================== -->

</header>
<style>
.breadcrumb-inner {
    white-space: nowrap; /* Impede a quebra de linha */
}

.breadcrumb-inner a {
    display: inline-block; /* Mantém os links alinhados corretamente */
}
</style>
<!-- ============================================== HEADER : END ============================================== -->
<div class="breadcrumb">
	<div class="container">
		<div class="breadcrumb-inner">
<?php
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$ret=mysqli_query($con,"select category.categoryName as catname,subcategory.subcategory as subcatname,products.productName as pname from products join category on category.id=products.category join subcategory on subcategory.id=products.subcategory where products.id='$pid'");
while ($rw=mysqli_fetch_array($ret)) {

?>


			<ul class="list-inline list-unstyled">
				<li><a href="index.php">Home</a></li>
				<li><?php echo htmlentities($rw['catname']);?></a></li>
				<li><?php echo htmlentities($rw['subcatname']);?></li>
				<li class='active'><?php echo htmlentities($rw['pname']);?></li>
			</ul>
			<?php }?>
		</div><!-- /.breadcrumb-inner -->
	</div><!-- /.container -->
</div><!-- /.breadcrumb -->
<div class="body-content outer-top-xs">
	<div class='container'>
		<div class='row single-product outer-bottom-sm '>
			<div class='col-md-3 sidebar'>
				<div class="sidebar-module-container">


					<!-- ==============================================CATEGORY============================================== -->
<div class="sidebar-widget outer-bottom-xs wow fadeInUp">
	<h3 class="section-title">Categoria</h3>
	<div class="sidebar-widget-body m-t-10">
		<div class="accordion">

		            <?php $sql=mysqli_query($con,"select id,categoryName  from category");
while($row=mysqli_fetch_array($sql))
{
    ?>
	    	<div class="accordion-group">
	            <div class="accordion-heading">
	                <a href="category.php?cid=<?php echo $row['id'];?>"  class="accordion-toggle collapsed">
	                   <?php echo $row['categoryName'];?>
	                </a>
	            </div>
	          
	        </div>
	        <?php } ?>
	    </div>
	</div>
</div>
	<!-- ============================================== CATEGORY : END ============================================== -->					<!-- ============================================== HOT DEALS ============================================== -->
<div class="sidebar-widget hot-deals wow fadeInUp">
	<h3 class="section-title">Ofertas</h3>
	<div class="owl-carousel sidebar-carousel custom-carousel owl-theme outer-top-xs">
		
								   <?php
$ret=mysqli_query($con,"select * from products order by rand() limit 4 ");
while ($rws=mysqli_fetch_array($ret)) {

?>

								        
													<div class="item">
					<div class="products">
						<div class="hot-deal-wrapper">
							<div class="image">
								<img src="admin/productimages/<?php echo htmlentities($rws['id']);?>/<?php echo htmlentities($rws['productImage1']);?>"  width="150"  alt="">
							</div>
							
						</div><!-- /.hot-deal-wrapper -->

						<div class="product-info text-left m-t-20">
							<h3 class="name"><a href="product-details.php?pid=<?php echo htmlentities($rws['id']);?>"><?php echo htmlentities($rws['productName']);?></a></h3>
							<div class="rating rateit-small"></div>

							<div class="product-price">	
								<span class="price">
								<div class="product-price">	
    <span class="price">
        R$ <?php echo number_format($rws['productPrice'], 2, ',', '.'); ?>
    </span>
    
    <span class="price-before-discount" style="text-decoration: line-through; color: red;">
        R$ <?php echo number_format($rws['productPriceBeforeDiscount'], 2, ',', '.'); ?>
    </span>					
</div><!-- /.product-price -->						
							
							</div><!-- /.product-price -->
							
						</div><!-- /.product-info -->

						<div class="cart clearfix animate-effect">
							<div class="action">
								
								<div class="add-cart-button btn-group">
								<?php if(isset($rws['productAvailability']) && $rws['productAvailability'] == 'In Stock') { ?>
									<a href="category.php?page=product&action=add&id=<?php echo $rws['id']; ?>">
									<button class="btn btn-primary">
								<i class="fa fa-shopping-cart"></i>													
							</button>
							</a>
							<?php } else { ?>
								<div class="action" style="color:red">Fora de Estoque</div>
							<?php } ?>
															
								</div>
								
							</div><!-- /.action -->
						</div><!-- /.cart -->
					</div>	
					</div>		
					<?php } ?>        
						
	    
    </div><!-- /.sidebar-widget -->
</div>

<!-- ============================================== COLOR: END ============================================== -->
				</div>
			</div><!-- /.sidebar -->
<?php 
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$ret=mysqli_query($con,"select * from products where id='$pid'");
while($row=mysqli_fetch_array($ret))
{

?>


			<div class='col-md-9'>
				<div class="row  wow fadeInUp">
					     <div class="col-xs-12 col-sm-6 col-md-5 gallery-holder">
    <div class="product-item-holder size-big single-product-gallery small-gallery">

        <div id="owl-single-product">

 <div class="single-product-gallery-item" id="slide1">
                <a data-lightbox="image-1" data-title="<?php echo htmlentities($row['productName']);?>" href="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage1']);?>">
                    <img class="img-responsive" alt="" src="assets/images/blank.gif" data-echo="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage1']);?>" width="370" height="350" />
                </a>
            </div>




            <div class="single-product-gallery-item" id="slide1">
                <a data-lightbox="image-1" data-title="<?php echo htmlentities($row['productName']);?>" href="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage1']);?>">
                    <img class="img-responsive" alt="" src="assets/images/blank.gif" data-echo="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage1']);?>" width="370" height="350" />
                </a>
            </div><!-- /.single-product-gallery-item -->

            <div class="single-product-gallery-item" id="slide2">
                <a data-lightbox="image-1" data-title="Gallery" href="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage2']);?>">
                    <img class="img-responsive" alt="" src="assets/images/blank.gif" data-echo="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage2']);?>" />
                </a>
            </div><!-- /.single-product-gallery-item -->

            <div class="single-product-gallery-item" id="slide3">
                <a data-lightbox="image-1" data-title="Gallery" href="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage3']);?>">
                    <img class="img-responsive" alt="" src="assets/images/blank.gif" data-echo="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage3']);?>" />
                </a>
            </div>

        </div><!-- /.single-product-slider -->


        <div class="single-product-gallery-thumbs gallery-thumbs">

            <div id="owl-single-product-thumbnails">
                <div class="item">
                    <a class="horizontal-thumb active" data-target="#owl-single-product" data-slide="1" href="#slide1">
                        <img class="img-responsive"  alt="" src="assets/images/blank.gif" data-echo="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage1']);?>" />
                    </a>
                </div>

            <div class="item">
                    <a class="horizontal-thumb" data-target="#owl-single-product" data-slide="2" href="#slide2">
                        <img class="img-responsive" width="85" alt="" src="assets/images/blank.gif" data-echo="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage2']);?>"/>
                    </a>
                </div>
                <div class="item">

                    <a class="horizontal-thumb" data-target="#owl-single-product" data-slide="3" href="#slide3">
                        <img class="img-responsive" width="85" alt="" src="assets/images/blank.gif" data-echo="admin/productimages/<?php echo htmlentities($row['id']);?>/<?php echo htmlentities($row['productImage3']);?>" height="200" />
                    </a>
                </div>

               
               
                
            </div><!-- /#owl-single-product-thumbnails -->

            

        </div>

    </div>
</div>     			




					<div class='col-sm-6 col-md-7 product-info-block'>
						<div class="product-info">
							<h1 class="name"><?php echo htmlentities($row['productName']);?></h1>
<?php $rt=mysqli_query($con,"select * from productreviews where productId='$pid'");
$num=mysqli_num_rows($rt);
{
?>		
							<div class="rating-reviews m-t-20">
								<div class="row">
									<div class="col-sm-3">
										<div class="rating rateit-small"></div>
									</div>
									<div class="col-sm-8">
										<div class="reviews">
											<a href="#" class="lnk">(<?php echo htmlentities($num);?> Reviews)</a>
										</div>
									</div>
								</div><!-- /.row -->		
							</div><!-- /.rating-reviews -->
<?php } ?>
<div class="stock-container info-container m-t-10">
    <div class="row">
        <div class="col-sm-12">
            <div class="stock-box">
                <span class="label">Disponibilidade :</span>
                <span class="value">
                    <?php 
                    if ($row['productAvailability'] == "In Stock") {
                        echo "Em Estoque";
                    } elseif ($row['productAvailability'] == "Out of Stock") {
                        echo "Fora de Estoque";
                    } else {
                        echo htmlentities($row['productAvailability']);
                    }
                    ?>
                </span>
            </div>  
        </div>
    </div>
</div>


<style>
  /* Espaço maior entre label e valor */
  .stock-container .value {
    margin-left: 20px; /* Ajuste conforme quiser */
    display: inline-block;
  }
</style>




<div class="stock-container info-container m-t-9">
    <div class="row">
        <div class="col-sm-12">
            <div class="stock-box">
                <span class="label">Marca do Produto :</span>
                <span class="value"><?php echo htmlentities($row['productCompany']);?></span>
            </div>  
        </div>
    </div>
</div>





	<div class="stock-container info-container m-t-10">
    <div class="row">
        <div class="col-sm-12">
            <div class="stock-box">
                <span class="label">Taxa de Frete :</span>
                <span class="value">
                    <?php 
                    if ($row['shippingCharge'] == 0) {
                        echo "Grátis";
                    } else {
                        echo "R$ " . number_format($row['shippingCharge'], 2, ',', '.'); 
                    }
                    ?>
                </span>
            </div>  
        </div>
    </div><!-- /.row -->   
</div>


							<div class="price-container info-container m-t-20">
								<div class="row">
									

									<div class="col-sm-6">
										<div class="price-box">
											<span class="price">
                    R$ <?php echo htmlentities(number_format($row['productPrice'], 2, ',', '.')); ?>
                </span>
                <span class="price-strike">
                    R$ <?php echo htmlentities(number_format($row['productPriceBeforeDiscount'], 2, ',', '.')); ?>
                </span>
										</div>
									</div>




									<div class="col-sm-6">
										<div class="favorite-button m-t-10">
											<a class="btn btn-primary" data-toggle="tooltip" data-placement="right" title="Wishlist" href="product-details.php?pid=<?php echo htmlentities($row['id'])?>&&action=wishlist">
											    <i class="fa fa-heart"></i>
											</a>
											
											</a>
										</div>
									</div>

								</div><!-- /.row -->
							</div><!-- /.price-container -->

	




							<div class="quantity-container info-container">
    <div class="row">
        <div class="col-sm-2">
            <span class="label">QTD :</span>
        </div>
        <div class="col-sm-2">
            <div class="cart-quantity">
                <div class="quant-input" style="position: relative; display: inline-block;">
                    <input type="text" value="1" id="quantity-input" style="width: 50px; text-align: center;">

                    <div class="arrows" style="position: absolute; right: -20px; top: 0; height: 100%; display: flex; flex-direction: column; justify-content: center;">
                        <div class="arrow plus gradient" style="cursor: pointer; user-select: none;">
                            <span class="ir"><i class="icon fa fa-sort-asc"></i></span>
                        </div>
                        <div class="arrow minus gradient" style="cursor: pointer; user-select: none;">
                            <span class="ir"><i class="icon fa fa-sort-desc"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-7">
    <?php if($row['productAvailability'] == 'In Stock') { ?>
        <a href="#" id="add-to-cart-btn" data-id="<?php echo $row['id']; ?>" class="btn btn-primary" style="margin-left: 10px;">
            <i class="fa fa-shopping-cart inner-right-vs"></i> Adicionar ao Carrinho
                </a>
            <?php } else { ?>
                <div class="action" style="color:red">Fora de Estoque</div>
            <?php } ?>
        </div>
    </div><!-- /.row -->
</div><!-- /.quantity-container -->

<script>
    // Seleção dos elementos
    const plusBtn = document.querySelector('.arrow.plus');
    const minusBtn = document.querySelector('.arrow.minus');
    const quantityInput = document.getElementById('quantity-input');
    const addToCartBtn = document.getElementById('add-to-cart-btn');

    // Incrementar quantidade
    plusBtn.addEventListener('click', () => {
        let val = parseInt(quantityInput.value) || 1;
        quantityInput.value = val + 1;
    });

    // Decrementar quantidade
    minusBtn.addEventListener('click', () => {
        let val = parseInt(quantityInput.value) || 1;
        if (val > 1) quantityInput.value = val - 1;
    });

    // Ao clicar em "Adicionar ao Carrinho", redireciona com a quantidade correta
    addToCartBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let quantity = parseInt(quantityInput.value);
        if (isNaN(quantity) || quantity < 1) quantity = 1;

        let productId = this.getAttribute('data-id');
        let url = `product-details.php?page=product&action=add&id=${productId}&quantity=${quantity}`;

        window.location.href = url;
    });
</script>



									
								</div><!-- /.row -->
							</div><!-- /.quantity-container -->

					<!-- 		<div class="product-social-link m-t-20 text-right">
								<span class="social-label">Share :</span>
								<div class="social-icons">
						            <ul class="list-inline">
						                <li><a class="fa fa-facebook" href="http://facebook.com/transvelo"></a></li>
						                <li><a class="fa fa-twitter" href="#"></a></li>
						                <li><a class="fa fa-linkedin" href="#"></a></li>
						                <li><a class="fa fa-rss" href="#"></a></li>
						                <li><a class="fa fa-pinterest" href="#"></a></li>
						            </ul>
						        </div>
							</div>
 -->
							

							
						</div><!-- /.product-info -->
					</div><!-- /.col-sm-7 -->
				</div><!-- /.row -->

				
				<div class="product-tabs inner-bottom-xs  wow fadeInUp">
					<div class="row">
						<div class="col-sm-3">
							<ul id="product-tabs" class="nav nav-tabs nav-tab-cell">
								<li class="active"><a data-toggle="tab" href="#description">Descrição</a></li>
								<li><a data-toggle="tab" href="#review">Avaliações</a></li>
							</ul><!-- /.nav-tabs #product-tabs -->
						</div>
						<div class="col-sm-9">

							<div class="tab-content">
								
								<div id="description" class="tab-pane in active">
									<div class="product-tab">
										<p class="text"><?php echo $row['productDescription'];?></p>
									</div>	
								</div><!-- /.tab-pane -->

								<div id="review" class="tab-pane">
									<div class="product-tab">
																				
										<div class="product-reviews">
											<h4 class="title">Avaliações de Clientes</h4>
<?php $qry=mysqli_query($con,"select * from productreviews where productId='$pid'");
while($rvw=mysqli_fetch_array($qry))
{
?>

											<div class="reviews" style="border: solid 1px #000; padding-left: 2% ">
												<div class="review">
													<div class="review-title"><span class="summary"><?php echo htmlentities($rvw['summary']);?></span><span class="date"><i class="fa fa-calendar"></i><span><?php echo htmlentities($rvw['reviewDate']);?></span></span></div>

													<div class="text">"<?php echo htmlentities($rvw['review']);?>"</div>
													<div class="text"><b>Qualidade :</b>  <?php echo htmlentities($rvw['quality']);?> Estrela</div>
													<div class="text"><b>Preço :</b>  <?php echo htmlentities($rvw['price']);?> Estrela</div>
													<div class="text"><b>Valor :</b>  <?php echo htmlentities($rvw['value']);?> Estrela</div>
                                                <div class="author m-t-15"><i class="fa fa-pencil-square-o"></i> <span class="name"><?php echo htmlentities($rvw['name']);?></span></div>													</div>
											
											</div>
											<?php } ?><!-- /.reviews -->
										</div><!-- /.product-reviews -->
										<form role="form" class="cnt-form" name="review" method="post">

										
										<div class="product-add-review">
											<h4 class="title">Escreva sua própria Avaliação</h4>
											<div class="review-table">
												<div class="table-responsive">
													<table class="table table-bordered">	
														<thead>
															<tr>
																<th class="cell-label">&nbsp;</th>
																<th>1 Estrela</th>
																<th>2 Estrelas</th>
																<th>3 Estrelas</th>
																<th>4 Estrelas</th>
																<th>5 Estrelas</th>
															</tr>
														</thead>	
														<tbody>
															<tr>
																<td class="cell-label">Qualidade</td>
																<td><input type="radio" name="quality" class="radio" value="1"></td>
																<td><input type="radio" name="quality" class="radio" value="2"></td>
																<td><input type="radio" name="quality" class="radio" value="3"></td>
																<td><input type="radio" name="quality" class="radio" value="4"></td>
																<td><input type="radio" name="quality" class="radio" value="5"></td>
															</tr>
															<tr>
																<td class="cell-label">Preço</td>
																<td><input type="radio" name="price" class="radio" value="1"></td>
																<td><input type="radio" name="price" class="radio" value="2"></td>
																<td><input type="radio" name="price" class="radio" value="3"></td>
																<td><input type="radio" name="price" class="radio" value="4"></td>
																<td><input type="radio" name="price" class="radio" value="5"></td>
															</tr>
															<tr>
																<td class="cell-label">Valor</td>
																<td><input type="radio" name="value" class="radio" value="1"></td>
																<td><input type="radio" name="value" class="radio" value="2"></td>
																<td><input type="radio" name="value" class="radio" value="3"></td>
																<td><input type="radio" name="value" class="radio" value="4"></td>
																<td><input type="radio" name="value" class="radio" value="5"></td>
															</tr>
														</tbody>
													</table><!-- /.table .table-bordered -->
												</div><!-- /.table-responsive -->
											</div><!-- /.review-table -->
											
											<div class="review-form">
												<div class="form-container">
													
														
														<div class="row">
															<div class="col-sm-6">
																<div class="form-group">
																	<label for="exampleInputName">Seu Nome <span class="astk">*</span></label>
																<input type="text" class="form-control txt" id="exampleInputName" placeholder="" name="name" required="required">
																</div><!-- /.form-group -->
																<div class="form-group">
																	<label for="exampleInputSummary">Sumário <span class="astk">*</span></label>
																	<input type="text" class="form-control txt" id="exampleInputSummary" placeholder="" name="summary" required="required">
																</div><!-- /.form-group -->
															</div>

															<div class="col-md-6">
																<div class="form-group">
																	<label for="exampleInputReview">Avaliação <span class="astk">*</span></label>

<textarea class="form-control txt txt-review" id="exampleInputReview" rows="4" placeholder="" name="review" required="required"></textarea>
																</div><!-- /.form-group -->
															</div>
														</div><!-- /.row -->
														
														<div class="action text-right">
															<button name="submit" class="btn btn-primary btn-upper">Enviar Avliação</button>
														</div><!-- /.action -->

													</form><!-- /.cnt-form -->
												</div><!-- /.form-container -->
											</div><!-- /.review-form -->

										</div><!-- /.product-add-review -->										
										
							        </div><!-- /.product-tab -->
								</div><!-- /.tab-pane -->

				

							</div><!-- /.tab-content -->
						</div><!-- /.col -->
					</div><!-- /.row -->
				</div><!-- /.product-tabs -->

<?php $cid=$row['category'];
			$subcid=$row['subCategory']; } ?>
				<!-- ============================================== UPSELL PRODUCTS ============================================== -->
<section class="section featured-product wow fadeInUp">
</style>
	<h3 class="section-title">Produtos Relacionados</h3>
	<div class="owl-carousel home-owl-carousel upsell-product custom-carousel owl-theme outer-top-xs">
		
		
	   
	<?php
        // Consulta que busca produtos com categoria válida
        $ret = mysqli_query($con, "
            SELECT p.* 
            FROM products p
            JOIN category c ON c.id = p.category
            WHERE c.categoryName IS NOT NULL
            ORDER BY RAND() 
            LIMIT 4
        ");

        while ($rws = mysqli_fetch_array($ret)) {
        ?>
			
			

			<div class="item item-carousel">
    <div class="products">
        <div class="product">
            <div class="product-image">
                <div class="image">
                    <a href="product-details.php?pid=<?php echo htmlentities($rws['id']); ?>">
                        <img src="admin/productimages/<?php echo htmlentities($rws['id']); ?>/<?php echo htmlentities($rws['productImage1']); ?>" alt="">
                    </a>
                </div>
            </div>
            <div class="product-info text-left">
                <h3 class="name"><a href="product-details.php?pid=<?php echo htmlentities($rws['id']); ?>"><?php echo htmlentities($rws['productName']); ?></a></h3>
                <div class="product-price">
                    <span class="price">
                        R$ <?php echo number_format($rws['productPrice'], 2, ',', '.'); ?>

                    </span>
                </div>
            </div>
        </div>
    </div>
	<style>
.owl-carousel .product-image .image img {
    max-width: 100%;
    height: auto;
    max-height: 180px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}
</style>
</div>
		<?php } ?>
	
		
			</div><!-- /.home-owl-carousel -->
</section><!-- /.section -->


<!-- ============================================== UPSELL PRODUCTS : END ============================================== -->
			
			</div><!-- /.col -->
			<div class="clearfix"></div>
		</div>
<?php include('includes/brands-slider.php');?>
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

	

</body>
</html>