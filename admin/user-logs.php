<?php
session_start();
include('include/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

// Classe POO
class UserLog {
    private $con;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
    }

    // Método para registrar login
    public function logLogin($email, $ip): mixed {
        $status = 1;
        $stmt = $this->con->prepare("INSERT INTO userlog(userEmail, userip, status) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $email, $ip, $status);
        return $stmt->execute();
    }

    // Método para registrar logout
    public function logLogout($email, $ip) {
        $stmt = $this->con->prepare("UPDATE userlog SET logout = NOW() WHERE userEmail = ? AND userip = ? AND logout IS NULL ORDER BY loginTime DESC LIMIT 1");
        $stmt->bind_param("ss", $email, $ip);
        return $stmt->execute();
    }

    // Método para listar todos os logs
    public function listAllLogs() {
        return $this->con->query("SELECT * FROM userlog ORDER BY loginTime DESC");
    }
}

// Instância da classe
$userLog = new UserLog($con);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <title>Admin | Registro de Usuários</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link href="images/icons/css/font-awesome.css" rel="stylesheet">
</head>
<body>
<?php include('include/header.php');?>

<div class="wrapper">
    <div class="container">
        <div class="row">
            <?php include('include/sidebar.php'); ?>
            <div class="span9">
                <div class="content">
                    <div class="module">
                        <div class="module-head">
                            <h3>Gerenciar Usuários</h3>
                        </div>
                        <div class="module-body table">
                            <table class="datatable-1 table table-bordered table-striped display" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>E-mail do Usuário</th>
                                        <th>IP do Usuário</th>
                                        <th>Hora de Login</th>
                                        <th>Hora de Logout</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $logs = $userLog->listAllLogs();
                                    $cnt = 1;
                                    while ($row = $logs->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$cnt}</td>
                                                <td>" . htmlentities($row['userEmail']) . "</td>
                                                <td>" . htmlentities($row['userip']) . "</td>
                                                <td>" . htmlentities($row['loginTime']) . "</td>
                                                <td>" . htmlentities($row['logout']) . "</td>
                                                <td>" . ($row['status'] == 1 ? 'Bem-sucedida' : 'Erro') . "</td>
                                              </tr>" ;
                                        $cnt++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>						
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>

<script src="scripts/jquery-1.9.1.min.js"></script>
<script src="scripts/jquery-ui-1.10.1.custom.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="scripts/datatables/jquery.dataTables.js"></script>
<script>

$(document).ready(function() {
    $('.datatable-1').dataTable();
    $('.dataTables_paginate').addClass("btn-group datatable-pagination");
    $('.dataTables_paginate > a').wrapInner('<span />');
    $('.dataTables_paginate > a:first-child').append('<i class="icon-chevron-left shaded"></i>');
    $('.dataTables_paginate > a:last-child').append('<i class="icon-chevron-right shaded"></i>');
});


    $(document).ready(function () {
        $('.datatable-1').dataTable();
    });
</script>

<

</body>
</html>
