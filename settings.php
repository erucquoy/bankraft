<?php
	session_start();
	if(empty($_SESSION['id']) || empty($_SESSION['type'])) {
		header('Location: index.php');
	}
	else {
		include('includes/header.php');
		?>
		<h1>Settings</h1>
		
		<!-- SECURITY -->
		<fieldset id="settings_security">
			<legend>Security</legend>
			<input type="password" name="password1" placeholder="New password"/>
			<input type="password" name="password2" placeholder="New password confirmation"/>
			<input type="password" name="pin" placeholder="New security pin"/>
		</fieldset>
		
		<!-- Informations -->
		<fieldset id="settings_email">
			<legend>Informations</legend>
			<input type="text" name="pseudo" placeholder="New pseudo"/>
			<input type="text" name="email1" placeholder="New email"/>
			<input type="text" name="email2" placeholder="New email confirmation"/>
		</fieldset>
		
		<?php
			if($_SESSION['type'] == 2) {
		?>
			<!-- DOMAIN -->
			<fieldset>
				<legend>Domain</legend>
				<a href="settings.php?buydomain=1">Click here to buy a subdomain(<?php echo $_SESSION['pseudo'];?>.bankraft.com) for 350 tokens.</a>
			</fieldset>
		
			<!-- VARIABLES -->
			<fieldset>
				<legend>Variables</legend>
			</fieldset>
		<?php
			}
		?>
		
		<!-- Transaction history -->
		<fieldset>
			<legend>Transaction history</legend>
		</fieldset>
		<?php
		include('includes/footer.php');
	}
?>
