<?php
	session_start();
	if(empty($_SESSION['id']) || empty($_SESSION['type']) || $_SESSION['type'] == 1) {
		header('Location: index.php');
	}
	else {
		include('includes/db.php');
		include('includes/header.php');
		
		if(!empty($_POST['name']) || !empty($_POST['ip'])) {
			if(!empty($_POST['name']) && !empty($_POST['ip'])) {
			    $_POST['name'] = htmlspecialchars($_POST['name']);
				$_POST['ip'] = htmlspecialchars($_POST['ip']);
				if(strlen($_POST['name']) > 30) {
					$error = 'Server name too long (Max 30) !';
				}
				if(strlen($_POST['ip']) > 50) {
					$error = 'Server address too long (Max 50) !';
				}
				if(empty($error)) {
					$query = $db->prepare('INSERT INTO servers(server_id, user_id, name, address) VALUES(:server, :user, :name, :address)');
					$query->execute(array(
						'user' => $_SESSION['id'],
						'name' => $_POST['name'],
						'address' => $_POST['ip'],
						'server' => str_replace('.', '', uniqid('', true))
					));
				}
			}
			else {
				$error = 'Fill all fields !';
			}
		}
		
		if(!empty($_GET['del'])) {
			$query = $db->prepare('DELETE FROM servers WHERE user_id = ? AND server_id = ?');
			$query->execute(array($_SESSION['id'], $_GET['del']));
		}
		
		echo '<h1 align="center">Server list of '.$_SESSION['pseudo'].'</h1>';
		
		$servers = array();
		$query = $db->prepare('SELECT * FROM servers WHERE user_id = ?');
		$query->execute(array($_SESSION['id']));
		while($data = $query->fetch()) {
			$servers[] = $data;
		}
		
		if(count($servers) > 0) {
			echo '<table id="servers">';
			echo '<tr><th id="servers_name">Name</th><th id="servers_address">Address</th><th id="servers_key">Key</th><th>Autoconf</th><th>Delete</th></tr>';
			
			foreach($servers as $data) {
				echo '<tr><td>'.$data['name'].'</td><td>'.$data['address'].'</td><td>'.$data['server_id'].'</td><td><a href="">Download</a></td><td><a href="servers.php?del='.$data['server_id'].'">Delete</a></td></tr>';
			}
			
			echo '</table>';
		}
		else {
			echo '<div class="error" align="center">No servers yet !</div>';
		}
		
		echo '<h1 align="center">Add new server</h1>';
		echo '<form action="servers.php" method="post">';
		echo '<div id="new_server"><input type="text" name="name" placeholder="Server name (Whatever you want)"/><input type="text" name="ip" placeholder="Server address (IP or domain)"/><input type="submit" value="Save new server !"/></div>';
		if(!empty($error)) {
			echo '<div class="error" align="center"><br />'.$error.'</div>';
		}
		echo '</form>';
		
		
		
		include('includes/footer.php');
	}
?>
