<?php 
//session_start();
?>

<div class="top-bar animate-dropdown">
	<div class="container">
		<div class="header-top-inner">
			<div class="cnt-account">
				<ul class="list-unstyled">

<?php if(strlen($_SESSION['login']))
    {   ?>
				<li style="list-style: none;">
  <span style="color: #000; text-decoration: none; cursor: default;">
    <i class="icon fa fa-user"></i>
    <strong>Bem Vindo:</strong> <?php echo htmlentities($_SESSION['username']); ?>
  </span>
</li>

				<?php } ?>

					<li><a href="my-account.php"><i class="icon fa fa-user"></i>Minha Conta</a></li>
					<li><a href="my-wishlist.php"><i class="icon fa fa-heart"></i>Lista de Desejos</a></li>
					<li><a href="my-cart.php"><i class="icon fa fa-shopping-cart"></i>Meu Carrinho</a></li>
					<?php if(strlen($_SESSION['login'])==0)
    {   ?>
<li><a href="login.php"><i class="icon fa fa-sign-in"></i>Login</a></li>
<?php }
else{ ?>
	
				<li><a href="logout.php"><i class="icon fa fa-sign-out"></i>Logout</a></li>
				<?php } ?>	
				</ul>
			</div><!-- /.cnt-account -->

<div class="cnt-block">
				<ul class="list-unstyled list-inline">
					<li class="dropdown dropdown-small">
						<a href="track-orders.php" class="dropdown-toggle" ><span class="key">Acompanhar Pedido</b></a>
						
					</li>

				
				</ul>
			</div>

			
			
			<div class="clearfix"></div>
		</div><!-- /.header-top-inner -->
	</div><!-- /.container -->
</div><!-- /.header-top -->