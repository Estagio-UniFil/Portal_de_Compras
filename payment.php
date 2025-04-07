<?php
namespace Models;
session_start();
include_once('includes/config.php');
include_once('models.login.php');
include_once('models.checkout.php');

if (strlen($_SESSION['id']) == 0) {   
    header('location:logout.php');
    exit();
}

// Redireciona para checkout se o endereço não estiver definido
if ($_SESSION['address'] == 0) {
    echo "<script type='text/javascript'> document.location ='checkout.php'; </script>";
    exit();
}

// Classe Orders
class Orders {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    // Método para fazer um pedido
    public function placeOrder($userId, $addressId, $totalAmount, $paymentType, $transactionNumber) {
        $orderNumber = mt_rand(100000000, 999999999); // Gera um número de pedido aleatório

        // Insere o pedido na tabela orders
        $query = $this->con->prepare("INSERT INTO orders (orderNumber, userId, addressId, totalAmount, txnType, txnNumber) VALUES (?, ?, ?, ?, ?, ?)");
        $query->bind_param("iiidss", $orderNumber, $userId, $addressId, $totalAmount, $paymentType, $transactionNumber);

        if ($query->execute()) {
            // Prepara a transação para mover itens do carrinho para os detalhes do pedido
            $sql = "INSERT INTO ordersdetails (userId, productId, quantity) 
                    SELECT userID, productId, productQty FROM cart WHERE userID = ?; ";
            $sql .= "UPDATE ordersdetails SET orderNumber = ? WHERE userId = ? AND orderNumber IS NULL; ";
            $sql .= "DELETE FROM cart WHERE userID = ?";

            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("iiii", $userId, $orderNumber, $userId, $userId);

            if ($stmt->execute()) {
                unset($_SESSION['address']);
                unset($_SESSION['gtotal']);
                return $orderNumber; // Retorna o número do pedido
            }
        }
        return false; // Indica falha na operação
    }
}

// Criando a instância da classe Orders
$orders = new Orders($con);

// Verifica se o formulário foi enviado
if (isset($_POST['submit'])) {
    $userId = $_SESSION['id'];
    $addressId = $_SESSION['address'];
    $totalAmount = $_SESSION['gtotal'];
    $paymentType = $_POST['paymenttype'];
    $transactionNumber = $_POST['txnnumber'];

    // Chama o método para processar o pedido
    $orderNumber = $orders->placeOrder($userId, $addressId, $totalAmount, $paymentType, $transactionNumber);

    if ($orderNumber) {
        echo '<script>alert("Seu pedido foi feito com sucesso. O número do pedido é ' . $orderNumber . '")</script>';
        echo "<script type='text/javascript'> document.location ='my-orders.php'; </script>";
    } else {
        echo "<script>alert('Algo deu errado. Por favor, tente novamente');</script>";
        echo "<script type='text/javascript'> document.location ='payment.php'; </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Portal de Compras | Pagamento</title>
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
                    <h1 class="display-4 fw-bolder">Pagamento</h1>
                </div>

            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4  mt-5">
     

<form method="post" name="signup">
     <div class="row">
         <div class="col-2">Pagamento Total</div>
         <div class="col-6"><input type="text" name="totalamount" value="<?php echo  $_SESSION['gtotal'];?>" class="form-control" readonly ></div>
     </div>
       <div class="row mt-3">
         <div class="col-2">Tipo de pagamento</div>
         <div class="col-6">

            <select class="form-control" name="paymenttype" id="paymenttype" required>
                <option value="">Select</option>
                <option value="Boleto">Boleto</option>
                <option value="Cartão de Crédtio">Cartão de Crédito</option>
                <option value="PIX">PIX</option>
            </select>
         </div>
          
     </div>

       <div class="row mt-3" id="txnno">
         <div class="col-2">Número de Transação</div>
         <div class="col-6"><input type="text" name="txnnumber" id="txnnumber" class="form-control" maxlength="50"></div>
     </div>


               <div class="row mt-3">
                 <div class="col-4">&nbsp;</div>
         <div class="col-6"><input type="submit" name="submit" id="submit" class="btn btn-primary" required></div>
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
    </body>
</html>
<script type="text/javascript">

  //For report file
  $('#txnno').hide();
  $(document).ready(function(){
  $('#paymenttype').change(function(){
  if($('#paymenttype').val()=='Cash on Delivery')
  {
  $('#txnno').hide();
  } else if($('#paymenttype').val()==''){
      $('#txnno').hide();
  } else{
    $('#txnno').show();
  jQuery("#txnnumber").prop('required',true);  
  }
})}) 
</script>
<?php  ?>
