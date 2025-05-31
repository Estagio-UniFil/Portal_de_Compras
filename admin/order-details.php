<?php
session_start();
include('include/config.php');
if(strlen($_SESSION['alogin'])==0) {	
    header('location:index.php');
} else {
date_default_timezone_set('America/Sao_Paulo');
$currentTime = date('d-m-Y h:i:s A', time());
$orderid = intval($_GET['oid']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <title>Admin | Pedidos Pendentes</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link href="images/icons/css/font-awesome.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .popup-loading {
            text-align: center;
            font-style: italic;
            color: #888;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<?php include('include/header.php'); ?>
<div class="wrapper">
    <div class="container">
        <div class="row">
            <?php include('include/sidebar.php'); ?>
            <div class="span9">
                <div class="content">
                    <div class="module">
                        <div class="module-head">
                            <h3>Detalhes dos Pedidos #<?php echo $orderid; ?></h3>
                        </div>
                        <div class="module-body table">
                            <br />
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped display">
                                    <tbody>
                                    <?php
                                    $query = mysqli_query($con, "SELECT orders.id as oid, users.name as username, users.email as useremail, users.contactno as usercontact,
                                        users.shippingAddress as shippingaddress, users.shippingCity as shippingcity, users.shippingState as shippingstate,
                                        users.shippingPincode as shippingpincode, products.productName as productname, products.shippingCharge as shippingcharge,
                                        orders.quantity as quantity, orders.orderDate as orderdate, products.productPrice as productprice,
                                        billingAddress,billingState,billingCity,billingPincode,products.id as pid,productImage1
                                        FROM orders 
                                        JOIN users ON orders.userId = users.id 
                                        JOIN products ON products.id = orders.productId 
                                        WHERE orders.id = '$orderid'");
                                    while($row = mysqli_fetch_array($query)) {
                                    ?>
                                        <tr>
                                            <th>Id do Pedido</th>
                                            <td><?php echo htmlentities($row['oid']); ?></td>
                                            <th>Data do Pedido</th>
                                            <td><?php echo htmlentities($row['orderdate']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nome de Usuário</th>
                                            <td><?php echo htmlentities($row['username']); ?></td>
                                            <th>Email/Contato</th>
                                            <td><?php echo htmlentities($row['useremail']) . " / " . htmlentities($row['usercontact']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Endereço de Envio</th>
                                            <td><?php echo htmlentities($row['shippingaddress'] . "," . $row['shippingcity'] . "," . $row['shippingstate'] . "-" . $row['shippingpincode']); ?></td>
                                            <th>Endereço de Cobrança</th>
                                            <td><?php echo htmlentities($row['billingAddress'] . "," . $row['billingCity'] . "," . $row['billingState'] . "-" . $row['billingPincode']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nome do Produto</th>
                                            <td><?php echo htmlentities($row['productname']); ?></td>
                                            <th>Imagem</th>
                                            <td><img src="productimages/<?php echo htmlentities($row['pid']."/".$row['productImage1']); ?>" width="100"></td>
                                        </tr>
                                        <tr>
                                            <th>Quantidade</th>
                                            <td><?php echo htmlentities($row['quantity']); ?></td>
                                            <th>Preço</th>
                                            <td><?php echo htmlentities($row['productprice']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Frete</th>
                                            <td><?php echo htmlentities($row['shippingcharge']); ?></td>
                                            <th>Total</th>
                                            <td><?php echo htmlentities($row['quantity'] * $row['productprice'] + $row['shippingcharge']); ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>

                                <div id="history-section">
                                    <!-- histórico será carregado aqui via AJAX -->
                                    <div class="popup-loading">Carregando histórico...</div>
                                </div>

                                <div style="margin-top: 20px;">
                                    <button class="btn btn-primary" onclick="openUpdatePopup(<?php echo $orderid; ?>)">Atualizar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- /.content -->
            </div> <!-- /.span9 -->
        </div>
    </div> <!-- /.container -->
</div> <!-- /.wrapper -->

<?php include('include/footer.php'); ?>

<script>
function openUpdatePopup(oid) {
    const popup = window.open(`updateorder.php?oid=${oid}`, 'Atualizar Pedido', 'width=700,height=600');

    const interval = setInterval(() => {
        if (popup.closed) {
            clearInterval(interval);
            loadHistory(oid); // atualiza histórico ao fechar popup
        }
    }, 500);
}

function loadHistory(oid) {
    $.ajax({
        url: 'fetch-history.php',
        type: 'GET',
        data: { oid: oid },
        success: function(data) {
            $('#history-section').html(data);
        },
        error: function() {
            $('#history-section').html('<div class="alert alert-danger">Erro ao carregar histórico.</div>');
        }
    });
}

// Carregar histórico ao entrar na página
$(document).ready(function() {
    loadHistory(<?php echo $orderid; ?>);
});
</script>

<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
<?php } ?>
