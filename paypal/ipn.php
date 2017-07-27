<?php
session_start();
if(empty($_SESSION['id']) || empty($_SESSION['type']) || $_SESSION['type'] == 1) {
	header('Location: index.php');
}
else
{
	header('Content-type: text/html; charset=utf-8');
	
	//CONFIG
	
	$email_paypal = "contact.bankraft@gmail.com";
	
	include('lib/tokens.php');
	
	//CONFIG
	
	$req = 'cmd=_notify-validate';

	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}

	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
	
	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number']; // id commande
	$payment_status = $_POST['payment_status']; // Completed,
	$payment_amount = $_POST['mc_gross']; //0.01
	$payment_currency = $_POST['mc_currency']; //EUR
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['receiver_email'];//
	$payer_email = $_POST['payer_email'];
	parse_str($_POST['custom'], $custom);
	
if (!$fp) 
{
} 
else 
{
	fputs ($fp, $header . $req);
	while (!feof($fp)) 
	{

		$res = fgets ($fp, 1024);

		if (strcmp ($res, "VERIFIED") == 0) 
		{
		if($payment_currency == "EUR")
		{
			if ($payment_status == "Completed")
			{
				if($email_paypal == $receiver_email)
				{

					$pseudo = $custom['pseudo'];
					$datas = serialize($_POST);
					$informations = $_POST['address_name'] . ' - '. $_POST['payer_email'];

					$req = $bdd->prepare('INSERT INTO paypal(pseudo, offre, datas, transaction_number, informations) VALUES(:pseudo, :offre, :datas, :transaction_number, :informations)');
					$req -> bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
					$req -> bindParam(':offre', $item_name);
					$req -> bindParam(':datas', $datas);
					$req -> bindParam(':transaction_number', $txn_id);
					$req -> bindParam(':informations', $informations);
					$req -> execute();
					
					$prix_net = floor(($prix_total-0.35)/1.034);
					$tokens = $prix_net*100;
					
					addFundsOf($_SESSION['id'],$tokens);
					

				}
			    
			}
		}
		}
		else if (strcmp ($res, "INVALID") == 0) 
		{

		}
	}
	fclose ($fp);
}
}
?>