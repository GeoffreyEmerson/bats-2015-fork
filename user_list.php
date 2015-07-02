<?php

	include("functions.php");
	html_header('HI Asset DB - Employee List','');
	include("top_menu.php");

	echo "\t\t<h1>HI Asset DB - Employee List</h1>\n\n";

	// MOVE TO SEPARATE PAGE: Entry fields for New User

	if ( isset($_GET["add"]) ) {
		echo "<table id=\"new_user_form\">\n";
		echo "<thead>\n";
		echo "<tr><th colspan=2>Add a New User</th></tr>\n";
		echo "</thead>\n";
		echo "<form name=\"new_user_form\" method=\"post\" action=\"adduser.php\">\n";
		echo "<tr><td>First Name:</td><td><INPUT TYPE=text NAME=pass_fname></td></tr>\n";
		echo "<tr><td>Last Name:</td><td><INPUT TYPE=text NAME=pass_lname></td></tr>\n";
		echo "<tr><td>Department:</td><td><INPUT TYPE=text NAME=pass_dept></td></tr>\n";
		echo "<tr><td colspan=2 style=\"align: center\"><INPUT TYPE=submit VALUE=\"Add User\"></td></tr>\n";
		echo "</form></table>\n";
	} else {
		echo "\t\t<div id=add-user-div>\n";
		echo "\t\t\t<a id=add-user-link href=user_list.php?add=new>Add User</a>\n";  // This could use some better page placement
		echo "\t\t</div>\n\n";
	}


	// Start database connect and query for active users

	connect_to_db();

	if ( isset($_GET["sort"]) ) {
		$sort_by = $_GET["sort"]; 
	} else {
		$sort_by = "firstname,lastname";
	}
	
	if ( isset($_GET["order"]) ) { 
		$order = $_GET["order"]; 
	} else { 
		$order = ""; 
	}
	
	if ( isset($_GET["activeonly"]) ) {
		$where_clause = "WHERE active='t' ";
	} else {
		$where_clause = "";
	}

	$query = "SELECT * FROM user_tbl ".$where_clause."ORDER BY ".$sort_by." ".$order;

	$result = pg_query($query);
	error_check($result,$query);

	echo "\t\t<table>\n";
	echo "\t\t\t<thead>\n";
//	echo "\t\t\t\t<tr>\n";
//	echo "\t\t\t\t\t<th colspan=5 style=\"valign: center;\">ACTIVE USERS</th>\n";
//	echo "\t\t\t\t</tr>\n";

	echo "\t\t\t\t<tr>\n";

	// Display column names with sorting options

	if ( ($sort_by == "userid") && ($order != "desc") ) {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=userid&order=desc\">ID</a></th>\n"; 
	} else {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=userid\">ID</a></th>\n"; 
	}
	if ($sort_by == "firstname") {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=firstname&order=desc\">First Name</a></th>\n";
	} else {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=firstname\">First Name</a></th>\n";
	}
	if ($sort_by == "lastname") {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=lastname&order=desc\">Last Name</a></th>\n";
	} else {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=lastname\">Last Name</a></th>\n";
	}
	if ( ($sort_by == "department") && ($order != "desc") ) {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=department&order=desc\">Department</a></th>\n"; 
	} else {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=department\">Department</a></th>\n"; 
	}
	if ( ($sort_by == "active") && ($order != "desc") ) {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=active&order=desc\">Current Status</a></th>\n"; 
	} else {
		echo "\t\t\t\t\t<th><a href=\"user_list.php?sort=active\">Current Status</a></th>\n"; 
	}
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t</thead>\n";
	echo "\t\t\t<tbody>\n";

	while($myrow = pg_fetch_assoc($result)) {
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t<td><a href=\"user_details.php?user=".$myrow['userid']."\">".$myrow['userid']."</a></td>\n";
		echo "\t\t\t\t\t<td><a href=\"user_details.php?user=".$myrow['userid']."\">".htmlspecialchars($myrow['firstname'])."</a></td>\n";
		echo "\t\t\t\t\t<td><a href=\"user_details.php?user=".$myrow['userid']."\">".htmlspecialchars($myrow['lastname'])."</a></td>\n";
		echo "\t\t\t\t\t<td>".htmlspecialchars($myrow['department'])."</td>\n";
		if ($myrow['active'] == 't') {
			echo "\t\t\t\t\t<td>Active</td>\n";
		} else {
			echo "\t\t\t\t\t<td>Inactive</td>\n";
		}
		echo "\t\t\t\t</tr>\n";
	}
	echo "\t\t\t</tbody>\n";
	echo "\t\t</table>\n\n";
	
	
	// Depricated: Query for inactive users
/*
	$query = "SELECT * FROM user_tbl WHERE active='f' ORDER BY ".$sort_by." ".$order;

	$result = pg_query($query);
        if (!$result) {
            echo "Problem with query " . $query . "<br/>";
            echo pg_last_error();
            exit();
        }

	echo "\t\t<table>\n";
	echo "\t\t\t<thead>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<th colspan=4 style=\"valign: center;\">FORMER USERS</th>\n";
	echo "\t\t\t\t</tr>\n";

	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td> ID </td>\n";
	echo "\t\t\t\t\t<td> First Name </td>\n";
	echo "\t\t\t\t\t<td> Last Name </td>\n";
	echo "\t\t\t\t\t<td> Department </td>\n";
	echo "\t\t\t\t</tr>\n";
	
	echo "\t\t\t</thead>\n";
	echo "\t\t\t<tbody>\n";

	while($myrow = pg_fetch_assoc($result)) {
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t<td><a href=\"user_details.php?user=".$myrow['userid']."\">".$myrow['userid']."</a></td>\n";
		echo "\t\t\t\t\t<td><a href=\"user_details.php?user=".$myrow['userid']."\">".htmlspecialchars($myrow['firstname'])."</a></td>\n";
		echo "\t\t\t\t\t<td><a href=\"user_details.php?user=".$myrow['userid']."\">".htmlspecialchars($myrow['lastname'])."</a></td>\n";
		echo "\t\t\t\t\t<td>".htmlspecialchars($myrow['department'])."</td>\n";
		echo "\t\t\t\t</tr>\n";
	} 
	
	echo "\t\t\t</tbody>\n";
	echo "\t\t</table>\n\n";
*/

	echo "\t</body>\n";
	echo "</html>";

?>
