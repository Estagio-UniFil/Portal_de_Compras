        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand" href="index.php">Portal de Compras</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="about-us.php">Sobre</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Shop</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="index.php">Todos Produtos</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                <li><a class="dropdown-item" href="shop-categories.php">Categor Wise</a></li>
                            </ul>
                        </li>
<?php if($_SESSION['id']==0){?>
          <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Usuários</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="login.php">Login</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                <li><a class="dropdown-item" href="signup.php">Cadastrar</a></li>
                            </ul>
                        </li>
                                     <li class="nav-item"><a class="nav-link" href="admin/">Admin</a></li>
                    <?php } else {?>
                        <li class="nav-item"><a class="nav-link" href="my-wishlist.php">Minnha Lista de Desejo</a></li>
                                  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Minha Conta</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="my-orders.php">Ordens</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                <li><a class="dropdown-item" href="my-profile.php">Perfil</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                <li><a class="dropdown-item" href="change-password.php">Mudar Seha</a></li>
                                  <li><hr class="dropdown-divider" /></li>
                                <li><a class="dropdown-item" href="manage-addresses.php">Endereços</a></li>
                                  <li><hr class="dropdown-divider" /></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                     <?php } ?>  
                      <li class="nav-item"><a class="nav-link" href="contact-us.php">Contate-nos</a></li> 
        
                    </ul>  
<?php if($_SESSION['id']!=0):?>
<strong>Bem Vindo:</strong> <?php echo $_SESSION['username'];?> &nbsp;
<?php endif;?>
                    <form class="d-flex">


                        <?php 
$uid=$_SESSION['id'];
                        $ret=mysqli_query($con,"select sum(productQty) as qtyy from cart where userId='$uid'");
$result=mysqli_fetch_array($ret);
$cartcount=$result['qtyy'];
                        ?>
                        <a class="btn btn-outline-dark" href="my-cart.php">
                            <i class="bi-cart-fill me-1"></i>
                            Carrinho
                            <?php if($cartcount==0):?>
                            <span class="badge bg-dark text-white ms-1 rounded-pill">0</span>
                        <?php else: ?>
                            <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $cartcount; ?></span>
                            <?php endif;?>
                        </a>
                    </form>

                </div>
            </div>
        </nav>