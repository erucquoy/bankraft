<?php
	session_start();
	
	if(!empty($_POST['email']) && !empty($_POST['password'])) {
		include('includes/db.php');
		if(strpos($_POST['email'], '@') !== false) {
			$query = $db->prepare('SELECT * FROM users WHERE email = ? AND password = ?');
		}
		else {
			$query = $db->prepare('SELECT * FROM users WHERE pseudo = ? AND password = ?');
		}
		$query->execute(array(
			strtolower(filter_var($_POST['email'], FILTER_SANITIZE_MAGIC_QUOTES)),
			sha1($_POST['password'])
		));
		if($data = $query->fetch()) {
			$db->exec('UPDATE users SET lastsignin = now(), ip = "'.$_SERVER['REMOTE_ADDR'].'" WHERE id = "'.$data['id'].'"');
			$_SESSION['id'] = $data['id'];
			$_SESSION['type'] = $data['type'];
			$_SESSION['email'] = $data['email'];
			$_SESSION['pseudo'] = $data['pseudo'];
		}
	}
	
	include('includes/header.php');
?>
<h1 id="slogan">Bankraft - Buy and sell easily on Minecraft!</h1>
<p id="intro">Players can now have a <b>single wallet for all their servers</b>! For holders of servers, the plugin is <b>free and requires no subscription</b>! Installation is very easy.<br/> Just <a href="signup.php">sign up</a> and install our Bukkit plugin!</p>
<img src="img/demo.png" alt="Demo" id="demo">
<?php
	include('includes/footer.php');
?>
