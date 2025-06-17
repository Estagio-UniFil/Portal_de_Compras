<?php
session_start();
include('includes/config.php'); // certifica que $con está definido corretamente

$condition = "1=1";
$params = [];
$types = "";

// Busca de produto
if (isset($_POST['product']) && !empty($_POST['product'])) {
    $search = $_POST['product'];
    $like = "%$search%";

    // 1) Verificar se existe produto com nome exatamente igual ao buscado
    $stmtProd = $con->prepare("SELECT id FROM products WHERE productName = ?");
    $stmtProd->bind_param("s", $search);
    $stmtProd->execute();
    $resultProd = $stmtProd->get_result();

    if ($resultProd->num_rows == 1) {
        $row = $resultProd->fetch_assoc();
        header("Location: product-details.php?pid=" . $row['id']);
        exit();
    }

    // 2) Verificar subcategoria
    $stmtSub = $con->prepare("SELECT id FROM subcategory WHERE subcategory LIKE ?");
    $stmtSub->bind_param("s", $like);
    $stmtSub->execute();
    $resultSub = $stmtSub->get_result();

    if ($resultSub->num_rows == 1) {
        $row = $resultSub->fetch_assoc();
        header("Location: sub-category.php?scid=" . $row['id']);
        exit();
    }

    // 3) Verificar categoria
    $stmtCat = $con->prepare("SELECT id FROM category WHERE categoryName LIKE ?");
    $stmtCat->bind_param("s", $like);
    $stmtCat->execute();
    $resultCat = $stmtCat->get_result();

    if ($resultCat->num_rows == 1) {
        $row = $resultCat->fetch_assoc();
        header("Location: category.php?cid=" . $row['id']);
        exit();
    }

    // 4) Se não redirecionar, filtra produtos pelo nome (LIKE)
    $condition .= " AND products.productName LIKE ?";
    $params[] = $like;
    $types .= "s";
}

if (isset($_POST['category']) && !empty($_POST['category'])) {
    $category = "%{$_POST['category']}%";
    $condition .= " AND category.categoryName LIKE ?";
    $params[] = $category;
    $types .= "s";
}

if (isset($_POST['subcategory']) && !empty($_POST['subcategory'])) {
    $subcategory = "%{$_POST['subcategory']}%";
    $condition .= " AND subcategory.subcategory LIKE ?";
    $params[] = $subcategory;
    $types .= "s";
}

$query = "
    SELECT products.* 
    FROM products 
    JOIN category ON products.category = category.id 
    JOIN subcategory ON products.subCategory = subcategory.id
    WHERE $condition
";

$stmt = $con->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$ret = $stmt->get_result();
$num = $ret->num_rows;

// ** ABRE DIV ROW PARA AGRUPAR OS PRODUTOS **
echo '<div class="row">';

if ($num === 1) {
    $row = $ret->fetch_assoc();
    header("Location: product-details.php?pid=" . $row['id']);
    exit();
}

if ($num > 0) {
    while ($row = $ret->fetch_assoc()) {
        ?>
        <div class="col-sm-6 col-md-4 wow fadeInUp">
            <div class="products">
                <div class="product">
                    <div class="product-image">
                        <div class="image">
                            <a href="product-details.php?pid=<?php echo htmlentities($row['id']); ?>">
                                <img src="admin/productimages/<?php echo htmlentities($row['id']); ?>/<?php echo htmlentities($row['productImage1']); ?>" alt="<?php echo htmlentities($row['productName']); ?>" width="200" height="300">
                            </a>
                        </div>
                    </div>
                    <div class="product-info text-left">
                        <h3 class="name">
                            <a href="product-details.php?pid=<?php echo htmlentities($row['id']); ?>">
                                <?php echo htmlentities($row['productName']); ?>
                            </a>
                        </h3>
                        <div class="rating rateit-small"></div>
                        <div class="description"></div>
                        <div class="product-price">
                            <span class="price">Rs. <?php echo htmlentities($row['productPrice']); ?></span>
                            <span class="price-before-discount">Rs.<?php echo htmlentities($row['productPriceBeforeDiscount']); ?></span>
                        </div>
                    </div>
                    <div class="cart clearfix animate-effect">
                        <div class="action">
                            <ul class="list-unstyled">
                                <li class="add-cart-button btn-group">
                                    <?php if ($row['productAvailability'] == 'In Stock') { ?>
                                        <a href="category.php?page=product&action=add&id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                            <i class="fa fa-shopping-cart"></i> Adicionar ao Carrinho
                                        </a>
                                    <?php } else { ?>
                                        <div style="color:red">Fora de Estoque</div>
                                    <?php } ?>
                                </li>
                                <li class="lnk wishlist">
                                    <a href="category.php?pid=<?php echo htmlentities($row['id']); ?>&action=wishlist" title="Wishlist">
                                        <i class="icon fa fa-heart"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    ?>
    <div class="col-sm-6 col-md-4 wow fadeInUp">
        <h3>Nenhum produto encontrado</h3>
    </div>
    <?php
}

// ** FECHA DIV ROW **
echo '</div>';
?>

