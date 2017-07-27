<?php
session_start();
if(empty($_SESSION['id']) || empty($_SESSION['type']) || $_SESSION['type'] == 1) {
	header('Location: index.php');
}
else
{
	include("lib/tokens.php");
    // Copyright 2013 oXopass Micropaiement
    $oxodid = "305";
    $oxofrm = "http://www.anhackin.net/wallet.php";
    $oxoapi = file_get_contents('https://secure.oxopass.com/check-access.php?ip='.$_SERVER['REMOTE_ADDR'].'&did='.$oxodid.'');
    if($oxoapi != 'autorise') {
        header('Location: '.$oxofrm.'');
        die();
    }
	else {
		// Code valide, action à executay
		addFundsOf($_SESSION['id'],140);
		$datax = base64_encode("oxopass");
		header('Location: mpayvalid.php?data='.$datax);
	}
}
        ?>