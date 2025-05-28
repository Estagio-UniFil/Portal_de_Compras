<?php
session_start();
include('include/config.php');
error_reporting(0);

if(strlen($_SESSION['alogin'])==0) {	
    header('location:index.php');
    exit();
}

if(isset($_POST['submit'])) {
    $category = mysqli_real_escape_string($con, trim($_POST['category']));
    $description = mysqli_real_escape_string($con, trim($_POST['description']));
    $createdBy = intval($_SESSION['aid']); // seu ID de usuário

    if($category == "" || $description == "") {
        $_SESSION['msg'] = "Todos os campos são obrigatórios.";
    } else {
        $query = "INSERT INTO category (categoryName, categoryDescription, createdBy) VALUES ('$category', '$description', '$createdBy')";
        if(mysqli_query($con, $query)) {
            $_SESSION['msg'] = "Categoria criada com sucesso!";
        } else {
            $_SESSION['msg'] = "Erro ao criar categoria.";
        }
    }
}

if(isset($_GET['del'])) {
    $id = intval($_GET['id']);
    mysqli_query($con, "DELETE FROM category WHERE id = '$id'");
    $_SESSION['delmsg'] = "Categoria deletada!";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | Categoria</title>
    <link type="text/css" href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link type="text/css" href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link type="text/css" href="css/theme.css" rel="stylesheet">
    <link type="text/css" href="images/icons/css/font-awesome.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600' rel='stylesheet'>
</head>
<body>
<?php include('include/header.php');?>

<div class="wrapper">
    <div class="container">
        <div class="row">
            <?php include('include/sidebar.php');?>				
            <div class="span9">
                <div class="content">

                    <div class="module">
                        <div class="module-head"><h3>Categoria</h3></div>
                        <div class="module-body">

                        <?php if(isset($_SESSION['msg'])) { ?>
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>Sucesso!</strong> <?php echo htmlentities($_SESSION['msg']); unset($_SESSION['msg']); ?>
                            </div>
                        <?php } ?>

                        <?php if(isset($_SESSION['delmsg'])) { ?>
                            <div class="alert alert-warning">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>Atenção!</strong> <?php echo htmlentities($_SESSION['delmsg']); unset($_SESSION['delmsg']); ?>
                            </div>
                        <?php } ?>

                        <form class="form-horizontal row-fluid" method="post">
                            <div class="control-group">
                                <label class="control-label" for="category">Nome da Categoria</label>
                                <div class="controls">
                                    <input type="text" name="category" id="category" placeholder="Digite o nome da categoria" class="span8 tip" required>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label" for="description">Descrição da Categoria</label>
                                <div class="controls">
                                    <textarea name="description" id="description" placeholder="Digite a descrição da categoria" class="span8 tip" required></textarea>
                                </div>
                            </div>

                            <div class="control-group">
                                <div class="controls">
                                    <button type="submit" name="submit" class="btn btn-primary">Criar</button>
                                </div>
                            </div>
                        </form>

                        </div>
                    </div>

                    <div class="module">
                        <div class="module-head"><h3>Lista de Categorias</h3></div>
                        <div class="module-body table">
                            <table cellpadding="0" cellspacing="0" border="0" class="datatable-1 table table-bordered table-striped display" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome da Categoria</th>
                                        <th>Descrição</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = mysqli_query($con, "SELECT * FROM category");
                                    $cnt = 1;
                                    while($row = mysqli_fetch_array($query)) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlentities($cnt); ?></td>
                                        <td><?php echo htmlentities($row['categoryName']); ?></td>
                                        <td><?php echo htmlentities($row['categoryDescription']); ?></td>
                                        <td>
                                            <a href="edit-category.php?id=<?php echo $row['id']; ?>"><i class="icon-edit"></i></a>
                                            <a href="category.php?id=<?php echo $row['id']; ?>&del=delete" onClick="return confirm('Você tem certeza que deseja deletar?')"><i class="icon-remove-sign"></i></a>
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

<?php include('include/footer.php');?>

<script src="scripts/jquery-1.9.1.min.js" type="text/javascript"></script>
<script src="scripts/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
<script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="scripts/flot/jquery.flot.js" type="text/javascript"></script>
<script src="scripts/datatables/jquery.dataTables.js"></script>
<script>
$(document).ready(function() {
    $('.datatable-1').dataTable();
    $('.dataTables_paginate').addClass("btn-group datatable-pagination");
    $('.dataTables_paginate > a').wrapInner('<span />');
    $('.dataTables_paginate > a:first-child').append('<i class="icon-chevron-left shaded"></i>');
    $('.dataTables_paginate > a:last-child').append('<i class="icon-chevron-right shaded"></i>');
});
</script>
</body>
</html>

