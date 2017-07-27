<?php
session_start();
if(empty($_SESSION['id']) || empty($_SESSION['type'])) {
	header('Location: index.php');
}
else
{
	include('lib/tokens.php');
	include('includes/header.php');
	require_once('includes/db.php');
	?>
	<div align="center">
	<h1>Starpass payments</h1>
	<h4>100 Tokens / code</h4>
	<br />
	<?php
	if(isset($_POST['code1']))
	{
		$datas = $_SESSION['id'];
		// Déclaration des variables
		$ident=$idp=$ids=$idd=$codes=$code1=$code2=$code3=$code4=$code5=$datas='';
		$idp = 153759;
		// $ids n'est plus utilisé, mais il faut conserver la variable pour une question de compatibilité
		$idd = 246694;
		$ident=$idp.";".$ids.";".$idd;
		// On récupère le(s) code(s) sous la forme 'xxxxxxxx;xxxxxxxx'
		if(isset($_POST['code1'])) $code1 = $_POST['code1'];
		if(isset($_POST['code2'])) $code2 = ";".$_POST['code2'];
		if(isset($_POST['code3'])) $code3 = ";".$_POST['code3'];
		if(isset($_POST['code4'])) $code4 = ";".$_POST['code4'];
		if(isset($_POST['code5'])) $code5 = ";".$_POST['code5'];
		$codes=$code1.$code2.$code3.$code4.$code5;
		// On récupère le champ DATAS
		if(isset($_POST['DATAS'])) $datas = $_POST['DATAS'];
		// On encode les trois chaines en URL
		$ident=urlencode($ident);
		$codes=urlencode($codes);
		$datas=urlencode($datas);

		/* Envoi de la requête vers le serveur StarPass
		Dans la variable tab[0] on récupère la réponse du serveur
		Dans la variable tab[1] on récupère l'URL d'accès ou d'erreur suivant la réponse du serveur */
		$get_f=@file( "http://script.starpass.fr/check_php.php?ident=$ident&codes=$codes&DATAS=$datas" );
		if(!$get_f)
		{
		exit( "Votre serveur n'a pas accès au serveur de StarPass, merci de contacter votre hébergeur. " );
		}
		$tab = explode("|",$get_f[0]);

		if(!$tab[1]) $url = "http://script.starpass.fr/error.php";
		else $url = $tab[1];

		// dans $pays on a le pays de l'offre. exemple "fr"
		$pays = $tab[2];
		// dans $palier on a le palier de l'offre. exemple "Plus A"
		$palier = urldecode($tab[3]);
		// dans $id_palier on a l'identifiant de l'offre
		$id_palier = urldecode($tab[4]);
		// dans $type on a le type de l'offre. exemple "sms", "audiotel, "cb", etc.
		$type = urldecode($tab[5]);
		// vous pouvez à tout moment consulter la liste des paliers à l'adresse : http://script.starpass.fr/palier.php

		// Si $tab[0] ne répond pas "OUI" l'accès est refusé
		// On redirige sur l'URL d'erreur
		if( substr($tab[0],0,3) != "OUI" )
		{
			   echo '<h1 style="color:darkred">Payment denied.</h1>';
		}
		else
		{
				$nbtokens = 100;
				
				if($pays == "France")
				{
					if($palier == "Gold A") { $nbtokens = 125; }
					elseif($palier == "Plus") { $nbtokens = 110; }
					elseif($palier == "Gold") { $nbtokens = 115; }
					else { $nbtokens = 100; }
				}
				elseif($pays == "Belgique")
				{
					if($palier == "Plus A") { $nbtokens = 160; }
					else { $nbtokens = 100; }
				}
				elseif($pays == "Royaume-Uni")
				{
					if($palier == "Gold") { $nbtokens = 140; }
					else { $nbtokens = 100; }
				}
				elseif($pays == "Canada")
				{
					if($palier == "Silver") { $nbtokens = 145; }
					elseif($palier == "Silver Mobile") { $nbtokens = 120; }
					else { $nbtokens = 100; }
				}
				elseif($pays == "France DOM")
				{
					if($type == "Audiotel") { $nbtokens = 150; }
				}
				elseif($pays == "Grèce")
				{
					if($palier == "Gold") { $nbtokens = 180; }
				}
				
				else { $nbtokens = 100; }
				
				echo '<h1 style="color:darkgreen">Payment accepted.</h1>';
				addFundsOf($_SESSION['id'],$nbtokens);
				echo 'Now you have <b>'.getFundsOf($_SESSION['id']).'</b> Tokens !';
				
				// user id / code / pays / palier / type / id palier / datas / nb tokens
				$req = $db->prepare('INSERT INTO logs_starpass VALUES("",?,?,?,?,?,?,?,?)');
				$req->execute(array($_SESSION['id'],$code1,$pays,$palier,$type,$id_palier,$datas,$nbtokens));
				

			   // echo "idd : $idd / codes : $codes / datas : $datas / pays : $pays / palier : $palier / id_palier : $id_palier / type : $type";
		}
	}

	else
	{
		?><div id="starpass_246694"></div><script type="text/javascript" src="http://script.starpass.fr/script.php?idd=246694&amp;verif_en_php=1&amp;datas="></script><noscript>Veuillez activer le Javascript de votre navigateur s'il vous pla&icirc;t.<br /><a href="http://www.starpass.fr/">Micro Paiement StarPass</a></noscript><?php
	}
		
?>





<br />
</div>

<?php
	include('includes/footer.php');
}
?>