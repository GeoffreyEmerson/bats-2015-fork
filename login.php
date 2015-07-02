<?php

	require_once("functions.php");

	// Get passed data
	if (isset($_POST['user_name'])) $user_name = $_POST['user_name'];
	if (isset($_POST['user_password'])) $user_password = $_POST['user_password'];
	if (isset($_POST['user_host'])) $user_host = $_POST['user_host'];
	if (isset($_POST['user_port'])) $user_port = $_POST['user_port'];
	if (isset($_POST['user_pg_name'])) $user_pg_name = $_POST['user_pg_name'];

	// This turns to false only when a database connection is attempted and failed
	$connect_success = true;
	
	if (isset($user_name) && isset($user_password) && isset($user_host) && isset($user_port) && isset($user_pg_name)) {

		set_error_handler("exception_error_handler");
		try {
		    $conn=@pg_connect("host=$user_host port=$user_port dbname=$user_pg_name user=$user_name password=$user_password");
		} Catch (Exception $e) {
		    $connect_success = false;
		}
		if ($connect_success) {
			session_start(); // Begin the server session so that variables can be passed between pages 
			session_regenerate_id(true); 
			$_SESSION['username'] = $user_name;
			$_SESSION['userpass'] = $user_password;
			$_SESSION['userhost'] = $user_host;
			$_SESSION['userport'] = $user_port;
			$_SESSION['userpgname'] = $user_pg_name;
			header( "Location: index.php" );
		}
	} 
	
?>


<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>BATS Login</title>
		<link rel="stylesheet" href="css/normalize.css">
		<link href='http://fonts.googleapis.com/css?family=Nunito:400,300' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="css/login.css">
	</head>
	<body>

		<form method="post" action="login.php">
			<h1>BATS Log in</h1>
			<?php if (!$connect_success) { echo "<h3 style=color:red>There was an error connecting to the database. Please doublecheck all fields and try again.</h3>";} ?>
			<h3>To tour BATS, use the username "testuser" and the password "testpass".</h3>
			<fieldset>
				<label for="name">User Name:</label>
				<input type="text" id="name" name="user_name" value="<?php if (isset($user_name)) echo $user_name ?>">
				<label for="password">Password:</label>
				<input type="password" id="password" name="user_password">
				<label for="host">Postgres URL:</label>
				<input type="text" id="host" name="user_host" value="<?php if (isset($user_host)) echo $user_host ?>">
				<label for="port">Postgres Port:</label>
				<input type="number" id="host" name="user_port" value="5432">
				<label for="pg_name">User Name:</label>
				<input type="text" id="pg_name" name="user_pg_name" value="bats">
			</fieldset>
			<button type="submit">Submit</button>
		</form>

	</body>
</html>
