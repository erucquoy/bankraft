<?php
	session_start();
	if(empty($_SESSION['id']) || empty($_SESSION['type']) || $_SESSION['type'] == 1) {
		header('Location: index.php');
	}
	else {
		include('includes/header.php');
		echo '<div id="store_header"><h1>Store of '.$_SESSION['pseudo'].'</h1><input type="button" value="New article" class="right" onclick="window.location.href=\'article.php\';"/></div>';
		
		echo '<table id="store">';
		echo '<tr><th>Picture</th><th>Title</th><th>Description</th><th>Duration</th><th>Price</th><th>Edit</th></tr>';
		$query = $db->prepare('SELECT * FROM articles WHERE user_id = ?');
		$query->execute(array($_SESSION['id']));
		while($data = $query->fetch()) {
			$noempty = true;
			$period = substr($data['expiration'], -1);
			switch($period) {
				case 'I':
					$period = 'minutes';
				break;
				case 'H':
					$period = 'hours';
				break;
				case 'D':
					$period = 'days';
				break;
				case 'M':
					$period = 'month';
				break;
				case 'Y':
					$period = 'years';
				break;
			}
			
			echo '<tr><td class="store_picture"><img src="picture.php?article='.$data['user_id'].'/'.$data['article_id'].'" alt="No picture"/></td><td>'.$data['title'].'</td><td>'.$data['description'].'</td><td class="store_duration">'.substr($data['expiration'], 0, -1).' '.$period.'</td><td class="store_price">'.$data['price'].' TKS</td><td><a href="article.php?edit='.$data['article_id'].'">Edit</a></td></tr>';
		}
		echo '</table>';
		
		if(empty($noempty)) {
			echo '<div id="no_products">You do not have any products in your store.</div>';
		}
		
		include('includes/footer.php');
	}
?>
