<?php
include('include/config.php');
$orderid = intval($_GET['oid']);

$ret = mysqli_query($con,"SELECT * FROM ordertrackhistory WHERE orderId='$orderid'");
if (mysqli_num_rows($ret) > 0) {
    echo '<table class="table table-bordered table-striped" style="margin-top:1%;">
        <tr>
            <th colspan="3" style="color:blue; font-size:16px; text-align:center;">Histórico de Pedidos</th>
        </tr>
        <tr>
            <th>Observação</th>
            <th>Status</th>
            <th>Data</th>
        </tr>';
    while ($row = mysqli_fetch_array($ret)) {
        echo "<tr>
            <td>{$row['remark']}</td>
            <td>{$row['status']}</td>
            <td>{$row['postingDate']}</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "<div class='alert alert-info'>Nenhum histórico disponível.</div>";
}
?>
