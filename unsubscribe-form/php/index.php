<?php
require "vendor/autoload.php";

use Intercom\IntercomClient;

// enter your configuration here
$APP_ID = "YOUR_APP_ID";
$TOKEN = "YOUR_ACCESS_TOKEN";
$user_id = "USER_ID_OF_CURRENTLY_LOGGED_IN_USER";

$msg = "";
$error = "";

$client = new IntercomClient($TOKEN,null);

if(isset($_POST) && !empty($_POST)){
	$mode = (isset($_POST['mode']) && !empty($_POST['mode']) ? $_POST['mode'] : $MODE);
	try
	{
		if($mode == "subscribe"){
			$user = $client->users->create([
				"user_id" => $user_id,
				"custom_attributes" => [
					"newsletter1" => (@$_POST["newsletter1"] == "on"),
					"newsletter2" => (@$_POST["newsletter2"] == "on")
				]
			]);
			$msg = "Subscription updated!";
		}
		elseif($mode == "unsubscribe_all"){
			$user = $client->users->create([
				"user_id" => $user_id,
				"unsubscribed_from_emails" => true
			]);
			$msg = "Unsubscribed from emails";
		}
		elseif($mode == "resubscribe"){
			$user = $client->users->create([
				"user_id" => $user_id,
				"unsubscribed_from_emails" => false
			]);
			$msg = "Resubscribed!";
		}
	}
	catch(Exception $e){
		$error = "Unknown error: <pre>" . $e->getMessage() . "</pre>";
	}
}
else{
	$user = $client->users->getUsers([
		"user_id" => $user_id
	]);
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Unsubscribe page</title>
</head>
<body>
	<h1>Unsubscribe page using PHP</h1>


<div style="border: 1px solid black; padding: 5px; color: blue">
	<h3>Debug section:</h3>
	<PRE>
  Subscribed to newsletter 1: <?php echo ($user->custom_attributes->newsletter1 ? "true" : "false") ?>

  Subscribed to newsletter 2: <?php echo ($user->custom_attributes->newsletter2 ? "true" : "false") ?>

  Unsubscribed from emails: <?php echo ($user->unsubscribed_from_emails ? "true" : "false") ?>
	</PRE>

</div>


	<?php
	if($error){
		echo "<h3 style='color: red; font-weight: bold'>Not updated: <br/>$error</h3>";
	}
	if($msg){
		echo "<h3 style='color: green; font-weight: bold'>$msg</h3>";
	}
	?>


	<h3>Select which newsletters you would like to receive</h3>
	<div>This should be prepopulated with the current user's subscription information</div>
	<form action="index.php" method="POST">		
		<input type="hidden" name="mode" value="subscribe">
		<div><label><input type="checkbox" name="newsletter1" <?php echo ($user->custom_attributes->newsletter1 ? "checked" : "") ?> >Newsletter one</label></div>
		<div><label><input type="checkbox" name="newsletter2" <?php echo ($user->custom_attributes->newsletter2 ? "checked" : "") ?> >Newsletter two</label></div>
		<input id="unsubscribe" type="submit" value="Unsubscribe"></button>
	</form>

	<br>
	<h3>Unsubscribe from all newsletter/bulk emails</h3>
	<form action="index.php" method="POST">
		<input type="hidden" name="mode" value="unsubscribe_all">
		<input id="unsubscribe_all" type="submit" value="Unsubscribe from all"></button>
	</form>

	<br>
	<h3>Opt into receiving emails</h3>
	<form action="index.php" method="POST">
		<input type="hidden" name="mode" value="resubscribe">
		<div><label><input type="checkbox" id="optin">Opt me into receiving email messages</label></div>
		<input id="resubscribe" type="submit" value="Resubscribe to receiving"></button>
	</form>

	<script type="text/javascript">
		document.getElementById("unsubscribe_all").addEventListener("click", function(e){
			if(confirm("Are you sure you want to unsubcribe from all emails?")){
			}
			else{
				e.preventDefault();
			}
		});
		document.getElementById("resubscribe").addEventListener("click", function(e){
			if(document.getElementById("optin").checked){
			}
			else{
				alert("Cannot resubscribe you unless you opt-in");
				e.preventDefault();
			}
		});


	(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;
	s.src='https://widget.intercom.io/widget/'+APP_ID;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()
	</script>
</body>
</html>
