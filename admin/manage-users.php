<?php
session_start();
include('include/config.php');

if (strlen($_SESSION['alogin']) == 0) {    
    header('location:index.php');
    exit();
}

date_default_timezone_set('America/Sao_Paulo');
$currentTime = date('d-m-Y h:i:s A', time());

// --- Deletar usuário com segurança ---
if (isset($_GET['del']) && isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    $stmt = $con->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $_SESSION['delmsg'] = "Usuário deletado com sucesso!";
        } else {
            $_SESSION['delmsg'] = "Erro ao deletar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['delmsg'] = "Erro na preparação da query: " . $con->error;
    }

    header("Location: manage-users.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <title>Admin | Governar Usuários</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Estilos -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link href="images/icons/css/font-awesome.css" rel="stylesheet">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600" rel="stylesheet">

    <!-- Estilos adicionais para compactar tabela -->
    <style>
        /* Fonte menor e padding reduzido para as células da tabela */
        table.dataTable tbody td, 
        table.dataTable thead th {
            padding: 4px 8px !important;
            font-size: 12px !important;
            white-space: nowrap; /* evita quebra de linha */
            vertical-align: middle !important;
        }

        /* Ajustar largura mínima da tabela para caber o conteúdo */
        table.dataTable {
            width: 100% !important;
            table-layout: auto;
        }

        /* Tornar o container da tabela responsivo com scroll horizontal */
        .dataTables_wrapper {
            overflow-x: auto;
        }

        /* Esconder algumas colunas em telas menores para melhorar visual */
        @media (max-width: 1024px) {
            /* Esconder colunas Endereço de Entrega e Endereço de Cobrança */
            table.dataTable td:nth-child(5),
            table.dataTable th:nth-child(5),
            table.dataTable td:nth-child(6),
            table.dataTable th:nth-child(6) {
                display: none;
            }
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
                            <h3>Governar Usuários</h3>
                        </div>
                        <div class="module-body table">
                            <?php if (isset($_SESSION['delmsg']) && $_SESSION['delmsg'] != ""): ?>
                                <div class="alert alert-danger">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    <strong>Atenção!</strong> <?php echo htmlentities($_SESSION['delmsg']); ?>
                                    <?php $_SESSION['delmsg'] = ""; ?>
                                </div>
                            <?php endif; ?>

                            <br />

                            <table class="datatable-1 table table-bordered table-striped display" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Número de Contato</th>
                                        <th>Endereço de Entrega</th>
                                        <th>Endereço de Cobrança</th>
                                        <th>Data de Registro</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = mysqli_query($con, "SELECT * FROM users");
                                    $cnt = 1;
                                    while ($row = mysqli_fetch_array($query)) {
                                    ?>                                    
                                        <tr>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td><?php echo htmlentities($row['name']); ?></td>
                                            <td><?php echo htmlentities($row['email']); ?></td>
                                            <td><?php echo htmlentities($row['contactno']); ?></td>
                                            <td>
                                                <?php echo htmlentities($row['shippingAddress']) . ", " .
                                                           htmlentities($row['shippingCity']) . ", " .
                                                           htmlentities($row['shippingState']) . " - " .
                                                           htmlentities($row['shippingPincode']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlentities($row['billingAddress']) . ", " .
                                                           htmlentities($row['billingCity']) . ", " .
                                                           htmlentities($row['billingState']) . " - " .
                                                           htmlentities($row['billingPincode']); ?>
                                            </td>
                                            <td><?php echo htmlentities($row['regDate']); ?></td>
                                            <td>
                                                <a href="manage-users.php?id=<?php echo $row['id']; ?>&del=delete" 
                                                   onclick="return confirm('Tem certeza que deseja excluir este usuário?')" 
                                                   class="btn btn-danger btn-sm">
                                                    Excluir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php $cnt++; } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>                        
                </div><!--/.content-->
            </div><!--/.span9-->
        </div>
    </div><!--/.container-->
</div><!--/.wrapper-->

<?php include('include/footer.php'); ?>

<!-- Scripts -->
<script src="scripts/jquery-1.9.1.min.js"></script>
<script src="scripts/jquery-ui-1.10.1.custom.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="scripts/flot/jquery.flot.js"></script>
<script src="scripts/datatables/jquery.dataTables.js"></script>
<script>
    $(document).ready(function () {
        $('.datatable-1').DataTable({
            "pagingType": "simple_numbers", // paginação mais compacta
            "lengthMenu": [5, 10, 20],      // opções para quantidade de linhas por página
            "pageLength": 5,                // padrão 5 linhas por página
            "autoWidth": false,
            "responsive": true,
            "language": {
                "infoFiltered": "(filtrado de _MAX_ registros no total)",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Próximo"
                }
            }
        });

        $('.dataTables_paginate').addClass("btn-group datatable-pagination");
        $('.dataTables_paginate > a').wrapInner('<span />');
        $('.dataTables_paginate > a:first-child').append('<i class="icon-chevron-left shaded"></i>');
        $('.dataTables_paginate > a:last-child').append('<i class="icon-chevron-right shaded"></i>');
    });
</script>

</body>
</html>


