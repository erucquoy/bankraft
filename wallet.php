<?php
session_start();
if(empty($_SESSION['id']) || empty($_SESSION['type'])) {
	header('Location: index.php');
}
else
{
	require_once('lib/tokens.php');
	include('includes/header.php');
?>

<script type="text/javascript">
function change_onglet(name)
{
	document.getElementById('o_'+anc_onglet).className = 'onglet_0 onglet';
	document.getElementById('o_'+name).className = 'onglet_1 onglet';
	document.getElementById('contenu_onglet_'+anc_onglet).style.display = 'none';
	document.getElementById('contenu_onglet_'+name).style.display = 'block';
	anc_onglet = name;
}

function change_itemname()
{
	var tokens = ((document.getElementById('pricetokens').value)-0.35)/1.034;
	tokens = tokens*100;
	document.getElementById('itemname').value="Bankraft - "+tokens+" Tokens";
}
</script>



<h1 align="center">Wallet of <?php echo $_SESSION['pseudo'].' - '.getFundsOf($_SESSION['id']).' tokens'?></h1>
<div class="systeme_onglets">
	<div class="onglets">
		<span class="onglet_0 onglet" id="o_deposit" onclick="javascript:change_onglet('deposit');">Deposit</span>
		<span class="onglet_0 onglet" id="o_withdraw" onclick="javascript:change_onglet('withdraw');">Withdraw</span>
		<span class="onglet_0 onglet" id="o_stats" onclick="javascript:change_onglet('stats');">Stats</span>
	</div>
	<div class="contenu_onglets">
		<div class="contenu_onglet" id="contenu_onglet_deposit" align="center">
			<h1>Deposit</h1>

			<table id="wallet-table">
			<tr>
				<td><img src="img/paypal.png" alt="Paypal" class="wallet-logo"/></td>
				<td><img src="img/starpass.png" alt="Starpass" class="wallet-logo"/></td>
				<td><img src="img/bitcoin.png" alt="Bitcoin" class="wallet-logo"/></td>
			</tr>
			<tr>
				<td>Min 5&euro;/deposit (3,4% + 0.35 fee).</td>
				<td>Micropayments for a lot of country.</td>
				<td>Bitcoin accepted here !</td>
			</tr>
			<tr>
				<td>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="paypal">
		        			<select id="pricetokens" name="amount">
		            				<option selected>Select a offer...</option>
						<?php
							$gtokens = 0;
							for($i = 5;$i<=250;$i=$i+5)
							{
								$prix = $i;
								$fee = ($i*0.034) + 0.35;
								$prix_total = $prix + $fee;
								$tokens = $i*100;
								echo '<option value="'.$prix_total.'">'.$prix.'€ (+'.$fee.'€ paypal fee) for '.$tokens.' Tokens</option>';
							}
						?>
						</select>
						<input name="currency_code" type="hidden" value="EUR">
						<input name="shipping" type="hidden" value="0.00">
						<input name="tax" type="hidden" value="0.00">
						<input name="return" type="hidden" value="http://www.anhackin.net/wallet.php?msg=paypal:true">
						<input name="cancel_return" type="hidden" value="http://www.anhackin.net/wallet.php?msg=paypal:false">
						<input name="notify_url" type="hidden" value="http://www.anhackin.net/paypal/ipn.php">
						<input name="cmd" type="hidden" value="_xclick">
						<input name="business" type="hidden" value="contact.bankraft@gmail.com">
						<input name="item_name" type="hidden" id="itemname" value="Bankraft - ">
						<input name="no_note" type="hidden" value="1">
						<input name="lc" type="hidden" value="FR">
						<input name="bn" type="hidden" value="PP-BuyNowBF">
						<input name="custom" type="hidden" value="id=<?php echo $_SESSION['id']; ?>">
						<input type="submit" value="Continue" name="submit">
		    			</form>
				</td>
				<td><input type="button" value="Continue to Starpass" onclick="window.location.href='starpass.php';"/></td>
				<td><input type="button" value="Continue to Bitcoin" onclick="window.location.href='bitcoin.php';"/></td>
			</tr>	
			</table>
			<h3>1&euro; = 100 Tokens</h3>	        
			
			<br />
			
			
			
			
		</div>
		<?php
		if($_SESSION['type'] == "2")
		{
		?>
		<div class="contenu_onglet" id="contenu_onglet_withdraw" align="center">
			<h1>Withdraw</h1>
				Only server owners can retrieve their money.<br />
			<br />	
		</div>
		<div class="contenu_onglet" id="contenu_onglet_stats" align="center">
		<h1>Stats</h1>
			This is your stats.<br />
			<br />
		</div>
		<?php
		}
		?>
		
	</div>
</div>
<script type="text/javascript">
//<!--
var anc_onglet = 'deposit';
change_onglet(anc_onglet);
//-->
</script>


<?php
	include('includes/footer.php');
}
?>
