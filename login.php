<?php
namespace Models;
session_start();
error_reporting(0);
include('includes/config.php');

class Users {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    // Método para verificar se o e-mail já está registrado
    public function checkEmailExists($email) {
        $stmt = $this->con->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $count = 0;
        $stmt->bind_result($count);
        $stmt->fetch();

        // Garante que $count está definido antes de retornar
        $stmt->close();
        return $count > 0;  // Retorna true se o e-mail já existir, caso contrário false
    }

    // Método para registrar um novo usuário
    public function createAccount($name, $email, $contactno, $password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->con->prepare("INSERT INTO users (name, email, contactno, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $contactno, $passwordHash);
        return $stmt->execute();
    }

    // Método para login do usuário
    public function login($email, $password) {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['login'] = $email;
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['name'];

            $uip = $_SERVER['REMOTE_ADDR'];
            $status = 1;
            $log = $this->con->prepare("INSERT INTO userlog (userEmail, userip, status) VALUES (?, ?, ?)");
            $log->bind_param("ssi", $email, $uip, $status);
            $log->execute();

            return true;
        } else {
            $uip = $_SERVER['REMOTE_ADDR'];
            $status = 0;
            $log = $this->con->prepare("INSERT INTO userlog (userEmail, userip, status) VALUES (?, ?, ?)");
            $log->bind_param("ssi", $email, $uip, $status);
            $log->execute();

            return false;
        }
    }
}

$user = new Users($con);

// Variáveis para manter os valores do formulário
$name = $email = $contactno = "";
$error = "";

if (isset($_POST['submit'])) {
    $name = $_POST['fullname'] ?? '';
    $email = $_POST['emailid'] ?? '';
    $contactno = preg_replace('/\D/', '', $_POST['contactno'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmpassword'] ?? '';

    // Validações básicas
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, insira um endereço de email válido.";
    } elseif (empty($password)) {
        $error = "A senha é obrigatória.";
    } elseif ($password !== $confirmPassword) {
        $error = "As senhas não coincidem.";
    } elseif (!preg_match('/^\d{11}$/', $contactno)) {
        $error = "O número de contato deve conter DDD + número (11 dígitos)";
    } else {
        // Verificar se o e-mail já está registrado
        if ($user->checkEmailExists($email)) {
            $error = "Este e-mail já está registrado. Tente outro.";
        } else {
            // Registrar o usuário
            if ($user->createAccount($name, $email, $contactno, $password)) {
                $_SESSION['msg'] = "Você foi registrado com sucesso!";
                $_SESSION['msg_type'] = "success";
                header("Location: login.php");
                exit();
            } else {
                $error = "O registro falhou. Tente novamente.";
            }
        }
    }
}

if (isset($_POST['login'])) {
    $loginEmail = $_POST['email'] ?? '';
    $loginPassword = $_POST['password'] ?? '';

    if ($user->login($loginEmail, $loginPassword)) {
        header("location:my-cart.php");
        exit();
    } else {
        $_SESSION['errmsg'] = "ID de e-mail ou senha inválidos";
        header("location:login.php");
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>Portal de Compras | Entrar | Inscrever-se</title>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <link rel="stylesheet" href="assets/css/red.css" />
    <link rel="stylesheet" href="assets/css/owl.carousel.css" />
    <link rel="stylesheet" href="assets/css/owl.transitions.css" />
    <link href="assets/css/lightbox.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/animate.min.css" />
    <link rel="stylesheet" href="assets/css/rateit.css" />
    <link rel="stylesheet" href="assets/css/bootstrap-select.min.css" />
    <link rel="stylesheet" href="assets/css/font-awesome.min.css" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" />

    <style>
    .message {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 20px 40px;
        border-radius: 10px;
        font-size: 18px;
        text-align: center;
        z-index: 9999;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }
    .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 2px solid #c3e6cb;
    }
    .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 2px solid #f5c6cb;
    }
    </style>

</head>
<body class="cnt-home">

<?php if (!empty($_SESSION['msg'])): ?>
<div class="message <?= $_SESSION['msg_type'] ?>">
    <?= htmlentities($_SESSION['msg']) ?>
</div>
<?php 
unset($_SESSION['msg'], $_SESSION['msg_type']); 
endif; ?>

<?php if (!empty($error)): ?>
<div class="message error"><?= htmlentities($error) ?></div>
<?php endif; ?>

<script>
setTimeout(function() {
    const msgBox = document.querySelector('.message');
    if (msgBox) {
        msgBox.style.display = 'none';
    }
}, 5000);
</script>

<header class="header-style-1">
    <?php include('includes/top-header.php');?>
    <?php include('includes/main-header.php');?>
    <?php include('includes/menu-bar.php');?>
</header>

<div class="breadcrumb">
    <div class="container">
        <div class="breadcrumb-inner">
            <ul class="list-inline list-unstyled">
                <li><a href="index.php">Home</a></li>
                <li class='active'>Autenticação</li>
            </ul>
        </div>
    </div>
</div>

<div class="body-content outer-top-bd">
    <div class="container">
        <div class="sign-in-page inner-bottom-sm">
            <div class="row">

                <!-- Login -->
                <div class="col-md-6 col-sm-6 sign-in">
                    <h4>Entrar</h4>
                    <p>Olá, bem-vindo à sua conta.</p>
                    <form class="register-form outer-top-xs" method="post" novalidate>
                        <span style="color:red;">
                        <?php
                        if (!empty($_SESSION['errmsg'])) {
                            echo htmlentities($_SESSION['errmsg']);
                            $_SESSION['errmsg'] = "";
                        }
                        ?>
                        </span>
                        <div class="form-group">
                            <label for="loginEmail" class="info-title">Endereço de Email <span>*</span></label>
                            <input type="email" name="email" class="form-control unicase-form-control text-input" id="loginEmail" required />
                        </div>
                        <div class="form-group">
                            <label for="loginPassword" class="info-title">Senha <span>*</span></label>
                            <input type="password" name="password" class="form-control unicase-form-control text-input" id="loginPassword" required />
                        </div>
                        <div class="radio outer-xs">
                            <a href="forgot-password.php" class="forgot-password pull-right">Esqueceu sua Senha?</a>
                        </div>
                        <button type="submit" class="btn-upper btn btn-primary checkout-page-button" name="login">Login</button>
                    </form>
                </div>

                <!-- Cadastro -->
                <div class="col-md-6 col-sm-6 create-new-account">
                    <h4 class="checkout-subtitle">Criar Conta</h4>
                    <p class="text title-tag-line">Crie sua própria conta de compras.</p>
                    <form class="register-form outer-top-xs" method="post" name="register" id="registerForm" novalidate>
                        <div class="form-group">
                            <label for="fullname" class="info-title">Nome Completo <span>*</span></label>
                            <input type="text" class="form-control unicase-form-control text-input" id="fullname" name="fullname" required
                                value="<?= htmlentities($name) ?>" />
                        </div>

                        <div class="form-group">
                            <label for="email" class="info-title">Endereço de Email <span>*</span></label>
                            <input type="email" class="form-control" id="email" name="emailid" required placeholder="exemplo@dominio.com"
                                value="<?= htmlentities($email) ?>" />
                            <span id="email-error" style="color:red;"></span>
                        </div>

                        <div class="form-group">
                            <label for="contactno" class="info-title">Número de Contato <span>*</span></label>
                            <input type="text" class="form-control" id="contactno" name="contactno" maxlength="15" required placeholder="(43) 91111-1111"
                                value="<?= !empty($contactno) ? '(' . substr($contactno, 0, 2) . ') ' . substr($contactno, 2, 5) . '-' . substr($contactno, 7) : '' ?>" />
                            <small class="text-muted">Digite DDD + número (ex: 43911111111)</small>
                            <span id="contact-error" style="color: red;"></span>
                        </div>

                        <div class="form-group">
                            <label for="password" class="info-title">Senha <span>*</span></label>
                            <input type="password" class="form-control unicase-form-control text-input" id="password" name="password" required />
                            <span id="password-error" style="color:red;"></span>
                        </div>

                        <div class="form-group">
                            <label for="confirmpassword" class="info-title">Confirmar Senha <span>*</span></label>
                            <input type="password" class="form-control unicase-form-control text-input" id="confirmpassword" name="confirmpassword" required />
                            <span id="confirmpassword-error" style="color:red;"></span>
                        </div>

                        <button type="submit" name="submit" class="btn-upper btn btn-primary checkout-page-button" id="submit">Criar</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
$(document).ready(function(){
    $('#contactno').mask('(00) 00000-0000');

    // Validação simples do email no frontend para feedback rápido
    $('#email').on('input', function() {
        const email = $(this).val();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regex.test(email)) {
            $('#email-error').text('Por favor, insira um email válido.');
        } else {
            $('#email-error').text('');
        }
    });
});
</script>

</body>
</html>


