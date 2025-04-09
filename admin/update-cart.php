<?php
session_start();
include_once('includes/config.php');
header('Content-Type: application/json');

if (isset($_POST['id']) && isset($_POST['quantity'])) {
    $id = intval($_POST['id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity === 0) {
        unset($_SESSION['cart'][$id]);
        echo json_encode(['success' => true, 'msg' => 'Produto removido do carrinho.']);
    } else {
        $_SESSION['cart'][$id]['quantity'] = $quantity;
        echo json_encode(['success' => true, 'msg' => 'Quantidade atualizada.']);
    }
} else {
    echo json_encode(['success' => false, 'msg' => 'Dados inválidos.']);
}