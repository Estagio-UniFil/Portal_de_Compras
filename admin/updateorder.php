<?php
session_start();
include_once 'include/config.php';

if(strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit;
}

$oid = intval($_GET['oid'] ?? 0);
$currrentSt = '';

// Atualiza pedido se for chamada via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    $status = $_POST['status'];
    $remark = $_POST['remark'];

    mysqli_query($con, "INSERT INTO ordertrackhistory(orderId,status,remark) VALUES('$oid','$status','$remark')");
    mysqli_query($con, "UPDATE orders SET orderStatus='$status' WHERE id='$oid'");

    // Recarrega histórico
    ob_start();
    $ret = mysqli_query($con,"SELECT * FROM ordertrackhistory WHERE orderId='$oid'");
    while($row = mysqli_fetch_array($ret)) {
        echo "
        <tr height='20'>
            <td class='fontkink1'><b>Na Data:</b></td>
            <td class='fontkink'>{$row['postingDate']}</td>
        </tr>
        <tr height='20'>
            <td class='fontkink1'><b>Status:</b></td>
            <td class='fontkink'>{$row['status']}</td>
        </tr>
        <tr height='20'>
            <td class='fontkink1'><b>Observação:</b></td>
            <td class='fontkink'>{$row['remark']}</td>
        </tr>
        <tr><td colspan='2'><hr /></td></tr>
        ";
    }
    $historyHtml = ob_get_clean();

    echo json_encode([
        'status' => $status,
        'date' => date('d/m/Y H:i:s'),
        'historyHtml' => $historyHtml
    ]);
    exit;
}

// Verifica status atual
$rt = mysqli_query($con,"SELECT orderStatus FROM orders WHERE id='$oid'");
if($row = mysqli_fetch_array($rt)) {
    $currrentSt = $row['orderStatus'];
}

$finalStates = ['Entregue', 'Closed']; // estados finais que bloqueiam atualização
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Atualizar em Conformidade</title>
  <link href="style.css" rel="stylesheet" />
  <link href="anuj.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div id="success-message" style="display:none; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:15px; margin:20px; border-radius:5px; font-family:Arial,sans-serif;"></div>

<div style="margin-left:50px;">
  <form id="updateticket" method="post" action="javascript:void(0);">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">

      <tr height="50">
        <td colspan="2" class="fontkink2"><div class="fontpink2"><b>Atualizar em Conformidade!</b></div></td>
      </tr>

      <tr height="30">
        <td class="fontkink1"><b>ID do Pedido:</b></td>
        <td class="fontkink"><?php echo $oid; ?></td>
      </tr>

      <tbody id="history-section">
      <?php
      $ret = mysqli_query($con,"SELECT * FROM ordertrackhistory WHERE orderId='$oid'");
      while($row = mysqli_fetch_array($ret)) {
      ?>
        <tr height="20">
          <td class="fontkink1"><b>Na Data:</b></td>
          <td class="fontkink"><?php echo $row['postingDate']; ?></td>
        </tr>
        <tr height="20">
          <td class="fontkink1"><b>Status:</b></td>
          <td class="fontkink"><?php echo $row['status']; ?></td>
        </tr>
        <tr height="20">
          <td class="fontkink1"><b>Observação:</b></td>
          <td class="fontkink"><?php echo $row['remark']; ?></td>
        </tr>
        <tr><td colspan="2"><hr /></td></tr>
      <?php } ?>
      </tbody>

      <?php if (in_array($currrentSt, $finalStates)) { ?>
        <tr>
          <td colspan="2" style="color:red; font-weight:bold;">
            Este pedido não pode mais ser atualizado. Status atual: <?php echo htmlentities($currrentSt); ?>
          </td>
        </tr>
        <tr>
          <td></td>
          <td><input type="button" value="Fechar" onclick="window.close();" style="cursor:pointer;" /></td>
        </tr>
      <?php } else { ?>
        <tr height="50">
          <td class="fontkink1">Status:</td>
          <td class="fontkink">
            <select name="status" class="fontkink" required>
              <option value="">Selecionar Status</option>
              <option value="Pedido Criado">Criado</option>
              <option value="Pagamento Confirmado">Pagamento Confirmado</option>
              <option value="Em Transporte">Em Transporte</option>
              <option value="Entregue">Entregue</option>
              <option value="Pagamento Expirado">Pagamento Vencido</option>
              <option value="Closed">Pedido Anulado</option>
            </select>
          </td>
        </tr>
        <tr>
          <td class="fontkink1">Observação:</td>
          <td class="fontkink">
            <textarea name="remark" cols="50" rows="7" required></textarea>
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            <input type="submit" value="Atualizar" style="cursor:pointer;" />
            &nbsp;&nbsp;
            <input type="button" value="Fechar" onclick="window.close();" style="cursor:pointer;" />
          </td>
        </tr>
      <?php } ?>
    </table>
  </form>
</div>

<script>
  $('#updateticket').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
      type: 'POST',
      url: '', // mesma página
      data: $(this).serialize() + '&ajax=1&oid=<?php echo $oid; ?>',
      success: function(res) {
        const data = JSON.parse(res);
        $('#history-section').html(data.historyHtml);
        $('#success-message').html(
          `✅ Pedido #<?php echo $oid; ?> atualizado com sucesso para o status: <strong>${data.status.toUpperCase()}</strong><br>📅 Atualizado em: ${data.date}`
        ).fadeIn();

        setTimeout(() => $('#success-message').fadeOut(), 4000);
      }
    });
  });
</script>

</body>
</html>


     