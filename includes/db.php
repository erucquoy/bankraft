<?php

try {
	$db = new PDO('mysql:host=127.0.0.1;dbname=bankraft', 'root', '');
}
catch(Exception $e) {
	die('Error : '.$e->getMessage());
}

?>
