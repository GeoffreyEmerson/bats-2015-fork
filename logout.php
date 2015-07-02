<?php
	session_start();
	$_SESSION = array();
	if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], 
				$params["domain"],
        $params["secure"], 
				$params["httponly"]
    );
	}
	session_destroy();
	header( "Location: index.php" );
?>
<!DOCTYPE html>
<html>
	<h3>Logout redirect failed.</h3>
	<h4><a href="index.php">Click here</a> to go back to the main page.</h4>
</html>