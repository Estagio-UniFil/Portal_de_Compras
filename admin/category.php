<?php
namespace Models;
session_start();
error_reporting(0);
include_once('includes/config.php');

// Classe Orders para gerenciamento do carrinho
class Orders {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function addToCart($productId) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity']++;
            $_SESSION['msg_success'] = "Quantidade do produto atualizada no carrinho.";
            header("Location: my-cart.php");
            exit();
        } else {
            $stmt = $this->con->prepare("SELECT id, productPrice FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
                $_SESSION['cart'][$product['id']] = [
                    "quantity" => 1,
                    "price" => $product['productPrice']
                ];
                $_SESSION['msg_success'] = "Produto adicionado ao carrinho com sucesso.";
                header("Location: my-cart.php");
                exit();
            } else {
                $_SESSION['msg_error'] = "O ID do produto é inválido.";
                header("Location: index.php");
                exit();
            }
        }
    }
}


// Classe Wishlist para gerenciamento da lista de desejos
class Wishlist {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function addToWishlist($userId, $productId) {
        $stmt = $this->con->prepare("INSERT INTO wishlist(userId, productId) VALUES(?, ?)");
        $stmt->bind_param("ii", $userId, $productId);
        if ($stmt->execute()) {
            $_SESSION['msg_success'] = "Produto adicionado à lista de desejos.";
            header("Location: my-wishlist.php");
            exit();
        } else {
            $_SESSION['msg_error'] = "Erro ao adicionar à lista de desejos.";
            header("Location: my-wishlist.php");
            exit();
        }
    }
}

$order = new Orders($con);
$wishlist = new Wishlist($con);

$cid = intval($_GET['cid']);

if (isset($_GET['action']) && $_GET['action'] == "add") {
    $productId = intval($_GET['id']);
    $order->addToCart($productId);
}

if (isset($_GET['pid']) && $_GET['action'] == "wishlist") {
    if (strlen($_SESSION['login']) == 0) {
        header('location:login.php');
        exit();
    } else {
        $userId = $_SESSION['id'];
        $productId = intval($_GET['pid']);
        $wishlist->addToWishlist($userId, $productId);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Meta -->

  
        <style>
.product-image {
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    padding: 10px;
}
.product-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
</style>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">
	    <meta name="keywords" content="MediaCenter, Template, eCommerce">
	    <meta name="robots" content="all">

	    <title>Categoria do Produto</title>

	    <!-- Bootstrap Core CSS -->
	    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
	    
	    <!-- Customizable CSS -->
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
	<?php if (isset($_SESSION['msg_success'])): ?>
<script>
  window.addEventListener('DOMContentLoaded', function() {
    toastr.success("<?php echo addslashes($_SESSION['msg_success']); ?>");
  });
</script>
<?php unset($_SESSION['msg_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['msg_error'])): ?>
<script>
  window.addEventListener('DOMContentLoaded', function() {
    toastr.error("<?php echo addslashes($_SESSION['msg_error']); ?>");
  });
</script>
<?php unset($_SESSION['msg_error']); ?>
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
<!-- ============================================== HEADER : END ============================================== -->
</div><!-- /.breadcrumb -->
<div class="body-content outer-top-xs">
	<div class='container'>
		<div class='row outer-bottom-sm'>
			<div class='col-md-3 sidebar'>
	            <!-- ================================== TOP NAVIGATION ================================== -->
<div class="side-menu animate-dropdown outer-bottom-xs">       
    <div class="head"><i class="icon fa fa-align-justify fa-fw"></i>Subcategorias</div>        
    <nav class="yamm megamenu-horizontal" role="navigation">
  
        <ul class="nav">
            <li class="dropdown menu-item">
              <?php $sql=mysqli_query($con,"select id,subcategory  from subcategory where categoryid='$cid'");

while($row=mysqli_fetch_array($sql))
{
    ?>
                <a href="sub-category.php?scid=<?php echo $row['id'];?>" class="dropdown-toggle"><i class="icon fa fa-desktop fa-fw"></i>
                <?php echo $row['subcategory'];?></a>
                <?php }?>
                        
</li>
</ul>
    </nav>
</div>
</div><!-- /.side-menu -->
<!-- ================================== TOP NAVIGATION : END ================================== -->	            <div class="sidebar-module-container">
	            	<h3 class="section-title">Comprar Por</h3>
	            	<div class="sidebar-filter">
		            	<!-- ============================================== SIDEBAR CATEGORY ============================================== -->
<div class="sidebar-widget wow fadeInUp outer-bottom-xs ">
	<div class="widget-header m-t-20">
		<h4 class="widget-title">Categoria</h4>
	</div>
	<div class="sidebar-widget-body m-t-10">
	         <?php $sql=mysqli_query($con,"select id,categoryName  from category");
while($row=mysqli_fetch_array($sql))
{
    ?>
		<div class="accordion">
	    	<div class="accordion-group">
	            <div class="accordion-heading">
	                <a href="category.php?cid=<?php echo $row['id'];?>"  class="accordion-toggle collapsed">
	                   <?php echo $row['categoryName'];?>
	                </a>
	            </div>  
	        </div>
	    </div>
	    <?php } ?>
	</div><!-- /.sidebar-widget-body -->
</div><!-- /.sidebar-widget -->



    
<!-- ============================================== COLOR: END ============================================== -->

	            	</div><!-- /.sidebar-filter -->
	            </div><!-- /.sidebar-module-container -->
            </div><!-- /.sidebar -->
			<div class='col-md-9'>
					<!-- ========================================== SECTION – HERO ========================================= -->

    <?php
$cid = intval($_GET['cid']); // sanitiza o ID da categoria
$categoryQuery = mysqli_query($con, "SELECT * FROM products WHERE category='$cid' LIMIT 1");
$productHeader = mysqli_fetch_array($categoryQuery);
?>
<div id="category" class="category-carousel hidden-xs">
    <div class="item">
        <div class="image text-center">
            <img 
                src="<?php echo $productHeader ? 'admin/productimages/' . htmlentities($productHeader['id']) . '/' . htmlentities($productHeader['productImage1']) : 'assets/images/noimage.png'; ?>" 
                alt="Imagem da categoria" 
                class="img-fluid rounded shadow-sm"
                style="max-width: 100%; height: auto; object-fit: contain; max-height: 300px;"
                onerror="this.onerror=null; this.src='assets/images/noimage.png';"
            />
        </div>
        <div class="container-fluid">
            <div class="caption vertical-top text-left">
                <div class="big-text"><br /></div>
                <?php
                $categoryNameQuery = mysqli_query($con, "SELECT categoryName FROM category WHERE id='$cid'");
                if ($rowCat = mysqli_fetch_array($categoryNameQuery)) {
                ?>
                    <div class="excerpt hidden-sm hidden-md">
                        <?php echo htmlentities($rowCat['categoryName']); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Lista de produtos -->
<div class="search-result-container">
    <div id="myTabContent" class="tab-content">
        <div class="tab-pane active" id="grid-container">
            <div class="category-product inner-top-vs">
                <div class="row g-4">
                    <?php
                    $productListQuery = mysqli_query($con, "SELECT * FROM products WHERE category='$cid'");
                    if (mysqli_num_rows($productListQuery) > 0) {
                        while ($product = mysqli_fetch_array($productListQuery)) {
                    ?>
                        <div class="col-sm-6 col-md-4 mb-4 wow fadeInUp">
                            <div class="products">
                                <div class="product">
                                    <div class="product-image">
                                        <div class="image text-center">
                                            <a href="product-details.php?pid=<?php echo htmlentities($product['id']); ?>">
                                                <img 
                                                    src="admin/productimages/<?php echo htmlentities($product['id']); ?>/<?php echo htmlentities($product['productImage1']); ?>" 
                                                    alt="Produto" 
                                                    class="img-fluid rounded shadow-sm"
                                                    style="max-height: 300px; width: auto;"
                                                    onerror="this.onerror=null; this.src='assets/images/noimage.png';"
                                                />
                                            </a>
                                        </div>
                                    </div>
                                    <div class="product-info text-center mt-2">
                                        <h5 class="product-title">
                                            <?php echo htmlentities($product['productName']); ?>
                                        </h5>
                                        <div class="product-price">
                                            R$ <?php echo number_format($product['productPrice'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                        }
                    } else {
                        echo "<div class='col-12 text-center'><p class='text-muted'>Nenhum produto encontrado nesta categoria.</p></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
        // Força quebra de linha a cada 3 colunas no desktop
        if ($counter % 3 == 0) {
            echo '<div class="w-100 d-none d-md-block"></div>';
        }
 else {
    echo "<div class='col-12 text-center'><h3>Nenhum produto encontrado</h3></div>";
}
?>
</div> <!-- /.row -->


	
		
	
		
	
		
										</div><!-- /.row -->
							</div><!-- /.category-product -->
						
						</div><!-- /.tab-pane -->
						
				

				</div><!-- /.search-result-container -->

			</div><!-- /.col -->
		</div></div>
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