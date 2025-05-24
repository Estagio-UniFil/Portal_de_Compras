<?php 
namespace Models;
session_start();
include_once('includes/config.php');

if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

class Orders {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function removeItem($itemId) {
        $query = $this->con->prepare("DELETE FROM cart WHERE id = ?");
        $query->bind_param("i", $itemId);
        if ($query->execute()) {
            echo "<script>alert('Produto excluído do carrinho.');</script>";
            echo "<script type='text/javascript'> document.location ='checkout.php'; </script>";
        }
    }
}

class Users {
    private $con;
    private $userId;

    public function __construct($db, $userId) {
        $this->con = $db;
        $this->userId = $userId;
    }

    public function addAddress($billing, $shipping) {
        $query = $this->con->prepare("
            INSERT INTO addresses (userId, billingAddress, biilingCity, billingState, billingPincode, billingCountry, 
                                   shippingAddress, shippingCity, shippingState, shippingPincode, shippingCountry) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $query->bind_param("issssssssss", 
            $this->userId, 
            $billing['address'], $billing['city'], $billing['state'], $billing['pincode'], $billing['country'],
            $shipping['address'], $shipping['city'], $shipping['state'], $shipping['pincode'], $shipping['country']
        );

        if ($query->execute()) {
            echo "<script>alert('Seu endereço foi adicionado com sucesso.');</script>";
            echo "<script type='text/javascript'> document.location ='checkout.php'; </script>";
        } else {
            echo "<script>alert('Algo deu errado. Por favor, tente novamente.');</script>";
            echo "<script type='text/javascript'> document.location ='checkout.php'; </script>";
        }
    }
}

// Instanciando classes
$cart = new Orders($con);
$address = new Users($con, $_SESSION['id']);

// Remover produto do carrinho
if (isset($_GET['del'])) {
    $cart->removeItem(intval($_GET['del']));
}

// Inserir endereço
if (isset($_POST['submit'])) {
    $billing = [
        'address' => $_POST['baddress'],
        'city' => $_POST['bcity'],
        'state' => $_POST['bstate'],
        'pincode' => $_POST['bpincode'],
        'country' => $_POST['bcountry']
    ];

    $shipping = [
        'address' => $_POST['saddress'],
        'city' => $_POST['scity'],
        'state' => $_POST['sstate'],
        'pincode' => $_POST['spincode'],
        'country' => $_POST['scountry']
    ];

    $address->addAddress($billing, $shipping);
}

// Processar pagamento
if (isset($_POST['proceedpayment'])) {
    $_SESSION['address'] = $_POST['selectedaddress'];
    $_SESSION['gtotal'] = $_POST['grandtotal'];
    echo "<script type='text/javascript'> document.location ='payment.php'; </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Portal de Compras | Checkout</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
        <script src="js/jquery.min.js"></script>
       <!--  <link href="css/bootstrap.min.css" rel="stylesheet" /> -->
    </head>
<style type="text/css"></style>
    <body>
<?php include_once('includes/header.php');?>
        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">


                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder">Checkout</h1>
                </div>

            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4  mt-5">
     


        <table class="table">
            <thead>
                <tr>
                    <th colspan="4"><h4>Meu Carrinho</h4></th>
                </tr>
            </thead>
            <tr>
                <thead>
                    <th>Produto</th>
                    <th>Nome do produto</th>
                    <th>Preço do produto</th>
                    <th>Quantidade</th>
                    <th>Montante total</th>
                    <th>Ação</th>
                </thead>
            </tr>
            <tbody>
<?php
$uid=$_SESSION['id'];
$ret=mysqli_query($con,"select products.productName as pname,products.productName as proid,products.productImage1 as pimage,products.productPrice as pprice,cart.productId as pid,cart.id as cartid,products.productPriceBeforeDiscount,cart.productQty from cart join products on products.id=cart.productId where cart.userId='$uid'");
$num=mysqli_num_rows($ret);
    if($num>0)
    {
while ($row=mysqli_fetch_array($ret)) {

?>

                <tr>
                    <td class="col-md-2"><img src="admin/productimages/<?php echo htmlentities($row['pimage']);?>" alt="<?php echo htmlentities($row['pname']);?>" width="100" height="100"></td>
                    <td>
                       <a href="product-details.php?pid=<?php echo htmlentities($pd=$row['pid']);?>"><?php echo htmlentities($row['pname']);?></a>
        </td>
<td>
                           <span class="text-decoration-line-through">$<?php echo htmlentities($row['productPriceBeforeDiscount']);?></span>
                            <span>$<?php echo htmlentities($row['pprice']);?></span>
                    </td>
                    <td><?php echo htmlentities($row['productQty']);?></td>
                     <td><?php echo htmlentities($totalamount=$row['productQty']*$row['pprice']);?></td>
                    <td>
                        <a href="my-cart.php?del=<?php echo htmlentities($row['cartid']);?>" onClick="return confirm('Are you sure you want to delete?')" class="btn-upper btn btn-danger">Delete</a>
                    </td>
                </tr>
                <?php $grantotal+=$totalamount;
            } ?>
<tr>
    <th colspan="4">Total geral</th>
    <th colspan="2"><?php echo $grantotal;?></th>
</tr>
            <?php } else{  
    echo "<script type='text/javascript'> document.location ='my-cart.php'; </script>"; } ?>   
            </tbody>
        </table>
<h5>Endereços já listados</h5>
<?php 
$uid=$_SESSION['id'];
$query=mysqli_query($con,"select * from addresses where userId='$uid'");
$count=mysqli_num_rows($query);
if($count==0):
echo "<font color='red'>No addresses Found.</font>";
else:
 ?>
 <form method="post">
    <input type="hidden" name="grandtotal" value="<?php echo $grantotal; ?>">
<div class="row">
<div class="col-6">
      <table class="table">
            <thead>
                <tr>
                    <th colspan="4"><h5>Endereço de Cobrança</h5></th>
                </tr>
            </thead>
            <tr>
                <thead>
                    <th>#</th>
                    <th width="250">Endereço</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>CEP</th>
                    <th>País</th>
            
                </thead>
            </tr>
            </table>  

</div>
<div class="col-6">
          <table class="table">
            <thead>
                <tr>
                    <th colspan="4"><h5>Endereço para Envio</h5></th>
                </tr>
            </thead>
            <tr>
                <thead>
                    <th width="250">Endereço</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>CEP</th>
                    <th>País</th>
            
                </thead>
            </tr>
            </tbody>
            </table> 
</div>
</div>
<!-- Fecthing Values-->
<?php while ($result=mysqli_fetch_array($query)) { ?>
<div class="row">
<div class="col-6">
      <table class="table">

            <tbody> 

                <tr>
                    <td><input type="radio" name="selectedaddress" value="<?php echo $result['id'];?>" required></td>
                    <td width="250"><?php echo $result['billingAddress'];?></td>
                    <td><?php echo $result['biilingCity'];?></td>
                    <td><?php echo $result['billingState'];?></td>
                    <td><?php echo $result['billingPincode'];?></td>
                    <td><?php echo $result['billingCountry'];?></td>
                </tr>
            </tbody>
            </table>  

</div>
<div class="col-6">
          <table class="table">
            <tbody> 
                <tr>
                    <td width="250"><?php echo $result['shippingAddress'];?></td>
                    <td><?php echo $result['shippingCity'];?></td>
                    <td><?php echo $result['shippingState'];?></td>
                    <td><?php echo $result['shippingPincode'];?></td>
                    <td><?php echo $result['shippingCountry'];?></td>
                </tr>
            </tbody>
            </table> 
</div>
</div>


<?php } endif;?>
<div align="right">
 <button class="btn-upper btn btn-primary" type="submit" name="proceedpayment">Prosseguir para Pagamento</button>
</div>
</form>

<hr />
<form method="post" name="address">

     <div class="row">
        <!--Billing Addresss --->
        <div class="col-6">
               <div class="row">
         <div class="col-9" align="center"><h5>Novo endereço de cobrança</h5><br /></div>
         <hr />
     </div>
     <div class="row">
         <div class="col-3">Endereço</div>
         <div class="col-6"><input type="text" name="baddress" id="baddress" class="form-control" required ></div>
     </div>
       <div class="row mt-3">
         <div class="col-3">Cidade</div>
         <div class="col-6"><input type="text" name="bcity" id="bcity"  class="form-control" required>
         </div>
          

     </div>

       <div class="row mt-3">
         <div class="col-3">Estado</div>
         <div class="col-6"><input type="text" name="bstate" id="bstate" class="form-control" required></div>
     </div>

          <div class="row mt-3">
         <div class="col-3">CEP</div>
         <div class="col-6"><input type="text" name="bpincode" id="bpincode" pattern="[0-9]+" title="only numbers" maxlength="6" class="form-control" required></div>
     </div>

           <div class="row mt-3">
         <div class="col-3">País</div>
         <div class="col-6"><input type="text" name="bcountry" id="bcountry" class="form-control" required></div>
     </div>
 </div>
        <!--Shipping Addresss --->
        <div class="col-6">
               <div class="row">
         <div class="col-9" align="center"><h5>Novo endereço de entrega</h5> 
            <input type="checkbox" name="adcheck" value="1"/>
            <small>Endereço de entrega igual ao endereço de cobrança</small></div>
         <hr />
     </div>
     <div class="row">
         <div class="col-3">Endereço</div>
         <div class="col-6"><input type="text" name="saddress"  id="saddress" class="form-control" required ></div>
     </div>
       <div class="row mt-3">
         <div class="col-3">Cidade</div>
         <div class="col-6"><input type="text" name="scity" id="scity" class="form-control" required>
         </div>
          
     </div>

       <div class="row mt-3">
         <div class="col-3">Estado</div>
         <div class="col-6"><input type="text" name="sstate" id="sstate" class="form-control" required></div>
     </div>

          <div class="row mt-3">
         <div class="col-3">CEP</div>
         <div class="col-6"><input type="text" name="spincode" id="spincode" pattern="[0-9]+" title="only numbers" maxlength="6" class="form-control" required></div>
     </div>

           <div class="row mt-3">
         <div class="col-3">País</div>
         <div class="col-6"><input type="text" name="scountry" id="scountry" class="form-control" required></div>
     </div>

      
 </div>
         <div class="row mt-3">
                 <div class="col-5">&nbsp;</div>
         <div class="col-6"><input type="submit" name="submit" id="submit" class="btn btn-primary" value="Add" required></div>
     </div>

</div>
 </form>

              
            </div>

 
</div>
        </section>
        <!-- Footer-->
   <?php include_once('includes/footer.php'); ?>
        <!-- Bootstrap core JS-->
        <script src="js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
        <script type="text/javascript">
    $(document).ready(function(){
        $('input[type="checkbox"]').click(function(){
            if($(this).prop("checked") == true){
                $('#saddress').val($('#baddress').val() );
                $('#scity').val($('#bcity').val());
                $('#sstate').val($('#bstate').val());
                $('#spincode').val( $('#bpincode').val());
                  $('#scountry').val($('#bcountry').val() );
            } 
            
        });
    });
</script>
    </body>
</html>
<?php ?>
