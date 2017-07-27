<?php
	require_once('includes/db.php');
	if(!empty($_GET['article']) && strlen($_GET['article']) == 27) {
		$query = $db->prepare('SELECT * FROM users WHERE id = ?');
		$query->execute(array(substr($_GET['article'], 0, 13)));
		if($data = $query->fetch()) {
			$query = $db->prepare('SELECT * FROM articles WHERE article_id = ?');
			$query->execute(array(substr($_GET['article'], -13)));
			if($data = $query->fetch()) {
				header('Content-Type: image/png');
				readfile('/var/pictures/'.$_GET['article'].'.png');
			}
		}
	}
?>
