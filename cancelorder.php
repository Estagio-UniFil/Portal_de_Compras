<?php
namespace Models;
session_start();
error_reporting(0);
include_once('includes/config.php');

class Orders {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    // Obtém informações do pedido
    public function getOrderDetails($orderId) {
        $stmt = $this->con->prepare("SELECT orderNumber, orderStatus FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Registra histórico e cancela o pedido
    public function cancelOrder($orderId, $reason) {
        $resStatus = "Cancelado";
        $canceledBy = "Usuário";

        // Inserir histórico de rastreamento
        $stmt1 = $this->con->prepare("INSERT INTO ordertrackhistory (orderId, remark, status, canceledBy) VALUES (?, ?, ?, ?)");
        $stmt1->bind_param("isss", $orderId, $reason, $resStatus, $canceledBy);
        $stmt1->execute();

        // Atualizar status do pedido
        $stmt2 = $this->con->prepare("UPDATE orders SET orderStatus = ? WHERE id = ?");
        $stmt2->bind_param("si", $resStatus, $orderId);
        $stmt2->execute();

        if ($stmt1 && $stmt2) {
            echo '<script>alert("Seu pedido foi cancelado.");</script>';
        } else {
            echo '<script>alert("Algo deu errado. Por favor, tente novamente.");</script>';
        }
    }
}

// Criar instância da classe Orders
$orderObj = new Orders(db: $con);

// Verifica se o formulário foi enviado
if (isset($_POST['submit'])) {
    $orderId = $_GET['oid'];
    $reason = $_POST['restremark'];
    $orderObj->cancelOrder($orderId, $reason);
}

// Obtém detalhes do pedido
$orderId = $_GET['oid'];
$orderDetails = $orderObj->getOrderDetails($orderId);
$status = $orderDetails['orderStatus'];

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cancelamento de Pedido</title>
</head>
<body>

<div style="margin-left:50px;">
    <table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%; text-align: center;">
        <tr align="center">
            <th colspan="2">Cancelar Pedido<?php echo $orderDetails['orderNumber']; ?></th>
        </tr>
        <tr>
            <th>Nº do pedido.</th>
            <th>Situação Atual</th>
        </tr>
        <tr>
            <td><?php echo $orderDetails['orderNumber']; ?></td>
            <td>
                <?php
                if (empty($status)) {
                    echo "Aguardando Confirmação";
                } else {
                    echo $status;
                }
                ?>
            </td>
        </tr>
    </table>

    <?php if (empty($status) || in_array($status, ["Packed", "Dispatched", "In Transit"])) { ?>
        <form method="post">
            <table>
                <tr>
                    <th>Motivo do Cancelamento</th>
                    <td>
                        <textarea name="restremark" rows="5" cols="50" required></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <button type="submit" name="submit">Atualizar Pedido</button>
                    </td>
                </tr>
            </table>
        </form>
    <?php } else { ?>
        <p style="color:red; font-size:20px;">
            <?php echo ($status == 'Cancelado') ? "Pedido já cancelado. Não precisa cancelar novamente." : "Você não pode cancelar isso. O pedido está pronto para entrega ou foi entregue."; ?>
        </p>
    <?php } ?>
</div>

</body>
</html>

     