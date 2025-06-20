<?php 
session_start();
error_reporting(0);
include('includes/config.php');


if (isset($_GET['deleted'])) {
    if ($_GET['deleted'] == 1) {
        echo '
        <div id="success-message" style="margin: 15px auto; padding: 15px; max-width: 700px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; font-size: 1.1em; text-align:center;">
            <strong>Sucesso!</strong> Pedido deletado com sucesso.
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById("success-message");
                if(msg){
                    msg.style.transition = "opacity 0.5s ease";
                    msg.style.opacity = 0;
                    setTimeout(() => msg.remove(), 500);
                }
            }, 5000);
        </script>';
    } else {
        echo '
        <div id="error-message" style="margin: 15px auto; padding: 15px; max-width: 700px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; font-size: 1.1em; text-align:center;">
            <strong>Erro!</strong> Não foi possível deletar o pedido. Tente novamente.
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById("error-message");
                if(msg){
                    msg.style.transition = "opacity 0.5s ease";
                    msg.style.opacity = 0;
                    setTimeout(() => msg.remove(), 500);
                }
            }, 5000);
        </script>';
    }
}

if(strlen($_SESSION['login'])==0) {   
    header('location:login.php');
} else {
    if (isset($_GET['id'])) {
    $deleteQuery = mysqli_query($con,"DELETE FROM orders WHERE userId='".$_SESSION['id']."' AND paymentMethod IS NULL AND id='".$_GET['id']."'");
    if ($deleteQuery) {
        // Redireciona para a mesma página com parâmetro de sucesso
        header("Location: pending-orders.php?deleted=1");
        exit();
    } else {
        // Você pode adicionar um parâmetro para erro também se quiser
        header("Location: pending-orders.php?deleted=0");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Histórico de Pedidos Pendentes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/red.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.css">
    <link rel="stylesheet" href="assets/css/owl.transitions.css">
    <link rel="stylesheet" href="assets/css/lightbox.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/rateit.css">
    <link rel="stylesheet" href="assets/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
</head>
<body class="cnt-home">

<header class="header-style-1">
    <?php include('includes/top-header.php');?>
    <?php include('includes/main-header.php');?>
    <?php include('includes/menu-bar.php');?>
</header>

<div class="breadcrumb">
    <div class="container">
        <div class="breadcrumb-inner">
            <ul class="list-inline list-unstyled">
                <li><a href="#">Home</a></li>
                <li class='active'>Carrinho de Compras</li>
            </ul>
        </div>
    </div>
</div>

<div class="body-content outer-top-xs">
    <div class="container">
        <div class="row inner-bottom-sm">
            <div class="shopping-cart">
                <div class="col-md-12 col-sm-12 shopping-cart-table">
                    <div class="table-responsive">
                        <form name="cart" method="post">    
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Imagem</th>
                                        <th>Nome do Produto</th>
                                        <th>Quantidade</th>
                                        <th>Preço por Unidade</th>
                                        <th>Taxa de Frete</th>
                                        <th>Total Geral</th>
                                        <th>Data do Pedido</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                $query=mysqli_query($con,"SELECT products.productImage1 AS pimg1, products.productName AS pname, products.id AS c, orders.productId AS opid, orders.quantity AS qty, products.productPrice AS pprice, products.shippingCharge AS shippingcharge, orders.paymentMethod AS paym, orders.orderDate AS odate, orders.id AS oid FROM orders JOIN products ON orders.productId=products.id WHERE orders.userId='".$_SESSION['id']."' AND orders.paymentMethod IS NULL");
                                $cnt=1;
                                $num=mysqli_num_rows($query);
                                if($num>0) {
                                    while($row=mysqli_fetch_array($query)) {
                                ?>
                                    <tr>
                                        <td><?php echo $cnt;?></td>
                                        <td class="cart-image">
                                            <a class="entry-thumbnail" href="detail.html">
                                                <img src="admin/productimages/<?php echo $row['c'];?>/<?php echo $row['pimg1'];?>" alt="" width="84" height="146">
                                            </a>
                                        </td>
                                        <td><a href="product-details.php?pid=<?php echo $row['opid'];?>"><?php echo $row['pname'];?></a></td>
                                        <td><?php echo $qty = $row['qty']; ?></td>
                                        <td><?php echo $price = $row['pprice']; ?></td>
                                        <td><?php echo $shippcharge = $row['shippingcharge']; ?></td>
                                        <td><?php echo (($qty*$price)+$shippcharge); ?></td>
                                        <td><?php echo $row['odate']; ?></td>
                                       <td>
                                        <a href="#" class="btn-delete-order btn btn-danger btn-sm" data-id="<?php echo $row['oid']; ?>">
                                        Deletar
                                        </a>
                                        </td>

                                    </tr>
                                <?php 
                                    $cnt++;
                                    } 
                                ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="cart-checkout-btn pull-right">
                                               <a href="payment-method.php" class="btn btn-primary">PROSSEGUIR para o pagamento</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                } else { 
                                ?>
                                    <tr>
                                        <td colspan="9" align="center"><h4>Nenhum resultado encontrado</h4></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- BRANDS CAROUSEL -->
        <?php echo include('includes/brands-slider.php');?>

    </div>
</div>

<?php include('includes/footer.php');?>

<!-- JS Scripts -->
<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/echo.min.js"></script>
<script src="assets/js/jquery.easing-1.3.min.js"></script>
<script src="assets/js/bootstrap-slider.min.js"></script>
<script src="assets/js/jquery.rateit.min.js"></script>
<script src="assets/js/lightbox.min.js"></script>
<script src="assets/js/bootstrap-select.min.js"></script>
<script src="assets/js/wow.min.js"></script>
<script src="assets/js/scripts.js"></script>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmDeleteLabel">Confirmação de Exclusão</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Deseja realmente deletar este pedido?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <a href="#" id="btnConfirmDelete" class="btn btn-danger">Deletar</a>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    let deleteUrlBase = 'pending-orders.php?id=';

    $('.btn-delete-order').click(function(e) {
      e.preventDefault();
      let orderId = $(this).data('id');
      // Atualiza o link do botão de confirmação com o ID do pedido
      $('#btnConfirmDelete').attr('href', deleteUrlBase + orderId);
      // Abre o modal
      $('#confirmDeleteModal').modal('show');
    });
  });
</script>

</body>
</html>
<?php } ?>
