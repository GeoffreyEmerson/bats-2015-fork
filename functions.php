<?php

date_default_timezone_set('America/Los_Angeles');

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

function session_check() {
	if ( !session_id() ) session_start(); 
}

function connect_to_db() {

	session_check();
	$host_seg = "host=" . $_SESSION['userhost'] . " ";
	$port_seg = "port=" . $_SESSION['userport'] . " ";
	$dbname_seg = "dbname=" . $_SESSION['userpgname'] . " ";
	$username_seg = "user=" . $_SESSION['username'] . " ";
	$password_seg = "password=" . $_SESSION['userpass'];
	
	set_error_handler("exception_error_handler");
	try {
		$db = pg_connect($host_seg . $port_seg . $dbname_seg . $username_seg . $password_seg);
	} Catch (Exception $e) {
    	echo "<pre>" . $e->getMessage() . "</pre>";
	}

	if (!$db) {
		echo "Unknown database connection error.";
	}
}

function safe_query($query) {
	set_error_handler("exception_error_handler");
	try {
		$result = pg_query($query);
	} Catch (Exception $e) {
    	echo "<pre>Problem with query: " . $query . "\n";
    	echo "<pre>Error Message:" . $e->getMessage() . "</pre>\n";
    	// This needs to be updated to redirect to a well-formatted error screen
	}

	//This checks for non-fatal errors that return nothing to $result
	error_check($result,$query);

	return $result;
}

function check_login() {
	if (isset($_SESSION['username'])) {
		$user_logged_in = TRUE;
	} else {
		$user_logged_in = FALSE;
	}
}

function html_header($page_name,$focus) {
	echo "<!DOCTYPE html>\n";
	echo "<html>\n";
	echo "\t<head>\n";
	echo "\t\t<meta charset=\"utf-8\">\n";
	echo "\t\t<link rel=\"shortcut icon\" href=\"favicon.ico\">\n";
	echo "\t\t<title>$page_name</title>\n";
	echo "\t\t<link rel=\"stylesheet\" src=\"http://normalize-css.googlecode.com/svn/trunk/normalize.css\">\n";
	echo "\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"css/bats.css\">\n";
	echo "\t\t<link href='http://fonts.googleapis.com/css?family=Nunito:400,300' rel='stylesheet' type='text/css'>\n";
	echo "\t</head>\n\n";
	if ($focus == '') { 
		echo "\t<body>\n";
	} else {
		echo "\t<body OnLoad=\"document.update_form.$focus.focus();\">\n";
	}
}

// Converts database column names to something that looks better on screen
function display_format($var) {
	switch($var) {

	case "sticker":		return "Sticker #";
	case "type":		return "Type";
	case "description":	return "Description";
	case "serial":		return "Serial";
	case "purchasedate":	return "Purchase Date";
	case "temp_assignment":	return "Assignment";
	case "computernumber":	return "Number";
	case "videocard":	return "Video Card";
	case "videobus":	return "Bus Type";
	case "cpu":		return "CPU Type";
	case "harddrive":	return "Hard Drive";
	case "memory":		return "Memory";
	case "servicetag":	return "Dell Tag";
	case "batch":		return "Batch";
	case "expresscode":	return "Express Code";
	case "warranty":	return "Warranty";
	case "mayadongle":	return "Maya Dongle";
	case "firstname":	return "First Name";
	case "lastname":	return "Last Name";
	case "department":	return "Department";
	case "extension":	return "Extension";
	case "status":		return "Status";
	case "user_status":	return "Status";
	case "active":		return "Active";
		
	default: return "NOT FORMATTED: $var";
	}
}

function column_head($page, $pass_array, $sort_by, $column, $order, $default_sort) {

	// This switch is a future expandable list for database columns that aren't appropriately named for column headings.
	switch($column) { 
		case "firstname":	
						$display="Assigned To"; 
						break;
		default:	
						$display=display_format($column);
	}

	// This section makes the activated column for sorting link to it's opposite sort direction
	
	// Make the sort direction toggle
	if ($default_sort=="desc") {
		$anti_sort="asc";
	} else {
		$anti_sort="desc";
	}
	
	// Replace the activated column's link with it's opposite sort direction
	if ( ($sort_by == $column) && ($order == $default_sort) ) {
		$sort_pass = $anti_sort;	
	} else {
		$sort_pass = $default_sort; 
	} 
	
	if ($sort_by == $column) {
		if ($sort_pass == "desc") {
			$arrow = "<span id=sort-arrow>&#x25B2;</span>";
		} else {
			$arrow = "<span id=sort-arrow>&#x25BC;</span>";
		}
	} else {
		$arrow = "";
	}
	
	// Pass through search terms for re-use so that sorting a column doesn't destroy searches
	if ( isset($pass_array['search']) ) {
		$search_section = "&search=".$pass_array['search']."&key=".htmlspecialchars($pass_array['key']); 
	} else {
		$search_section = "";
	}

	echo "\t\t\t\t\t<th class=\"main-thead-cell\"><a href=\"".$page.".php?sort=".$column."&order=".$sort_pass.$search_section."\">".$arrow.$display."</a></th>\n";
	
	return;
}




function detail_row($current_tbl, $asset, $current_val, $field, $edit_field) {
	// Used in user_datails.php and tag_details.php
	// If those pages are ever combined, consider moving this function to that page
	if ($edit_field == $field) {

		echo "\t\t\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t\t\t<td>".display_format($field)."</td>\n";
		echo "\t\t\t\t\t\t\t<td>\n";
		echo "\t\t\t\t\t\t\t\t<FORM name=\"update_form\" method=\"post\" action=\"updating.php\">\n";
		echo "\t\t\t\t\t\t\t\t<INPUT TYPE=hidden NAME=pass_table VALUE=$current_tbl>\n";
		echo "\t\t\t\t\t\t\t\t<INPUT TYPE=hidden NAME=pass_assetid VALUE=$asset>\n";
		echo "\t\t\t\t\t\t\t\t<INPUT TYPE=hidden NAME=pass_field VALUE=$field>\n";
		if ( ($field == "type") || ($field == "description") || ($field == "department") || ($field == "videocard") || ($field == "videobus") || ($field == "harddrive") || ($field == "cpu") || ($field == "memory") ) {
		
			echo "<SELECT NAME=\"new_value\">\n";

			// Create OPTION list from current values in the database!
			$query = "SELECT $field FROM $current_tbl GROUP BY $field ORDER BY $field";
			$result = pg_query($query);
			while( $option_row = pg_fetch_assoc($result) ) {
				echo "<OPTION VALUE=\"".htmlentities($option_row[$field])."\"";
				if ($option_row[$field] == $current_val) {
					echo " selected=\"selected\"";
				}
				echo ">".htmlentities($option_row[$field])."</OPTION>\n";
			}

			echo "	</SELECT>\n";
			echo "	</td>\n";

		} elseif ($field == "active") {
			echo "<input type=radio NAME=\"new_value\" value=\"t\" ";
			if ($current_val=="t") { echo "checked";}
			echo ">Yes &nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<input type=radio NAME=\"new_value\" value=\"f\" ";
			if ($current_val=="f") { echo "checked";}
			echo ">No";
		} elseif ($field == "status") {
			$choice_array = array('Active','Available','Benched','Unstable','Proxy','Build','Remote','Parts','Reserved','Decomissioned','Unknown');
			echo "<SELECT NAME=\"new_value\">\n";
			
			foreach ($choice_array as $choice) {
				echo "<OPTION VALUE=\"". $choice ."\"";
					if ($choice == $current_val) { echo "selected=\"selected\""; }
				echo ">". $choice ."</OPTION>\n";
			}
			echo "</SELECT>\n";
			echo "</td>\n";

		} else {
			$value = htmlentities($current_val);
			echo "\t\t\t\t\t\t\t\t<INPUT TYPE=text NAME=\"new_value\" VALUE=\"".$value."\">\n";
			echo "\t\t\t\t\t\t\t</td>\n";
		}
		echo "\t\t\t\t\t\t\t<td>\n";
		echo "\t\t\t\t\t\t\t\t<INPUT TYPE=submit VALUE=\"Update\"></td>\n";
		echo "\t\t\t\t\t\t\t\t</FORM>\n";
		echo "\t\t\t\t\t\t\t</td>\n";
		echo "\t\t\t\t\t\t</tr>\n";

		return;

	} else {		// Display data that is not currently being edited
		echo "\t\t\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t\t\t<td> ".display_format($field)."</td>\n";
		if ( ($field=="active") && ($current_val=='t') ) {
			echo "\t\t\t\t\t\t\t<td style=\"color:green;\">Yes</td>\n";
		} elseif ( ($field=="active") && ($current_val=='f') ) {
			echo "\t\t\t\t\t\t\t<td style=\"color:red;\">No</td>\n";
		} elseif ( ($field=="active") && (!$current_val) ) {
			echo "\t\t\t\t\t\t\t<td style=\"color:red;\">NULL!</td>\n";
		} elseif ( ($field=="batch") || ($field=="harddrive") || ($field=="memory") || ($field=="videocard") || ($field=="videobus") || ($field=="cpu") || ($field=="warranty") || ($field=="status") ) { // Some fields get a reference to a list of other assets with the same value
			echo "\t\t\t\t\t\t\t<td><a href=\"pc_list.php?search=$field&key=".htmlentities($current_val). "\">$current_val</a></td>\n";
		} elseif ($field=="description") {
			echo "\t\t\t\t\t\t\t<td><a href=\"asset_list.php?search=$field&key=".htmlentities($current_val). "\">$current_val</a></td>\n";
		} else {
			echo "\t\t\t\t\t\t\t<td> ".htmlentities($current_val)."</td>\n";
		}

		if ( ($current_tbl=="asset_tbl") || ($current_tbl=="pc_detail_tbl")       ) {
			echo "\t\t\t\t\t\t\t<td><a href=\"tag_details.php?asset=".$asset."&field=".$field."\">edit</a></td>\n";
		} elseif ($current_tbl == "user_tbl") {
			echo "\t\t\t\t\t\t\t<td><a href=\"user_details.php?user=".$asset."&field=".$field."\">edit</a></td>\n";
		}
		echo "\t\t\t\t\t\t</tr>\n";

		return;
	}
}


/*
function form_row($field) {

	echo "\t\t\t<tr>\n";
	echo "\t\t\t\t<td> ".display_format($field)."</td>\n";
	echo "\t\t\t\t<td ALIGN=center><INPUT TYPE=text NAME=\"new_$field\"></td>\n";
	echo "\t\t\t</tr>\n";

	return;
}
*/

function error_check($result,$query) {
	if (!$result) {
		echo "<pre>Problem with query: " . $query . "\n";
		echo pg_last_error();
		echo "</pre>";
		exit();
	}
}

function query_error_check($passed_result, $passed_query, $note="") { //check queries for errors
	if (!$passed_result) {
		html_header('HI Asset DB - Error','');
		include("top_menu.php");
		echo "<h2>Problem with query - " . $note . "</h2>\n";
		echo "<pre>" . $query . "\n";
		echo pg_last_error();
		echo "</pre></body></html>\n";
		exit();
	}
	return;
}