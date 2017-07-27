<?php
	require_once('includes/db.php');
	
	$bank_key = '540f4c49433e7439';
	
	function getFundsOf($user_id) {
		global $db;
		$query = $db->prepare('SELECT * FROM funds WHERE user_id = ?');
		$query->execute(array($user_id));
		if($data = $query->fetch()) {
			return floor(aes_decrypt($data['amount'], generateKeyFrom($data['key']), $data['key']));
		}
	}
	
	function setFundsOf($user_id, $amount) {
		global $db;
		$query = $db->prepare('SELECT * FROM funds WHERE user_id = ?');
		$query->execute(array($user_id));
		if($data = $query->fetch()) {
			$db->exec('UPDATE funds SET amount = "'.aes_encrypt($amount, generateKeyFrom($data['key']), $data['key']).'" WHERE user_id = "'.$user_id.'"');
		}
	}
	
	function addFundsOf($user_id, $amount) {
		setFundsOf($user_id, getFundsOf($user_id)+$amount);
	}
	
	function removeFundsOf($user_id, $amount) {
		setFundsOf($user_id, getFundsOf($user_id)-$amount);
	}
	
	function initFundsOf($user_id) {
		global $db;
		$keys = generateNewKeys();
		$db->exec('INSERT INTO funds VALUES("'.$user_id.'","'.aes_encrypt('0', $keys[0], $keys[1]).'","'.$keys[1].'")');
	}
	
	function generateNewKeys() {
		global $bank_key;
		$key = '';
		$user_key = substr(md5(uniqid()), 0, 16);
		for($i = 0; $i<16; $i++) {
			$key .= $bank_key[$i].$user_key[$i];
		}
		return array($key, $user_key);
	}
	
	function generateKeyFrom($user_key) {
		global $bank_key;
		$key = '';
		for($i = 0; $i<16; $i++) {
			$key .= $bank_key[$i].$user_key[$i];
		}
		return $key;
	}
	
	function aes_encrypt($decrypted, $key, $iv) {
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
		mcrypt_generic_init($td, $key, $iv);
		$encrypted = base64_encode(mcrypt_generic($td, $decrypted));
		mcrypt_generic_deinit($td);
		return $encrypted;
	}
	
	function aes_decrypt($encrypted, $key, $iv) {
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
		mcrypt_generic_init($td, $key, $iv);
		$decrypted = mdecrypt_generic($td, base64_decode($encrypted));
		mcrypt_generic_deinit($td);
		return $decrypted;
	}
?>
