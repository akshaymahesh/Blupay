<?php 

	require_once "/home/akshhhlt/public_html/blupay/twilio-php-master/Services/Twilio.php";
	require_once "/home/akshhhlt/public_html/blupay/db_helper.php";
	require_once "/home/akshhhlt/public_html/blupay/keys.php"; // passwords, api key, etc.

	/*** Simulates texts ***/ 
	$testingMode = TRUE;

	/***** TWILLIO ACCOUNT INFO *****/
	$AccountSid = AccountSid;
	$AuthToken = AuthToken;
	$server_phone_num = server_phone_num;
	$client = new Services_Twilio($AccountSid, $AuthToken);

	// define and log relevant information
	$user_phone_num = $_GET["From"];
	$message_body = $_GET["Body"];
	$isExistingUser = doesUserExist($user_phone_num);
	$balance = getBalance($user_phone_num);
	$action = parseText($message_body); // check for keywords in message
	logToFile(time(), "SMS Recieved from \"$user_phone_num\". isExistingUser: $isExistingUser. Action: $action. SMS: $message_body");


	if ($isExistingUser) existing_user($action);
	else onboard_user($message_body);

	/*** Asks new user for name, inserts user into db ***/
	function onboard_user($message_body){
		logToFile("onboard user", $message_body);
		session_start();
		if(!strlen($_SESSION['lastTextTime'])){ // onboarding step 1
			$text = "BLUPAY :: Welcome New User! Reply with your first name to get started";
			$_SESSION['lastTextTime'] = time();
			$_SESSION['lastAction'] = "waitingForName";
		}

		else if ((strlen($_SESSION['lastTextTime'])) && ($_SESSION['lastTextTime'] - time() < 120)){ // onboarding step 2
			insertNewUser($GLOBALS['user_phone_num'], $message_body);
			$text = "BLUPAY :: Welcome $message_body! Your balance is $1000. Reply with SEND, REQUEST, BALANCE or '?'";
		}

		send_text(0, $text);
	}

	/*** Redirects existing user to appropriate action ***/
	function existing_user($action){
		logToFile("existing user", "action = $action");
		session_start();

		if ($action == 999) return; // reset was initiated, skip sending text
    	if ($action == 0)
			$text = "BLUPAY :: Welcome back. Reply with SEND, REQUEST, BALANCE or '?'";
		if ($action == 1)
			$text = "BLUPAY :: Send keyword detected";
		if ($action == 2)
			$text = "BLUPAY :: Request keyword detected";
		if ($action == 3){
			$balance = getBalance($GLOBALS['user_phone_num']);
			$text = "BLUPAY :: Your balance is $balance dollars.";
		}
		send_text(0, $text);
	}

	/*** Determines message intent by looking for keywords ***/
	function parseText($message_body){
		$message_body = strtolower($message_body);
		$action = 0; // if $action is unchanged, it's not one of the commands
		if (strpos($message_body,'send') !== false)
			$action = 1;
		if (strpos($message_body,'request') !== false)
			$action = 2;
		if (strpos($message_body,'balance') !== false)
			$action = 3;
		if (strpos($message_body,'reset') !== false){
			send_text(0, resetDemo());
			$action = 999;
		}

		logToFile("parseText", "action = $action");
		return $action;
	}

	/*** Uses Twilio API to send text ***/
	function send_text($target_phone_num, $sms){
		global $client, $user_phone_num, $testingMode;
		if ($target_phone_num == 0) $target_phone_num = $user_phone_num;
		$smsSent = ($testingMode ? 0 : $client->account->sms_messages->create(
				    server_phone_num,
				    $target_phone_num,
				    $sms));
		logToFile("Sent text: \"$sms\"", 1);
		echo ($testingMode ? "$sms <br>" : '');
	}

 ?>