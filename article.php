<?php
	session_start();
	if(empty($_SESSION['id']) || empty($_SESSION['type']) || $_SESSION['type'] == 1) {
		header('Location: index.php');
	}
	else {
		include('includes/header.php');
		
		if(	!empty($_POST['title']) ||
			!empty($_POST['description']) ||
			//PRICE
			!empty($_POST['price']) ||
			//COMANDS
			!empty($_POST['server']) ||
			!empty($_POST['initialization_cmd'])
		) {
			if( 	!empty($_POST['title']) &&
				!empty($_POST['description']) &&
				//PRICE
				!empty($_POST['price']) &&
				//COMANDS
				!empty($_POST['server']) &&
				!empty($_POST['initialization_cmd'])
			) {
				$treatment = true;
			}
			else {
				$error = 'Fill all fields !';
			}
		}
		
		if($treatment) {
			/* CHECK REQUIRED FIELDS */
			if(strlen($_POST['title']) > 50) {
				$error = 'Title too long (50 max) !';
			}
			if(strlen($_POST['description']) > 10000) {
				$error = 'Description too long (10.000 max) !';
			}
			if(!is_numeric($_POST['price']) || $_POST['price'] < 0 || $_POST['price'] > 1000000) {
				$error = 'Bad price !';
			}
			$query = $db->prepare('SELECT * FROM servers WHERE user_id = ? AND server_id = ?');
			$query->execute(array($_SESSION['id'], $_POST['server']));
			if(!$data = $query->fetch()) {
				$error = 'Bad server !';
			}
			
			if(!empty($_GET['edit'])) {
				$query = $db->prepare('SELECT * FROM articles WHERE user_id = ? AND article_id = ?');
				$query->execute(array($_SESSION['id'], $_GET['edit']));
				if(!$data = $query->fetch()) {
					$error = 'Bad article ID !';
				}
			}
			
			if(empty($error)) {
				if(!empty($_GET['edit'])) {
					$id = $_GET['edit'];
					$mysql = 'UPDATE articles SET title = :title, description = :description, price = :price WHERE article_id = :article';
					$vars = array(
						'title' => $_POST['title'],
						'description' => $_POST['description'],
						'price' => $_POST['price'],
						'article' => $id
					);
				}
				else {
					$id = uniqid();
					$mysql = 'INSERT INTO articles(article_id, user_id, title, description, price, expiration, required) VALUES(:article, :user, :title, :description, :price, :expiration, :required)';
					$vars = array(
						'article' => $id,
						'user' => $_SESSION['id'],
						'title' => $_POST['title'],
						'description' => $_POST['description'],
						'price' => $_POST['price'],
						'expiration' => "0",
						'required' => "0"
					);
				}
				
				$query = $db->prepare($mysql);
				$query->execute($vars);
				
				/* |||||||||||||||||||||||||||||||||||||||||||||||||| */
				/* ||||||||||||||||||| CHECK DATABASE ||||||||||||||| */
				/* |||||||||||||||||||||||||||||||||||||||||||||||||| */
				
				$check = array();
				$check['subscription'] = false;
				$check['trial'] = false;
				$check['hide'] = false;
				$check['blockamount'] = false;
				$check['limit'] = false;
				$check['stock'] = false;
				$check['initialization_cmd'] = false;
				$check['expiration_cmd'] = false;
				$check['renewal_cmd'] = false;
				$check['refund_cmd'] = false;
				
				//OPTIONS
				$query = $db->prepare('SELECT * FROM options WHERE article_id = ?');
				$query->execute(array($id));
				while($data = $query->fetch()) {
					if($data['type'] == 'subscription') {
						$check['subscription'] = true;
					}
					if($data['type'] == 'trial') {
						$check['trial'] = true;
					}
					if($data['type'] == 'hide') {
						$check['hide'] = true;
					}
					if($data['type'] == 'blockamount') {
						$check['blockamount'] = true;
					}
				}
				
				//LIMITS
				$query = $db->prepare('SELECT * FROM limits WHERE article_id = ?');
				$query->execute(array($id));
				while($data = $query->fetch()) {
					if($data['type'] == 1) {
						$check['limit'] = true;
					}
					if($data['type'] == 2) {
						$check['stock'] = true;
					}
				}
				
				//COMMANDS
				$query = $db->prepare('SELECT * FROM commands WHERE article_id = ?');
				$query->execute(array($id));
				while($data = $query->fetch()) {
					if($data['type'] == 1) {
						$check['initialization_cmd'] = true;
					}
					if($data['type'] == 2) {
						$check['expiration_cmd'] = true;
					}
					if($data['type'] == 3) {
						$check['renewal_cmd'] = true;
					}
					if($data['type'] == 4) {
						$check['refund_cmd'] = true;
					}
				}
				
				
				/* |||||||||||||||||||||||||||||||||||||||||||||||||| */
				/* |||||||||||||||||| SAVE TO DATABASE |||||||||||||| */
				/* |||||||||||||||||||||||||||||||||||||||||||||||||| */
				
				//EXPIRATION
				if(!empty($_POST['expiration_number']) && !empty($_POST['expiration_type'])) {
					$t = $_POST['expiration_type'];
					$n = $_POST['expiration_number'];
					if(($t == 'I' || $t == 'H' || $t == 'D' || $t == 'M' || $t == 'Y') && is_numeric($n)) {
						$period = $n.$t;
						if(strlen($period) <= 4) {
							$query = $db->prepare('UPDATE articles SET expiration = ? WHERE article_id = ?');
							$query->execute(array($period, $id));
						}
						else {
							$error = 'Bad time type !';
						}
					}
					else {
						$error = 'Bad time type !';
					}
				}
				else {
					$query = $db->prepare('UPDATE articles SET expiration = ? WHERE article_id = ?');
					$query->execute(array(0, $id));
				}
			
				//REQUIRED
				if(!empty($_POST['needed'])) {
					$query = $db->prepare('SELECT * FROM articles WHERE article_id = ?');
					$query->execute(array($_POST['needed']));
					if($data = $query->fetch()) {
						if($data['article_id'] != $id) {
							$query = $db->prepare('UPDATE articles SET required = ? WHERE article_id = ?');
							$query->execute(array($_POST['needed'], $id));
						}
						else {
							$error = 'Bad required article !';
						}
					}
				}
				else {
					$query = $db->prepare('UPDATE articles SET required = ? WHERE article_id = ?');
					$query->execute(array('0', $id));
				}
			
				//OPTIONS
				if($check['subscription']) {
					if(empty($_POST['subscription'])) {
						$query = $db->prepare('DELETE FROM options WHERE article_id = ? AND type = ?');
						$query->execute(array($id, 'subscription'));
					}
				}
				else {
					if(!empty($_POST['subscription'])) {
						$query = $db->prepare('INSERT INTO options(article_id, type) VALUES(:article_id, :type)');
						$query->execute(array(
							'article_id' => $id,
							'type' => 'subscription'
						));
					}
				}
				if($check['trial']) {
					if(empty($_POST['trial'])) {
						$query = $db->prepare('DELETE FROM options WHERE article_id = ? AND type = ?');
						$query->execute(array($id, 'trial'));
					}
				}
				else {
					if(!empty($_POST['trial'])) {
						$query = $db->prepare('INSERT INTO options(article_id, type) VALUES(:article_id, :type)');
						$query->execute(array(
							'article_id' => $id,
							'type' => 'trial'
						));
					}
				}
				if($check['hide']) {
					if(empty($_POST['hide'])) {
						$query = $db->prepare('DELETE FROM options WHERE article_id = ? AND type = ?');
						$query->execute(array($id, 'hide'));
					}
				}
				else {
					if(!empty($_POST['hide'])) {
						$query = $db->prepare('INSERT INTO options(article_id, type) VALUES(:article_id, :type)');
						$query->execute(array(
							'article_id' => $id,
							'type' => 'hide'
						));
					}
				}
				if($check['blockamount']) {
					if(empty($_POST['blockamount'])) {
						$query = $db->prepare('DELETE FROM options WHERE article_id = ? AND type = ?');
						$query->execute(array($id, 'blockamount'));
					}
				}
				else {
					if(!empty($_POST['blockamount'])) {
						$query = $db->prepare('INSERT INTO options(article_id, type) VALUES(:article_id, :type)');
						$query->execute(array(
							'article_id' => $id,
							'type' => 'blockamount'
						));
					}
				}
			
				//LIMIT
				if(!empty($_POST['limit']) && !empty($_POST['limit_time_number']) && !empty($_POST['limit_time_type'])) {
					$t = $_POST['limit_time_type'];
					$n = $_POST['limit_time_number'];
					$l = $_POST['limit'];
					if(($t == 'I' || $t == 'H' || $t == 'D' || $t == 'M' || $t == 'Y') && is_numeric($n) && is_numeric($l)) {
						$period = $n.$t;
						if(strlen($period) <= 4) {
							if($check['limit']) {
								$query = $db->prepare('UPDATE limits SET amount = ?, period = ? WHERE article_id = ? AND type = ?');
								$query->execute(array($l, $period, $id, 1));
							}
							else {
								$query = $db->prepare('INSERT INTO limits(article_id, type, amount, period) VALUES(:article_id, :type, :amount, :period)');
								$query->execute(array(
									'article_id' => $id,
									'type' => 1,
									'amount' => $l,
									'period' => $period
								));
							}
						}
						else {
							$error = 'Bad time type !';
						}
					}
					else {
						$error = 'Bad time type !';
					}
				}
				else {
					$query = $db->prepare('DELETE FROM limits WHERE article_id = ? AND type = ?');
					$query->execute(array($id, 1));
				}
			
				//STOCK
				if(!empty($_POST['stock']) && !empty($_POST['stock_time_number']) && !empty($_POST['stock_time_type'])) {
					$t = $_POST['stock_time_type'];
					$n = $_POST['stock_time_number'];
					$l = $_POST['stock'];
					if(($t == 'I' || $t == 'H' || $t == 'D' || $t == 'M' || $t == 'Y') && is_numeric($n) && is_numeric($l)) {
						$period = $n.$t;
						if(strlen($period) <= 4) {
							if($check['stock']) {
								$query = $db->prepare('UPDATE limits SET amount = ?, period = ? WHERE article_id = ? AND type = ?');
								$query->execute(array($l, $period, $id, 2));
							}
							else {
								$query = $db->prepare('INSERT INTO limits(article_id, type, amount, period) VALUES(:article_id, :type, :amount, :period)');
								$query->execute(array(
									'article_id' => $id,
									'type' => 2,
									'amount' => $l,
									'period' => $period
								));
							}
						}
						else {
							$error = 'Bad time type !';
						}
					}
					else {
						$error = 'Bad time type !';
					}
				}
				else {
					$query = $db->prepare('DELETE FROM limits WHERE article_id = ? AND type = ?');
					$query->execute(array($id, 2));
				}
			
				//COMMANDS
				if(!empty($_POST['initialization_cmd']) && !empty($_POST['server'])) {
					if($check['initialization_cmd']) {
						$query = $db->prepare('UPDATE commands SET command = ? WHERE article_id = ? AND type = ?');
						$query->execute(array($_POST['initialization_cmd'], $id, 1));
					}
					else {
						$query = $db->prepare('INSERT INTO commands(article_id, server_id, command, type) VALUES(:article_id, :server_id, :command, :type)');
						$query->execute(array(
							'article_id' => $id,
							'server_id' => "",
							'command' => $_POST['initialization_cmd'],
							'type' => 1
						));
					}
				}
				if(!empty($_POST['expiration_cmd']) && !empty($_POST['server'])) {
					if($check['expiration_cmd']) {
						$query = $db->prepare('UPDATE commands SET command = ? WHERE article_id = ? AND type = ?');
						$query->execute(array($_POST['expiration_cmd'], $id, 2));
					}
					else {
						$query = $db->prepare('INSERT INTO commands(article_id, server_id, command, type) VALUES(:article_id, :server_id, :command, :type)');
						$query->execute(array(
							'article_id' => $id,
							'server_id' => "",
							'command' => $_POST['expiration_cmd'],
							'type' => 2
						));
					}
				}
				elseif($check['expiration_cmd']) {
					$query = $db->prepare('DELETE FROM commands WHERE article_id = ? AND type = ?');
					$query->execute(array($id, 2));
				}
				if(!empty($_POST['renewal_cmd']) && !empty($_POST['server'])) {
					if($check['renewal_cmd']) {
						$query = $db->prepare('UPDATE commands SET command = ? WHERE article_id = ? AND type = ?');
						$query->execute(array($_POST['renewal_cmd'], $id, 3));
					}
					else {
						$query = $db->prepare('INSERT INTO commands(article_id, server_id, command, type) VALUES(:article_id, :server_id, :command, :type)');
						$query->execute(array(
							'article_id' => $id,
							'server_id' => "",
							'command' => $_POST['renewal_cmd'],
							'type' => 3
						));
					}
				}
				elseif($check['renewal_cmd']) {
					$query = $db->prepare('DELETE FROM commands WHERE article_id = ? AND type = ?');
					$query->execute(array($id, 3));
				}
				if(!empty($_POST['refund_cmd']) && !empty($_POST['server'])) {
					if($check['refund_cmd']) {
						$query = $db->prepare('UPDATE commands SET command = ? WHERE article_id = ? AND type = ?');
						$query->execute(array($_POST['refund_cmd'], $id, 4));
					}
					else {
						$query = $db->prepare('INSERT INTO commands(article_id, server_id, command, type) VALUES(:article_id, :server_id, :command, :type)');
						$query->execute(array(
							'article_id' => $id,
							'server_id' => "",
							'command' => $_POST['refund_cmd'],
							'type' => 4
						));
					}
				}
				elseif($check['refund_cmd']) {
					$query = $db->prepare('DELETE FROM commands WHERE article_id = ? AND type = ?');
					$query->execute(array($id, 4));
				}
				
				//SERVER
				if(!empty($_POST['server'])) {
					$query = $db->prepare('SELECT * FROM servers WHERE server_id = ?');
					$query->execute(array($_POST['server']));
					if($data = $query->fetch()) {
						$query = $db->prepare('UPDATE commands SET server_id = ? WHERE article_id = ?');
						$query->execute(array($_POST['server'], $id));
					}
				}
				
				//PICTURE
				if(!empty($_FILES['picture']['tmp_name'])) {
					if($_FILES['picture']['error'] == UPLOAD_ERR_OK && !empty($_FILES['picture']['tmp_name'])) {
						if(substr($_FILES['picture']['name'], -3) == 'png') {
							if(!file_exists('/var/pictures/'.$_SESSION['id'].'/')) {
								mkdir('/var/pictures/'.$_SESSION['id'].'/');
							}
							move_uploaded_file($_FILES['picture']['tmp_name'], '/var/pictures/'.$_SESSION['id'].'/'.$id.'.png');
						}
						else {
							$error = 'Bad picture extension !';
						}
					}
					else {
						switch($_FILES['picture']['error']) {
							case 1:
								$error = 'Max size exceeded !';
							break;
							case 2:
								$error = 'Max size exceeded !';
							break;
							case 3:
								$error = 'Upload failed !';
							break;
							case 4:
								$error = 'Bad file !';
							break;
						}
					}
				}
			}
		}
		/* |||||||||||||||||||||||||||||||||||||||||||||||||| */
		/* ||||||||||||||||||| DELETE ARTICLE ||||||||||||||| */
		/* |||||||||||||||||||||||||||||||||||||||||||||||||| */
		
		if(!empty($_GET['del'])) {
			$query = $db->prepare('SELECT * FROM articles WHERE user_id = ? AND article_id = ?');
			$query->execute(array($_SESSION['id'], $_GET['del']));
			if(!$data = $query->fetch()) {
				$error = 'Bad article ID !';
			}
			else {
				$query = $db->prepare('DELETE FROM articles WHERE article_id = ?');
				$query->execute(array($_GET['del']));
				$query = $db->prepare('DELETE FROM limits WHERE article_id = ?');
				$query->execute(array($_GET['del']));
				$query = $db->prepare('DELETE FROM options WHERE article_id = ?');
				$query->execute(array($_GET['del']));
				$query = $db->prepare('DELETE FROM commands WHERE article_id = ?');
				$query->execute(array($_GET['del']));
				unlink($_SESSION['id'].'/'.$_GET['del']);
			}
		}
		
		/* |||||||||||||||||||||||||||||||||||||||||||||||||| */
		/* ||||||||||||||||||| LOAD & DISPLAY ||||||||||||||| */
		/* |||||||||||||||||||||||||||||||||||||||||||||||||| */
		if(!empty($_GET['edit'])) {
			$article = array();
			$article['title'] = '';
			$article['description'] = '';
			$article['expiration_number'] = '';
			$article['expiration_type'] = '';
			$article['price'] = '';
			$article['needed'] = '';
			
			$article['subscription'] = '';
			$article['trial'] = '';
			$article['hide'] = '';
			$article['blockamount'] = '';
			
			$article['limit'] = '';
			$article['limit_time_type'] = '';
			$article['limit_time_number'] = '';
			$article['stock'] = '';
			$article['stock_time_type'] = '';
			$article['stock_time_number'] = '';
			
			$article['server'] = '';
			$article['initialization_cmd'] = '';
			$article['expiration_cmd'] = '';
			$article['renewal_cmd'] = '';
			$article['refund_cmd'] = '';
			
			//INFORMATIONS
			$query = $db->prepare('SELECT * FROM articles WHERE article_id = ?');
			$query->execute(array($_GET['edit']));
			if($data = $query->fetch()) {
				$article['title'] = $data['title'];
				$article['description'] = $data['description'];
				$article['expiration_number'] = substr($data['expiration'], 0, -1);
				$article['expiration_type'] = strtoupper(substr($data['expiration'], -1));
				$article['price'] = $data['price'];
				$article['needed'] = $data['required'];
			}
			
			//OPTIONS
			$query = $db->prepare('SELECT * FROM options WHERE article_id = ?');
			$query->execute(array($_GET['edit']));
			while($data = $query->fetch()) {
				if($data['type'] == 'subscription') {
					$article['subscription'] = true;
				}
				if($data['type'] == 'trial') {
					$article['trial'] = true;
				}
				if($data['type'] == 'hide') {
					$article['hide'] = true;
				}
				if($data['type'] == 'blockamount') {
					$article['blockamount'] = true;
				}
			}
			
			//LIMITS
			$query = $db->prepare('SELECT * FROM limits WHERE article_id = ?');
			$query->execute(array($_GET['edit']));
			while($data = $query->fetch()) {
				if($data['type'] == 1) {
					$article['limit'] = $data['amount'];
					$article['limit_time_type'] = strtoupper(substr($data['period'], -1));
					$article['limit_time_number'] = substr($data['period'], 0, -1);
				}
				if($data['type'] == 2) {
					$article['stock'] = $data['amount'];
					$article['stock_time_type'] = strtoupper(substr($data['period'], -1));
					$article['stock_time_number'] = substr($data['period'], 0, -1);
				}
			}
			
			//COMMANDS
			$query = $db->prepare('SELECT * FROM commands WHERE article_id = ?');
			$query->execute(array($_GET['edit']));
			while($data = $query->fetch()) {
				if($data['type'] == 1) {
					$article['server'] = $data['server_id'];
					$article['initialization_cmd'] = $data['command'];
				}
				if($data['type'] == 2) {
					$article['server'] = $data['server_id'];
					$article['expiration_cmd'] = $data['command'];
				}
				if($data['type'] == 3) {
					$article['server'] = $data['server_id'];
					$article['renewal_cmd'] = $data['command'];
				}
				if($data['type'] == 4) {
					$article['server'] = $data['server_id'];
					$article['refund_cmd'] = $data['command'];
				}
			}
		}
		
		$_GET['edit'] = @htmlspecialchars($_GET['edit']);
		$_GET['del'] = @htmlspecialchars($_GET['del']);
		
		$article['title'] = @htmlspecialchars($article['title']);
		$article['description'] = @htmlspecialchars($article['description']);
		$article['expiration_number'] = @htmlspecialchars($article['expiration_number']);
		$article['expiration_type'] = @htmlspecialchars($article['expiration_type']);
		$article['price'] = @htmlspecialchars($article['price']);
		$article['needed'] = @htmlspecialchars($article['needed']);
		
		$article['subscription'] = @htmlspecialchars($article['subscription']);
		$article['trial'] = @htmlspecialchars($article['trial']);
		$article['hide'] = @htmlspecialchars($article['hide']);
		$article['blockamount'] = @htmlspecialchars($article['blockamount']);
		
		$article['limit'] = @htmlspecialchars($article['limit']);
		$article['limit_time_type'] = @htmlspecialchars($article['limit_time_type']);
		$article['limit_time_number'] = @htmlspecialchars($article['limit_time_number']);
		$article['stock'] = @htmlspecialchars($article['stock']);
		$article['stock_time_type'] = @htmlspecialchars($article['stock_time_type']);
		$article['stock_time_number'] = @htmlspecialchars($article['stock_time_number']);
		
		$article['server'] = @htmlspecialchars($article['server']);
		$article['initialization_cmd'] = @htmlspecialchars($article['initialization_cmd']);
		$article['expiration_cmd'] = @htmlspecialchars($article['expiration_cmd']);
		$article['renewal_cmd'] = @htmlspecialchars($article['renewal_cmd']);
		$article['refund_cmd'] = @htmlspecialchars($article['refund_cmd']);
		
		if((!empty($_POST['save']) || !empty($_GET['del'])) && empty($error)) {
			header('Location: mystore.php');
		}
		?>
		<div id="new_article">
			<h1 align="center">Article configuration</h1>
			<form action="article.php<?php if(!empty($_GET['edit'])) echo '?edit='.$_GET['edit']; ?>" method="post" enctype="multipart/form-data">
				<!-- INFOS -->
				<fieldset id="new_article_info">
					<legend>Details</legend>
					<input type="text" name="title" placeholder="Title" id="new_article_title" class="required" onchange="checkFields(event);" value="<?php echo $article['title']; ?>"/>
					<textarea name="description" placeholder="Description" id="new_article_description" class="required" onchange="checkFields(event);"><?php echo $article['description']; ?></textarea>
					<input type="text" name="expiration_number" placeholder="Expire after..." id="new_article_expiration_number" value="<?php echo $article['expiration_number']; ?>"/>
					<select name="expiration_type" id="new_article_expiration_type">
						<option <?php if($article['expiration_type'] == 'I') echo 'selected';?> value="I">Minutes</option>
						<option <?php if($article['expiration_type'] == 'H') echo 'selected';?> value="H">Hours</option>
						<option <?php if($article['expiration_type'] == 'D') echo 'selected';?> value="D">Days</option>
						<option <?php if($article['expiration_type'] == 'M') echo 'selected';?> value="M">Month</option>
						<option <?php if($article['expiration_type'] == 'Y') echo 'selected';?> value="Y">Years</option>
					</select>
					<input type="text" name="price" placeholder="Price" id="new_article_price" class="required" onchange="checkFields(event);" value="<?php echo $article['price']; ?>"> TKS
					<select name="needed" id="new_article_needed">
						<option selected value="0">Required article for buying this one</option>
						<?php
							$query = $db->prepare('SELECT * FROM articles WHERE user_id = ?');
							$query->execute(array($_SESSION['id']));
							while($data = $query->fetch()) {
								if($data['article_id'] != $_GET['edit']) {
									if($article['needed'] == $data['article_id']) {
										echo '<option selected value="'.$data['article_id'].'">'.$data['title'].' ('.$data['description'].')</option>';
									}
									else {
										echo '<option value="'.$data['article_id'].'">'.$data['title'].' ('.$data['description'].')</option>';
									}
								}
							}
						?>
					</select>
				</fieldset>
				
				<!-- OPTIONS -->
				<fieldset id="new_article_options">
					<legend>Options</legend>
					<input <?php if($article['subscription']) echo 'checked';?> type="checkbox" name="subscription"/>Subscription (Optional)<br />
					<input <?php if($article['trial']) echo 'checked';?> type="checkbox" name="trial"/>Allow users to test one time for 30 minutes the item before buying it<br />
					<input <?php if($article['hide']) echo 'checked';?> type="checkbox" name="hide"/>Hide this product for the moment<br />
					<input <?php if($article['blockamount']) echo 'checked';?> type="checkbox" name="blockamount"/>Disable the quantity option<br />
				</fieldset>
				
				<!-- LIMIT & STOCK -->
				<fieldset id="new_article_limit">
					<legend>User limit</legend>
					<input type="text" name="limit" placeholder="Maximum pcs per user" class="new_article_amount" value="<?php echo $article['limit']; ?>"/> PCS
					<input type="text" name="limit_time_number" placeholder="Expire after..." class="new_article_time" value="<?php echo $article['limit_time_number']; ?>"/>
					<select name="limit_time_type">
						<option <?php if($article['limit_time_type'] == 'I') echo 'selected';?> value="I">Minutes</option>
						<option <?php if($article['limit_time_type'] == 'H') echo 'selected';?> value="H">Hours</option>
						<option <?php if($article['limit_time_type'] == 'D') echo 'selected';?> value="D">Days</option>
						<option <?php if($article['limit_time_type'] == 'M') echo 'selected';?> value="M">Month</option>
						<option <?php if($article['limit_time_type'] == 'Y') echo 'selected';?> value="Y">Years</option>
					</select>
				</fieldset>
				<fieldset id="new_article_stock">
					<legend>Stock</legend>
					<input type="text" name="stock" placeholder="Number of items in stock" class="new_article_amount" value="<?php echo $article['stock']; ?>"/> PCS
					<input type="text" name="stock_time_number" placeholder="Renewal period of the stock" class="new_article_time" value="<?php echo $article['stock_time_number']; ?>"/>
					<select name="stock_time_type">
						<option <?php if($article['stock_time_type'] == 'I') echo 'selected';?> value="I">Minutes</option>
						<option <?php if($article['stock_time_type'] == 'H') echo 'selected';?> value="H">Hours</option>
						<option <?php if($article['stock_time_type'] == 'D') echo 'selected';?> value="D">Days</option>
						<option <?php if($article['stock_time_type'] == 'M') echo 'selected';?> value="M">Month</option>
						<option <?php if($article['stock_time_type'] == 'Y') echo 'selected';?> value="Y">Years</option>
					</select>
				</fieldset>
				
				<!-- COMMANDS -->
				<fieldset id="new_article_comands">
					<legend>Commands</legend>
					<select name="server" id="new_article_server" class="required" onchange="checkFields(event);">
						<option selected value="0">Server related to this article</option>
						<?php
							$query = $db->prepare('SELECT * FROM servers WHERE user_id = ?');
							$query->execute(array($_SESSION['id']));
							while($data = $query->fetch()) {
								if($article['server'] == $data['server_id']) {
									echo '<option selected value="'.$data['server_id'].'">'.$data['name'].' ('.$data['address'].')</option>';
								}
								else {
									echo '<option value="'.$data['server_id'].'">'.$data['name'].' ('.$data['address'].')</option>';
								}
							}
						?>
					</select>
					<textarea name="initialization_cmd" placeholder="Initialization commands (1 line = 1 command)" class="required" onchange="checkFields(event);" id="new_article_init"><?php echo $article['initialization_cmd']; ?></textarea>
					<textarea name="expiration_cmd" placeholder="Expiration commands (1 line = 1 command)"><?php echo $article['expiration_cmd']; ?></textarea>
					<textarea name="renewal_cmd" placeholder="Subscription renewal commands (1 line = 1 command)"><?php echo $article['renewal_cmd']; ?></textarea>
					<textarea name="refund_cmd" placeholder="Refund commands (1 line = 1 command)"><?php echo $article['refund_cmd']; ?></textarea>
				</fieldset>
				
				<!-- PICTURE -->
				<fieldset id="new_article_picture">
					<legend>Picture (PNG only)</legend>
					<input type="file" name="picture"/>
					<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
				</fieldset>
				<?php
					if(!empty($error)) {
						echo '<div class="error" align="center"><br />'.$error.'<br /></div>';
					}
				?>
				<input type="button" value="Click to delete this article" id="new_article_delete" onclick="window.location.href='article.php?del=<?php echo $_GET['edit']; ?>';"/>
				<input type="submit" value="Click to save this article" id="new_article_submit" name="save"/>
			</form>
		</div>
		<script>
			function checkFields(e) {
				if(e.target.value != "") {
					e.target.className="";
				}
				else {
					e.target.className="required";
				}
			}
			
			if(document.getElementById('new_article_title').value != '') {
				document.getElementById('new_article_title').className='';
			}
			if(document.getElementById('new_article_description').value != '') {
				document.getElementById('new_article_description').className='';
			}
			if(document.getElementById('new_article_price').value != '') {
				document.getElementById('new_article_price').className='';
			}
			if(document.getElementById('new_article_server').value != '') {
				document.getElementById('new_article_server').className='';
			}
			if(document.getElementById('new_article_init').value != '') {
				document.getElementById('new_article_init').className='';
			}
		</script>
		<?php
		include('includes/footer.php');
	}
?>
