<?php

	function logToFile($command, $result){
		$pathToLog = "/home/akshhhlt/public_html/blupay/sql_log";
		error_log("$command ::: $result \n", 3, $pathToLog);
		if ($command == '')
			error_log("ERROR: Command is blank.\n", 3, $pathToLog);
		if ($result == '')
			error_log("ERROR: Result is blank.\n", 3, $pathToLog);
	}

	function checkConn(){
		$GLOBALS['sqli_conn'] = mysqli_connect("localhost", "", "", "akshhhlt_blupay");
		if ($GLOBALS['sqli_conn']->connect_error){
			$status = ("Failed: " . $GLOBALS['sqli_conn']->connect_error);
		}
		else $status = "Successful";
		logToFile("SQL Status ", $status);
		return $returnVal;
	}

	function insertNewUser($user_phone_num, $name){	
		checkConn();
		if (doesUserExist($user_phone_num)){
			logToFile("Attempted inserting $user_phone_num", "Already in table (1)");
			return;
		}
		$sql = "INSERT INTO `akshhhlt_blupay`.`users` (`user_phone_num`, `name`, `balance`, `locked`) VALUES ('$user_phone_num', '$name', '100', '0')";
		$result = mysqli_query($GLOBALS['sqli_conn'], $sql);
		logToFile($sql, $result);
	}

	function doesUserExist($user_phone_num){
		checkConn();
		$sql = "SELECT name from `akshhhlt_blupay`.`users` where user_phone_num=$user_phone_num";
		$result = mysqli_query($GLOBALS['sqli_conn'], $sql);
		$returnVal = ($result->num_rows == 0 ? false : true);
		logToFile($sql, $returnVal);
		return $returnVal;
	}

	function getBalance($user_phone_num){
		checkConn();
		if (!doesUserExist($user_phone_num)) return -1;
		$sql = "SELECT balance from `akshhhlt_blupay`.`users` where user_phone_num=$user_phone_num";
		$result = mysqli_query($GLOBALS['sqli_conn'], $sql);
		$row = mysqli_fetch_assoc($result);
		logToFile("getBalance for $user_phone_num", "balance = $balance");
		return $row['balance'];

	}

	function resetDemo(){
		checkConn();
		$_SESSION = array();
		$sql = "TRUNCATE TABLE `akshhhlt_blupay`.`users`";
		$result = mysqli_query($GLOBALS['sqli_conn'], $sql);
		logToFile($sql, $result);
		echo "reset successful";
		return "reset successful";
	}

	if ($_GET['action'] == 'reset') resetDemo();

?>