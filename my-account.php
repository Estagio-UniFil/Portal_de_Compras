<?php
namespace Models;
session_start();
error_reporting(0);
include_once('includes/config.php');
include_once('models.login.php');

if (strlen($_SESSION['login']) == 0) {   
    header('location:login.php');
    exit();
}

// Classe Users para manipulação de usuários
class Users {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function updateUserInfo($userId, $name, $contactNo) {
    $contactNo = preg_replace('/\D/', '', $contactNo);

    $query = $this->con->prepare("UPDATE users SET name = ?, contactno = ? WHERE id = ?");
    $query->bind_param("ssi", $name, $contactNo, $userId);

    if ($query->execute()) {
        return ['status' => 'success', 'message' => "Suas informações foram atualizadas!"];
    } else {
        return ['status' => 'error', 'message' => "Erro ao atualizar as informações!"];
    }
}
}


# Fim da classe Users

// Função para formatar telefone para exibir no formulário
function formatPhone($phone) {
    $clean = preg_replace('/\D/', '', $phone);
    if (strlen($clean) === 11) {
        return '(' . substr($clean, 0, 2) . ') ' . substr($clean, 2, 5) . '-' . substr($clean, 7);
    }
    return $phone; // Retorna original se inválido
}

// Criando instância da classe Users
$users = new Users($con);

// Atualizando informações do usuário
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $rawContact = $_POST['contactno'] ?? '';
    $cleanContactNo = preg_replace('/\D/', '', $rawContact);
    $userId = $_SESSION['id'];

    if (!preg_match('/^\d{11}$/', $cleanContactNo)) {
        $_SESSION['toast'] = [
            'status' => 'error',
            'message' => "O número de contato deve conter DDD + número (11 dígitos). Ex: 43911112222"
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $result = $users->updateUserInfo($userId, $name, $cleanContactNo);

    $_SESSION['toast'] = $result;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// Configuração de fuso horário
date_default_timezone_set('America/Sao_Paulo'); 
$currentTime = date('d-m-Y h:i:s A', time());
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <style>
        .toast {
    position: fixed;
    top: 30%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    padding: 15px 25px;
    border-radius: 6px;
    z-index: 9999;
    font-weight: bold;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    animation: fadein 0.3s, fadeout 0.3s 1.7s;
    text-align: center;
    max-width: 90%;
}

.toast-error {
    background-color: #dc3545; /* vermelho para erro */
}

.toast-success {
    background-color: #28a745; /* verde para sucesso */
}

    </style>

    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Minha Conta</title>

    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Customizable CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/red.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.css">
    <link rel="stylesheet" href="assets/css/owl.transitions.css">
    <link href="assets/css/lightbox.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/rateit.css">
    <link rel="stylesheet" href="assets/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <script type="text/javascript">
    function valid()
    {
        if(document.chngpwd.cpass.value=="")
        {
            alert("O campo de senha atual está vazio!!");
            document.chngpwd.cpass.focus();
            return false;
        }
        else if(document.chngpwd.newpass.value=="")
        {
            alert("O campo Nova senha está vazio !!");
            document.chngpwd.newpass.focus();
            return false;
        }
        else if(document.chngpwd.cnfpass.value=="")
        {
            alert("O campo Confirmar senha está vazio!!");
            document.chngpwd.cnfpass.focus();
            return false;
        }
        else if(document.chngpwd.newpass.value!= document.chngpwd.cnfpass.value)
        {
            alert("Os campos Senha e Confirmar Senha não correspondem !!");
            document.chngpwd.cnfpass.focus();
            return false;
        }
        return true;
    }
    </script>
</head>
<body class="cnt-home">

<?php if (!empty($_SESSION['toast'])): 
    $toast = $_SESSION['toast'];
    $toastClass = ($toast['status'] === 'success') ? 'toast-success' : 'toast-error';
?>
    <div id="toast" class="toast <?php echo $toastClass; ?>">
        <?php echo htmlentities($toast['message']); ?>
    </div>
    <?php unset($_SESSION['toast']); ?>
<?php endif; ?>


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
                <li class='active'>Checkout</li>
            </ul>
        </div>
    </div>
</div>

<div class="body-content outer-top-bd">
    <div class="container">
        <div class="checkout-box inner-bottom-sm">
            <div class="row">
                <div class="col-md-8">
                    <div class="panel-group checkout-steps" id="accordion">
                        <div class="panel panel-default checkout-step-01">
                            <div class="panel-heading">
                                <h4 class="unicase-checkout-title">
                                    <a data-toggle="collapse" class="" data-parent="#accordion" href="#collapseOne">
                                        <span>1</span>Meu Perfil
                                    </a>
                                </h4>
                            </div>

                            <div id="collapseOne" class="panel-collapse collapse in">
                                <div class="panel-body">
                                    <div class="row">		
                                        <h4>Informações Pessoais</h4>
                                        <div class="col-md-12 col-sm-12 already-registered-login">

                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM users WHERE id='" . $_SESSION['id'] . "'");
                                        while ($row = mysqli_fetch_array($query)) {
                                            $formattedContact = formatPhone($row['contactno']);
                                        ?>

                                            <form class="register-form" role="form" method="post">
                                                <div class="form-group">
                                                    <label class="info-title" for="name">Nome<span>*</span></label>
                                                    <input type="text" class="form-control unicase-form-control text-input" value="<?php echo htmlentities($row['name']); ?>" id="name" name="name" required="required">
                                                </div>

                                                <div class="form-group">
                                                    <label class="info-title" for="exampleInputEmail1">Endereço de Email<span>*</span></label>
                                                    <input type="email" class="form-control unicase-form-control text-input" id="exampleInputEmail1" value="<?php echo htmlentities($row['email']); ?>" readonly>
                                                </div>

                                                <div class="form-group">
                                                    <label for="contactno" class="info-title">Número de Contato <span>*</span></label>
                                                    <input type="text" class="form-control" id="contactno" name="contactno" maxlength="15" required placeholder="(43) 91111-1111" value="<?php echo htmlentities($formattedContact); ?>" />
                                                    <small class="text-muted">Digite DDD + número (ex: 43911111111)</small>
                                                    <span id="contact-error" style="color: red;"></span>
                                                </div>

                                                <button type="submit" name="update" class="btn-upper btn btn-primary checkout-page-button">Atualizar</button>
                                            </form>
                                        <?php } ?>

                                        </div>	
                                    </div>			
                                </div>
                            </div>
                        </div>						
                    </div>
                </div>

                <?php include('includes/myaccount-sidebar.php');?>

            </div>
        </div>
        <?php include('includes/brands-slider.php');?>
    </div>
</div>

<?php include('includes/footer.php');?>

<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/echo.min.js"></script>
<script src="assets/js/jquery.easing-1.3.min.js"></script>
<script src="assets/js/bootstrap-slider.min.js"></script>
<script src="assets/js/jquery.rateit.min.js"></script>
<script type="text/javascript" src="assets/js/lightbox.min.js"></script>
<script src="assets/js/bootstrap-select.min.js"></script>
<script src="assets/js/wow.min.js"></script>
<script src="assets/js/scripts.js"></script>
<script src="switchstylesheet/switchstylesheet.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function () {
    const phoneField = $('#contactno');
    const errorSpan = $('#contact-error');

    // Aplica a máscara ao campo
    phoneField.mask('(00) 00000-0000', {
        placeholder: "(__) _____-____"
    });

    function validarTelefone() {
        const numeroLimpo = phoneField.cleanVal(); // Remove máscara, deixa só números
        if (numeroLimpo.length !== 11) {
            errorSpan.text('O número deve conter exatamente 11 dígitos.');
        } else {
            errorSpan.text('');
        }
    }

    // Valida a cada tecla digitada
    phoneField.on('input', validarTelefone);

    // Valida novamente ao sair do campo
    phoneField.on('blur', validarTelefone);

    // Previne envio do formulário se número inválido
    $('form.register-form').on('submit', function (e) {
        const numeroLimpo = phoneField.cleanVal();
        if (numeroLimpo.length !== 11) {
            e.preventDefault();
            errorSpan.text('O número deve conter exatamente 11 dígitos.');
            phoneField.focus();
        }
    });
});
</script>

<script>
setTimeout(() => {
    const toast = document.getElementById('toast');
    if (toast) toast.style.display = 'none';
}, 2000);
</script>

</body>
</html>
