<!DOCTYPE HTML>
<html>

<head>
	<meta charset="utf-8"/>
	<meta name="description" content="Buy and sell easily on a lot of Minecraft servers! Developed by Quentin Malghem & Edouard Rucquoy."/>
	<title>Bankraft<?php if(!empty($_SESSION['pseudo'])) echo ' - '.$_SESSION['pseudo']; ?></title>
	<link rel="stylesheet" href="css/main.css"/>
	<link rel="stylesheet" href="css/article.css"/>
	<link rel="stylesheet" href="css/servers.css"/>
	<link rel="stylesheet" href="css/mystore.css"/>
	<link rel="stylesheet" href="css/wallet.css"/>
	<link rel="stylesheet" href="css/settings.css"/>
</head>

<body>

<header>
	<div class="center">
		<a href="index.php"><img src="img/logo.png" alt="Logo" id="logo"/></a>
		<div class="right">
			<?php
				require_once('lib/tokens.php');
				if(!empty($_SESSION['id']) && $_SESSION['id'] != '') {
					echo '<input type="button" value="Wallet ('.getFundsOf($_SESSION['id']).' TKS)" onclick="window.location.href=\'wallet.php\';">';
					if($_SESSION['type'] == 2) {
						echo '<input type="button" value="My store" onclick="window.location.href=\'mystore.php\';">';
						echo '<input type="button" value="My servers" onclick="window.location.href=\'servers.php\';">';
					}
					echo '<input type="button" value="Settings" onclick="window.location.href=\'settings.php\';">';
					echo '<input type="button" value="Sign out" onclick="window.location.href=\'signout.php\';">';
				}
				else {
					echo '<form action="index.php" method="post">';
					echo '<input type="text" name="email" placeholder="E-mail or pseudo"/>';
					echo '<input type="password" name="password" placeholder="Password"/>';
					echo '<input type="submit" value="SIGN IN"/> or <input type="button" value="SIGN UP" onclick="window.location.href=\'signup.php\';">';
					echo '</form>';
				}
			?>
		</div>
	</div>
</header>
