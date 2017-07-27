<?php
	session_start();
	if(!empty($_SESSION['id'])) {
		header('Location: index.php');
	}
	
	require_once('lib/recaptchalib.php');
	
	if(	!empty($_POST['email']) || 
		!empty($_POST['pseudo']) || 
		!empty($_POST['password']) || 
		!empty($_POST['passwordrepeat']) || 
		!empty($_POST['pin']) || 
		!empty($_POST['cgu']) || 
		!empty($_POST['usertype']) || 
		!empty($_POST['recaptcha_challenge_field']) || 
		!empty($_POST['recaptcha_response_field'])
	) {
		if(!(	!empty($_POST['email']) && 
			!empty($_POST['pseudo']) && 
			!empty($_POST['password']) && 
			!empty($_POST['passwordrepeat']) && 
			!empty($_POST['pin']) && 
			!empty($_POST['cgu']) && 
			!empty($_POST['usertype']) && 
			!empty($_POST['recaptcha_challenge_field']) && 
			!empty($_POST['recaptcha_response_field']))
		) {
			$error = 'Fill all fields !';
		}
		else {
			$treatment = true;
		}
	}
	
	if(!empty($treatment))
	{
		include('includes/db.php');
		
		$captchakey = '6LcgTPoSAAAAADT2xKHM4zjb4pSNjvdl3UJ9PAGn';
		$captcha = recaptcha_check_answer($captchakey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
		if(!$captcha->is_valid) {
			$error = 'Bad captcha !';
		}
		else {
			$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
			$pseudo = filter_var($_POST['pseudo'], FILTER_SANITIZE_MAGIC_QUOTES);
			$pin = filter_var($_POST['pin'], FILTER_SANITIZE_NUMBER_INT);
		
			if($_POST['password'] != $_POST['passwordrepeat']) {
				$error = 'Passwords do not match !';
			}
			if(strlen($_POST['password']) >= 8) {
				$password = sha1($_POST['password']);
			}
			else {
				$error = 'Password too weak !';
			}
		
			if($email == false || $pseudo == false || $pin == false) {
				$error = 'Bad characters !';
			}
		
			if(strlen($email) > 100) {
				$error = 'E-mail too long!';
			}
			if(strlen($pseudo) > 30) {
				$error = 'Pseudo too long !';
			}
			if(strlen($pin) > 4 || strlen($pin) < 4) {
				$error = 'Bad pin ! (4 numbers)';
			}
		
			if($_POST['usertype'] != 1 && $_POST['usertype'] != 2) {
				$error = 'Bad user type !';
			}
		
			$query = $db->prepare('SELECT * FROM users WHERE email = ? OR pseudo = ?');
			$query->execute(array(strtolower($email), strtolower($pseudo)));
			if($data = $query->fetch()) {
				$error = 'Email or pseudo already in use !';
			}
			
			if(empty($error)) {
				require_once('lib/tokens.php');
				
				$id = uniqid();
				$query = $db->prepare('INSERT INTO users(id, type, email, pseudo, password, signupdate, lastsignin, ip, pin, domain) VALUES(:id, :type, :email, :pseudo, :password, now(), now(), :ip, :pin, :domain)');
				$query->execute(array(
					'id' => $id,
					'type' => $_POST['usertype'],
					'email' => strtolower($email),
					'pseudo' => strtolower($pseudo),
					'password' => $password,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'pin' => $pin,
					'domain' => ''
				));
				initFundsOf($id);
				
				$_SESSION['id'] = $id;
				$_SESSION['type'] = $_POST['usertype'];
				$_SESSION['email'] = strtolower($email);
				$_SESSION['pseudo'] = strtolower($pseudo);
				
				header('Location: index.php');
			}
		}
	}
	
	include('includes/header.php');
?>	
	<div id="playervserver1" class="playervserver left" onclick="playervserver(1);">
		I'm a player !<br/>
		<img src="img/steve.png" alt="player"/>
	</div>
	<div id="playervserver2" class="playervserver right" onclick="playervserver(2);">
		I'm a server owner !<br/><br/><br/><br/>
		<img src="img/server_hd.png" alt="server"/>
	</div>
	<form action="signup.php" method="post" id="signup">
		<h1 style="text-align: center;">Bankraft - Sign up</h1>
		<input type="text" name="email" placeholder="Enter your e-mail"/><br />
		<input type="text" name="pseudo" placeholder="Enter your pseudo"/><br />
		<input type="password" name="password" placeholder="Enter your password"/><br />
		<input type="password" name="passwordrepeat" placeholder="Repeat your password"/><br />
		<input type="password" name="pin" placeholder="Enter a 4 numbers security pin"/><br />
		<?php echo recaptcha_get_html('6LcgTPoSAAAAAI0od1_n4QNLeiM-rDNtgPt4zbV0'); ?>
		<input type="checkbox" name="cgu"/>I accept the terms and conditions.<br />
		<input type="hidden" name="usertype" id="usertype" value="0" /><br />
		<input type="submit" value="Sign up !" id="signupsubmit"/>
		<div class="error"><?php if(!empty($error)) echo $error; ?></div>
	</form>
	
<?php
	include('includes/footer.php');
?>
