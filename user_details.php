<?php

	// Note: pages "tag_details.php" and "user_details.php" are very similar. 
	// It might be worth investigating combining the pages.
		
	if ( isset($_GET['field']) ) {  // This is used if a request to edit a field has been made.
		$edit_field = $_GET['field'];
	} else {
		$edit_field="";
	}

	if ($edit_field && $edit_field!='active') {
		$target = "new_value";
	} else {
		$target = "";
	}

	include("functions.php");
	html_header('BATS - User Detail',$target);
	include("top_menu.php");
	
	echo "\t\t<h1>User Details</h1>\n\n";

	// Connect to database
	// and initialize passed parameters

	connect_to_db();
	$current_tbl = "user_tbl";
	
	if ( isset($_GET["user"]) ) {
		$userid = $_GET["user"];
		$query = "SELECT * FROM $current_tbl WHERE userid='$userid'";
	} else { 
		echo "<h2>Error! No user selected.</h2>";
		exit();
	}

	$result = pg_query($query);
	if (!$result) {
		echo "<pre>Problem with query: " . $query . "\n\n";
		echo pg_last_error();
		echo "</pre>";
		exit();
	}

	$myrow = pg_fetch_assoc($result); // Converts the results into an easily parsable array.
	
// Create main asset data list

	// Framing. Replace with div?
	// echo "\t\t<table class=outerframe>\n"; 
	// echo "\t\t\t<tr>\n";
	// echo "\t\t\t\t<td class=outerframe>\n";

	echo "\t\t<div class=outerframe>\n";
	
	// Inner table 1: User attributes
	echo "\t\t\t\t<table class=leftframe>\n"; 
	echo "\t\t\t\t\t<thead>\n";
	echo "\t\t\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t\t\t<th>User ID</th>\n";
	echo "\t\t\t\t\t\t\t<th colspan=2>". $myrow['userid'] ."</th>\n";
	echo "\t\t\t\t\t\t</tr>\n";
	echo "\t\t\t\t\t</thead>\n";

	// This function allows for editable fields
	detail_row($current_tbl, $myrow['userid'], $myrow['firstname'], 'firstname', $edit_field);
	detail_row($current_tbl, $myrow['userid'], $myrow['lastname'], 'lastname', $edit_field);
	detail_row($current_tbl, $myrow['userid'], $myrow['department'], 'department', $edit_field);
	detail_row($current_tbl, $myrow['userid'], $myrow['extension'], 'extension', $edit_field);
	// detail_row($current_tbl, $myrow['userid'], $myrow[user_status], 'user_status', $edit_field);
	detail_row($current_tbl, $myrow['userid'], $myrow['active'], 'active', $edit_field);

	// Framing. Replace with div?	
	echo "\t\t\t\t\t</table>\n"; 
	// echo "\t\t\t\t</td>\n";
	// echo "\t\t\t\t<td class=outerframe>\n"; 
	
	// Inner table 2: User Assignments
	echo "\t\t\t\t\t<table class=rightframe>\n"; 

	$user_query = "SELECT * FROM asset_tbl a
			LEFT OUTER JOIN pc_detail_tbl ON (pc_assetid=assetid)
			LEFT JOIN (
				SELECT DISTINCT ON (assetid_fkey) * FROM assignment_tbl 
				LEFT JOIN user_tbl ON (userid_fkey=userid)
				ORDER BY assetid_fkey, datetime desc
			) AS usernames
			ON assetid=assetid_fkey
			WHERE userid = '$userid'
			ORDER BY type ASC, description ASC";

	$result = pg_query($user_query);
	if (!$result) {
			echo "<pre>Problem with query: " . $user_query . "\n\n";
			echo pg_last_error();
			echo "</pre>";
			exit();
	}

	echo "\t\t\t\t<thead>\n";
	echo "\t\t\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t\t\t<th colspan=3> Assigned Equipment </th>\n";
	echo "\t\t\t\t\t\t</tr>\n";
	echo "\t\t\t\t</thead>\n";

	if ( pg_num_rows($result)>0 ) {
		while ( $assign_row = pg_fetch_assoc($result) ) {
			echo "\t\t\t\t\t\t<tr>\n";
			echo "\t\t\t\t\t\t\t<td><a href=tag_details.php?asset=" . $assign_row['assetid'] . ">";
			if($assign_row['sticker']) { 
				echo $assign_row['sticker'];
			} else { 
				echo "?"; //This makes it more clear an asset is missing a sticker, and gives something to click on for the anchor.
			}
			echo "</a></td>\n";
			echo "\t\t\t\t\t\t\t<td>";
			if($assign_row['type']=='PC') {
				echo "[". $assign_row['computernumber'] ."] - ";
			}
			echo $assign_row['description'] . "</td>\n";
			echo "\t\t\t\t\t\t\t<td>" . date( 'n/d/Y g:i A' , strtotime($assign_row['datetime']) ). "</td>\n";
			echo "\t\t\t\t\t\t</tr>\n";

		}
	} else {
		echo "\t\t\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t\t\t<td colspan=\"2\"> No assignments </td>\n";
		echo "\t\t\t\t\t\t</tr>\n";
	}

	echo "\t\t\t\t\t</table>\n";
	
	// End of outer table/div?
	// echo "\t\t\t\t</td>\n";
	// echo "\t\t\t</tr>\n";
	// echo "\t\t</table>\n\n";
 
 	echo "\t\t</div>\n";

	// Create Notes table below main table
	echo "\t\t<h2>User Notes</h2>\n";
	echo "\t\t<div id=add-user-div>\n";
	echo "\t\t\t<a id=add-note-link href=user_details.php?user=$userid&field=note>Add note</a>\n";  // This could use some better page placement
	echo "\t\t</div>\n\n";
	echo "\t\t<table>\n";
	echo "\t\t\t<thead>\n";
	echo "\t\t\t<tr>\n";
	echo "\t\t\t\t<th colspan=2>Date</th>\n";
	echo "\t\t\t\t<th>Note</th>\n";
	echo "\t\t\t</tr>\n";
	echo "\t\t\t</thead>\n";
		
	if ($edit_field == 'note') {
		echo "\t\t\t<tr>\n";
		echo "\t\t\t\t<td colspan=3>\n";
		echo "\t\t\t\t\t<form name=\"update_form\" method=\"post\" action=\"addnote.php\">\n";
		echo "\t\t\t\t\t<input type=hidden name=pass_id value=$userid>\n";
		echo "\t\t\t\t\t<input type=hidden name=signature value='". $_SESSION['username'] ."'>\n";
		echo "\t\t\t\t\t<input type=hidden name=pass_table value=\"user_notes_tbl\">\n";
		echo "\t\t\t\t\t<textarea cols=\"50\" rows=\"4\" name=\"new_value\"></textarea>\n";
		echo "\t\t\t\t\t<input type=submit value=\"Add Note\">\n";
		echo "\t\t\t\t\t</form>\n";
		echo "\t\t\t\t</td>\n";
		echo "\t\t\t</tr>\n\n";
	}



	// Query for notes

	$query = "SELECT * FROM user_notes_tbl WHERE userid='$userid' ORDER BY datetime DESC";

	$notes_result = pg_query($query);
	if (!$result) {
            echo "Problem with query: " . $query . "<br/>\n";
            echo pg_last_error();
            exit();
	}

	// List notes, if any
	if ( pg_num_rows($notes_result) ) {
		while($note_row = pg_fetch_assoc($notes_result)) {
			echo "\t\t\t<tr>\n";
			echo "\t\t\t\t<td>";
			echo date( 'n/d/Y g:i A' , strtotime($note_row['datetime']) );
			echo "</td>\n";
			echo "\t\t\t\t<td>";
			if ($note_row['signature']) {
				echo " <img src=note.gif title=\"Note added by: " . $note_row['signature'] . "\">";
			} else {
				echo "&nbsp;";
			}
			echo "</td>\n";
			echo "\t\t\t\t<td class=\"notetext\">".$note_row['note']."</td>\n";
			echo "\t\t\t</tr>\n";
		}
	} else {
		echo "\t\t\t<tr>\n";
		echo "\t\t\t\t<td colspan=3> No notes </td></tr>\n";
	}

	echo "\t\t</table>\n\n";
	
	echo "\t\t<footer>\n";
	echo "\t\t</footer>\n\n";
	
	echo "\t</body>\n";
	echo "</html>";

?>
