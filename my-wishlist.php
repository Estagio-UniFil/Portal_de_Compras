<?php
namespace Models;
session_start();
error_reporting(0);
include('includes/config.php');


if (strlen($_SESSION['login']) == 0) {   
    header('location:login.php');
    exit();
}

// Classe Orders para gerenciar pedidos e carrinho
class Orders {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    // Método para adicionar produto ao carrinho
    public function addToCart($productId) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity']++;
        } else {
            $sql = $this->con->prepare("SELECT id, productPrice FROM products WHERE id = ?");
            $sql->bind_param("i", $productId);
            $sql->execute();
            $result = $sql->get_result();

            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
                $_SESSION['cart'][$product['id']] = [
                    "quantity" => 1,
                    "price" => $product['productPrice']
                ];
                return true;
            } else {
                return "ID do produto é inválido";
            }
        }
        return true;
    }
}

// Classe Wishlist para gerenciar a lista de desejos
class Wishlist {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    // Método para remover um item da wishlist
    public function removeFromWishlist($wishlistId) {
        $query = $this->con->prepare("DELETE FROM wishlist WHERE id = ?");
        $query->bind_param("i", $wishlistId);
        return $query->execute();
    }

    // Método para mover um item da wishlist para o carrinho usando a classe Orders
    public function moveToCart($productId, $orders) {
        // Remove da wishlist
        $query = $this->con->prepare("DELETE FROM wishlist WHERE productId = ?");
        $query->bind_param("i", $productId);
        $query->execute();

        // Adiciona ao carrinho
        return $orders->addToCart($productId);
    }
}

// Criando instâncias das classes
$wishlist = new Wishlist($con);
$orders = new Orders($con);

// Remover item da wishlist
if (isset($_GET['del'])) {
    $wishlistId = intval($_GET['del']);
    $wishlist->removeFromWishlist($wishlistId);
}

// Mover item para o carrinho
if (isset($_GET['action']) && $_GET['action'] == "add") {
	$productId = intval($_GET['id']);
	$result = $wishlist->moveToCart($productId, $orders);
	if ($result === true) {
		$_SESSION['msg_success'] = "Produto adicionado ao carrinho com sucesso.";
		header("Location: my-cart.php");
		exit();
	} else {
		$_SESSION['msg_error'] = "O ID do produto é inválido.";
		header("Location: index.php");
		exit();
	}
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Meta -->
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">
	    <meta name="keywords" content="MediaCenter, Template, eCommerce">
	    <meta name="robots" content="all">

	    <title>Minha Lista de Desejos</title>
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
		<link rel="shortcut icon" href="assets/images/favicon.ico">
	</head>
    <body class="cnt-home">
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
<div class="breadcrumb">
	<div class="container">
		<div class="breadcrumb-inner">
			<ul class="list-inline list-unstyled">
				<li><a href="home.html">Home</a></li>
				<li class='active'>Lista de Desejos</li>
			</ul>
		</div><!-- /.breadcrumb-inner -->
	</div><!-- /.container -->
</div><!-- /.breadcrumb -->

<div class="body-content outer-top-bd">
	<div class="container">
		<div class="my-wishlist-page inner-bottom-sm">
			<div class="row">
				<div class="col-md-12 my-wishlist">
	<div class="table-responsive">
		<table class="table">
			<thead>
				<tr>
					<th colspan="4">Minha Lista de Desejos</th>
				</tr>
			</thead>
			<tbody>
<?php
$ret=mysqli_query($con,"select products.productName as pname,products.productName as proid,products.productImage1 as pimage,products.productPrice as pprice,wishlist.productId as pid,wishlist.id as wid from wishlist join products on products.id=wishlist.productId where wishlist.userId='".$_SESSION['id']."'");
$num=mysqli_num_rows($ret);
	if($num>0)
	{
while ($row=mysqli_fetch_array($ret)) {

?>

				<tr>
					<td class="col-md-2"><img src="admin/productimages/<?php echo htmlentities($row['pid']);?>/<?php echo htmlentities($row['pimage']);?>" alt="<?php echo htmlentities($row['pname']);?>" width="60" height="100"></td>
					<td class="col-md-6">
						<div class="product-name"><a href="product-details.php?pid=<?php echo htmlentities($pd=$row['pid']);?>"><?php echo htmlentities($row['pname']);?></a></div>
<?php $rt=mysqli_query($con,"select * from productreviews where productId='$pd'");
$num=mysqli_num_rows($rt);
{
?>

						<div class="rating">
							<i class="fa fa-star rate"></i>
							<i class="fa fa-star rate"></i>
							<i class="fa fa-star rate"></i>
							<i class="fa fa-star rate"></i>
							<i class="fa fa-star non-rate"></i>
							<span class="review">( <?php echo htmlentities($num);?> Reviews )</span>
						</div>
						<?php } ?>
						<div class="price">R$. 
							<?php echo htmlentities($row['pprice']);?>
							<span>R$900.00</span>
						</div>
					</td>
					<td class="col-md-2">
						<a href="my-wishlist.php?page=product&action=add&id=<?php echo $row['pid']; ?>" class="btn-upper btn btn-primary">Adicionar ao carrinho</a>
					</td>
					<td class="col-md-2 close-btn">
						<a href="my-wishlist.php?del=<?php echo htmlentities($row['wid']);?>" class="remove-wishlist-item"><i class="fa fa-times"></i></a>
					</td>
				</tr>
				<?php } } else{ ?>
				<tr>
					<td style="font-size: 18px; font-weight:bold ">Sua lista de desejos está vazia</td>

				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>			</div><!-- /.row -->
		</div><!-- /.sigin-in-->
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

	 <style>
    .custom-confirm-overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }

    .custom-confirm-box {
      background: #fff;
      padding: 25px 30px;
      max-width: 400px;
      width: 90%;
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      text-align: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .custom-confirm-box p {
      font-size: 18px;
      margin-bottom: 20px;
      color: #333;
    }

    .custom-confirm-box button {
      min-width: 110px;
      padding: 10px 15px;
      margin: 0 8px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .custom-confirm-box .btn-confirm {
      background-color: #d9534f;
      color: #fff;
    }

    .custom-confirm-box .btn-confirm:hover {
      background-color: #c9302c;
    }

    .custom-confirm-box .btn-cancel {
      background-color: #6c757d;
      color: #fff;
    }

    .custom-confirm-box .btn-cancel:hover {
      background-color: #5a6268;
    }
  </style>
	
</body>
</html>
<?php ?>