<?php
require "vendor/autoload.php";

use Intercom\IntercomClient;

// enter your configuration here
$APP_ID = "YOUR_APP_ID";
$TOKEN = "YOUR_ACCESS_TOKEN"; // https://developers.intercom.com/docs/personal-access-tokens) standard access token needed for "lead" mode in order to list leads

// $mode values: 
// - user
// - lead (will use email provided to find an existing lead and will create one if not found)
// - lead_in_browser (treats the existing browser session as the same lead)
$MODE = "user"; 
// end of configuration

$msg = "";
$error = "";
if(isset($_POST) && !empty($_POST)){
	$mode = (isset($_POST['mode']) && !empty($_POST['mode']) ? $_POST['mode'] : $MODE);
	$body = $_POST['message'];
	$email= $_POST['email'];
	$name = $_POST['name'];
	$visitor_or_lead_user_id = $_POST["visitor_or_lead_user_id"];

	$client = new IntercomClient($TOKEN,null);
	$from = null;
	try
	{
		if($mode == "user"){
			// update existing user
			$user = $client->users->create([
				"name" => $name,
				"email" => $email
			]);
			$from = [ "type" => "user", "id" => $user->id];
		}
		else if($mode == "lead"){
			// create lead if it doesn't exist
			// if multiple leads exist, just pick first one
			$existing_leads = $client->leads->getLeads(["email" => $email]);
			if($existing_leads->total_count == 0){
				$lead = $client->leads->create([
					"name" => $name,
					"email" => $email
				]);
			}
			else{
				$lead = $existing_leads->contacts[0];
				$client->leads->update([
					"name" => $name,
					"id" => $lead->id
				]);
			}
			$from = [ "type" => "contact", "id" => $lead->id];
		}
		else if($mode == "lead_in_browser"){
			$lead = null;

			// check if lead exists, if doesn't, it means they are a visitor and we need to convert them to a lead
			try{
				$lead = $client->leads->getLead("", ["user_id" => $visitor_or_lead_user_id]);
			}
			catch(GuzzleHttp\Exception\ClientException $exception) {
				if ($exception->getResponse()->getStatusCode() == 404){
					// current official PHP library has no visitor endpoint, so make a manual API call
					$headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $TOKEN];
					$clientAPI = new GuzzleHttp\Client(['base_uri' => 'https://api.intercom.io/', 'headers' => $headers, 'debug' => false]);
					$response = $clientAPI->request('POST', 'visitors/convert', ["json" => ["visitor" => ["user_id" => $visitor_or_lead_user_id], "type" => "lead"]]);
					$lead = json_decode($response->getBody()->getContents());
				}
				else{
					
					$error = "Could not verify if we need to convert visitor to lead. [" . $exception->getResponse()->getStatusCode() . "] " . $exception->getResponse()->getBody(true);
				}
			}
			if($lead != null){
				$client->leads->update([
					"name" => $name,
					"email" => $email,
					"user_id" => $visitor_or_lead_user_id
				]);
				$from = [ "type" => "contact", "user_id" => $lead->user_id];
			}
		}
		if($from != null){
			$client->messages->create([
			  "message_type" => "inapp",
			  "body" => $body,
			  "from" => $from
			]);
			$msg = "Message sent!";
		}
	}
	catch(Exception $e){
		$error = "Unknown error: <pre>" . $e->getMessage() . "</pre>";
	}
}
?>
<h1>Sample Contact Form integration with Intercom</h1>
<ul>
	<li>Sends message to Intercom and creates approrpiate record if needed</li>
	<li>Configurable to allow sending message as
		<ul>
			<li>user (Identified by the email provided. If none found it will create a new record)</li>
			<li>lead (Identified by the email provided. If none found it will create a new record. If multiple leads found it will attach to the first record - will require an extended <a href="https://developers.intercom.com/docs/personal-access-tokens">access token</a>)</li>
			<li>lead (Identified by existing browser session. Will update email on existing record on new submission in same browser session) </li>
		</ul>
	</li>
	<li>Current page is set up to use the <a href="https://docs.intercom.com/install-on-your-product-or-site/quick-install/install-intercom-on-your-website-for-logged-out-visitors">visitor/logged out user snippet of the Intercom integration</a></li>
</ul>

<?php
if($error){
	echo "<h3 style='color: red; font-weight: bold'>Message not sent<br/>$error</h3>";
}
if($msg){
	echo "<h3 style='color: green; font-weight: bold'>$msg</h3>";
}
?>

<form id="msg" action="form.php" method="post">
	<h3>Form configuration</h3>
	<div><b>Record Identification mode</b>
		<div><label><input type="radio" name="mode" value="user">User (uses email to identify user)</label></div>
		<div><label><input type="radio" name="mode" value="lead">Lead (uses email to identify lead)</label></div>
		<div><label><input type="radio" name="mode" value="lead_in_browser">Lead based on on browser session (uses browser session and visitor user_id to identify lead)</label></div>
	</div>

	<h3>Form details exposed to end user</h3>
	<div><label>Name: <input type="text" name="name"></label></div>
	<div><label>Email: <input type="text" name="email"></label></div>
	<div><label>Message: <br/><textarea name="message" cols="40" rows="10"></textarea></label></div>
	<div><label>Lead/Visitor User ID: <input type="text" name="visitor_or_lead_user_id" id="visitor_or_lead_user_id"></label> (should be a hidden input) (if this is not populating, Intercom hasn't loaded up yet or this session is a user session. Logout and boot Intercom using the buttons below</div>
	<input id="sendMessage" type="submit" value="Send Message"></button>
</form>
<h3>Miscellanous Buttons</h3>
<input type="button" value="Read current visitor ID" id="getVisitorId">
<input type="button" value="Boot Intercom " id="boot">
<input type="button" value="Logout / End Session" id="logout">

<script type="text/javascript">
	var APP_ID = '<?php echo $APP_ID ?>';
	window.intercomSettings= {app_id: APP_ID};

	document.getElementById("getVisitorId").addEventListener("click", function(e){
		e.preventDefault();
		var user_id = Intercom("getVisitorId") || "";
		document.getElementById("visitor_or_lead_user_id").value = user_id;
		alert("Visitor/Lead user id is " + user_id + " \n\n(if this is blank, Intercom hasn't loaded up yet or this session is a user session. Logout and boot Intercom again)");
	});
	document.getElementById("sendMessage").addEventListener("click", function(e){
		document.getElementById("visitor_or_lead_user_id").value = Intercom("getVisitorId") || "";
	});
	document.getElementById("boot").addEventListener("click", function(e){
		e.preventDefault();
		Intercom("boot", {app_id: APP_ID});
	});
	document.getElementById("logout").addEventListener("click", function(e){
		e.preventDefault();
		Intercom("shutdown");
	});


(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;
s.src='https://widget.intercom.io/widget/'+APP_ID;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()
</script>