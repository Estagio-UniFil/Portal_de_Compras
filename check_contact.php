<?php
include("includes/config.php");

if (!empty($_POST["contactno"])) {
    $contactno = $_POST["contactno"];
    $query = mysqli_query($con, "SELECT id FROM users WHERE contactno = '$contactno'");
    $count = mysqli_num_rows($query);
    
    if ($count > 0) {
        echo "<span style='color:red'>Número de contato já registrado.</span>";
    } else {
        echo "<span style='color:green'>Número disponível.</span>";
    }
}
?>
