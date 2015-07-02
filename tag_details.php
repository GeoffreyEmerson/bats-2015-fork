<?php
	
	// Note: pages "tag_details.php" and "user_details.php" are very similar. 
	// It might be worth investigating combining the pages.

	if ( isset($_GET['field']) ) {  // This is used if a request to edit a field has been made.
		$edit_field = $_GET['field'];
		$target = "new_value";
	} else {
		$edit_field="";
		$target = "";
	}

	include("functions.php");
	html_header('BATS - Asset Detail',$target);
	include("top_menu.php");

	echo "\t\t<h1>Asset Details</h1>\n\n";
	
	// Connect to database
	// and initialize passed parameters

	connect_to_db();
	$current_tbl = "asset_tbl"; // I might use this variable later to make the SELECT statements more versatile.

	// Set the assetid variable
	if ( isset($_GET['asset']) ) { 		// This is the normal route to get the assetid
		$assetid = $_GET['asset'];
		$query = "SELECT * FROM $current_tbl WHERE assetid='$assetid'";
	} elseif ( isset($_POST['tag_number']) ) {	// This is for searches by tag number
		$tag_number = $_POST['tag_number'];
		$query = "SELECT * FROM ".$current_tbl." WHERE sticker='".$tag_number."'";
	} else {
		// If there is no asset or tag to search by...
		echo "<h2>No Asset Variable Passed!</h2>";
		exit();
	}

	$asset_result = pg_query($query);
	error_check($asset_result, $query);

	// This section is for when a sticker has been put on an asset, but not yet entered into the system.
	if (pg_num_rows($asset_result)==0) {
		echo "<h2>Number of rows returned: ".pg_num_rows($asset_result)."</h2>"; 
		echo "<h1>Tag Not Found!</h1>\n\n"; 
		echo "<h2>Use this form to create an entry for: " . $tag_number . "</a></h2>\n";
		include("new_asset_module1.php");
		exit();
	}

	$asset_row = pg_fetch_assoc($asset_result); // Converts the results into an easily parsable array.

// Create main asset data list

	// Framing. Replace with div?
/* 	echo "\t\t<table class=outerframe>\n"; 
	echo "\t\t\t<tr>\n";
	echo "\t\t\t\t<td class=outerframe>\n"; */
	
	echo "\t\t<div class=outerframe>\n";
	
	// Inner table 1: User attributes
	echo "\t\t\t<table class=leftframe>\n"; 

	echo "\t\t\t\t\t<thead>\n";
	echo "\t\t\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t\t\t<th>Asset ID</th>\n";
	echo "\t\t\t\t\t\t\t<th colspan=2>". $asset_row['assetid'] ."</th>\n";
	echo "\t\t\t\t\t\t</tr>\n";
	echo "\t\t\t\t\t</thead>\n";
	
	if ($asset_row['active']=="f") { 
		echo "\t\t\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t\t\t<td colspan=2 style=\"color:red;\"><b>DECOMISSIONED</b></td>\n";
		echo "\t\t\t\t\t\t</tr>\n"; 
	}

	detail_row($current_tbl, $asset_row['assetid'], $asset_row['sticker'], 'sticker', $edit_field);
	detail_row($current_tbl, $asset_row['assetid'], $asset_row['type'], 'type', $edit_field);
	detail_row($current_tbl, $asset_row['assetid'], $asset_row['description'], 'description', $edit_field);
	detail_row($current_tbl, $asset_row['assetid'], $asset_row['serial'], 'serial', $edit_field, $asset_row);
	detail_row($current_tbl, $asset_row['assetid'], $asset_row['purchasedate'], 'purchasedate', $edit_field);
	detail_row($current_tbl, $asset_row['assetid'], $asset_row['status'], 'status', $edit_field);

	// If the asset is a PC, fetch PC specific details
	if ($asset_row['type']=="PC") {
		$sub_tbl = "pc_detail_tbl";

		$query = "SELECT * FROM $sub_tbl WHERE pc_assetid=".$asset_row['assetid'];
		
		$pc_result = pg_query($query);
		if (!$pc_result) {
			echo "<pre>Problem with query: " . $query . "\n\n";
			echo pg_last_error();
			echo "</pre>";
			exit();
		}
		$pc_row = pg_fetch_assoc($pc_result);
	
		// Build PC Data list
	
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['computernumber'], 'computernumber', $edit_field);
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['videocard'], 'videocard', $edit_field);
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['videobus'], 'videobus', $edit_field);
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['cpu'], 'cpu', $edit_field);
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['harddrive'], 'harddrive', $edit_field);
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['memory'], 'memory', $edit_field);
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['batch'], 'batch', $edit_field);
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['expresscode'], 'expresscode', $edit_field);
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['warranty'], 'warranty', $edit_field);
		detail_row($sub_tbl, $asset_row['assetid'], $pc_row['mayadongle'], 'mayadongle', $edit_field);
	}
	detail_row($current_tbl, $asset_row['assetid'], $asset_row['active'], 'active', $edit_field);

	echo "\t\t\t</table>\n"; 
	
	// Framing. Replace with div?	
	/* 	echo "\t\t\t\t</td>\n";
	echo "\t\t\t\t<td class=outerframe>\n";  */
 
	
	// Inner table 2: User Assignments
	echo "\t\t\t<table class=rightframe>\n"; 
	
	$assignment_query = "SELECT * FROM asset_tbl 
				LEFT JOIN assignment_tbl ON (asset_tbl.assetid=assignment_tbl.assetid_fkey)
				LEFT JOIN user_tbl ON (assignment_tbl.userid_fkey=user_tbl.userid)
					WHERE (asset_tbl.assetid='".$asset_row['assetid']."')
					ORDER BY assignment_tbl.datetime DESC";

	$assignment_result = pg_query($assignment_query);
	if (!$assignment_result) {
		echo "<pre>Problem with query: " . $assignment_query . "\n\n";
		echo pg_last_error();
		echo "</pre>";
		exit();
	}

	// Get the first row for use with the New User Assignment form
	$assignment_row = pg_fetch_assoc($assignment_result);
 	
	echo "\t\t\t\t<thead>\n";
	echo "\t\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t\t<th>User Assignment</th>\n";
	echo "\t\t\t\t\t\t<th>";
	if ($edit_field !='assign') { 
		echo"<a href=\"tag_details.php?asset=".$asset_row['assetid']."&field=assign\"><button type=button>Update</button></a>";
	} else {
		echo "\n\t\t\t\t\t\t\t<form name=\"update_form\" method=\"post\" action=\"updating.php\">\n";
		echo "\t\t\t\t\t\t\t<input type=hidden name=pass_table value=\"assignment_tbl\">\n";
		echo "\t\t\t\t\t\t\t<input type=hidden name=pass_currentid value=\"".$assignment_row['userid']."\">\n";
		echo "\t\t\t\t\t\t\t<input type=hidden name=pass_assetid value=".$asset_row['assetid'].">\n";
		echo "\t\t\t\t\t\t\t<select name=\"new_value\">\n";

		// Query all names from active users to create a list of names to chose from
		
		$query = "SELECT * FROM user_tbl WHERE active='t' ORDER BY firstname";
		$user_result = pg_query($query);

		if (!$user_result) {
			echo "</form></td></tr></table><pre>Problem with query: " . $query . "\n\n";
			echo pg_last_error();
			echo "</pre></table></body>";
			exit();
		}

		while( $option_row = pg_fetch_assoc($user_result) ) {
			echo "\t\t\t\t\t\t\t\t<option value=\"".$option_row['userid']."\"";
			if ($option_row['userid'] == $assignment_row['userid']) {
				echo " selected=\"selected\"";  // Makes the current assignee selected
			}
			echo ">".$option_row['firstname']." ".$option_row['lastname']."</option>\n";
		}
		echo "\t\t\t\t\t\t\t</select>\n";
		echo "\t\t\t\t\t\t\t<button type=submit>Assign</button>\n";
		echo "\t\t\t\t\t\t\t<a href=\"tag_details.php?asset=".$asset_row['assetid']."\"><button type=button>Cancel</button></a>\n";
	}
	echo "</th>\n";
	echo "\t\t\t\t\t</tr>\n";
	echo "\t\t\t\t</thead>\n";

 
	// Get first row for current user assignment
	if ($assignment_row['userid_fkey'] !="" ) {
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t<td><a href=user_details.php?user=" . $assignment_row['userid'] . ">";
		echo $assignment_row['firstname'] . " " . $assignment_row['lastname'];
		echo "</a></td>\n";
		echo "\t\t\t\t\t<td>" . date( 'n/d/Y g:i A' , strtotime($assignment_row['datetime']) ). "</td>\n";
		echo "\t\t\t\t</tr>\n";
		echo "\t\t\t</table>\n";

		// Get second row for history of user assignments
		$assignment_row = pg_fetch_assoc($assignment_result);
		if ($assignment_row['userid_fkey'] !="" ) {
			echo "\t\t\t<table class=rightframe id=history>\n";
			echo "\t\t\t\t<thead>\n";
			echo "\t\t\t\t\t<tr>\n";
			echo "\t\t\t\t\t\t<th colspan=2>Assignment History</th>\n";
			echo "\t\t\t\t\t</tr>\n";
			echo "\t\t\t\t</thead>\n";
			do {
				echo "\t\t\t\t<tr>\n";
				echo "\t\t\t\t\t<td><a href=user_details.php?user=" . $assignment_row['userid'] . ">";
				echo $assignment_row['firstname'] . " " . $assignment_row['lastname'];
				echo "</a></td>\n";
				echo "\t\t\t\t\t<td>" . date( 'n/d/Y g:i A' , strtotime($assignment_row['datetime']) ) . "</td>\n";
				echo "\t\t\t\t</tr>\n";
			} while ($assignment_row = pg_fetch_assoc($assignment_result));
		}
	} else {
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t<td colspan=\"2\"> No assignment </td>\n";
		echo "\t\t\t\t</tr>\n";
	}

	echo "\t\t\t</table>\n";
	echo "\t\t</div>\n"; // End of outerframe

	// Create Notes table below main table
	echo "\t\t<h2>Asset Notes</h2>\n";
	echo "\t\t<div id=add-asset-div>\n";
	echo "\t\t\t<a id=add-asset-link href=\"tag_details.php?asset=".$asset_row['assetid']."&field=note\">Add note</a>\n";  // This could use some better page placement
	echo "\t\t</div>\n\n";
	echo "\t\t<table>\n";
	echo "\t\t\t<thead>\n";
	echo "\t\t\t<tr>\n";
	echo "\t\t\t\t<th class=note-date-col>Date</th>\n";
	echo "\t\t\t\t<th colspan=2>Note</th>\n";
	echo "\t\t\t</tr>\n";
	echo "\t\t\t</thead>\n";

	// Create field to type in a note if that button has been pressed.
	// This should probably move to the area where the "Add note" button is displayed.
	if ($edit_field == 'note'){
		echo "\t\t\t<tr>\n";
		echo "\t\t\t\t<td colspan=3>\n";
		echo "\t\t\t\t\t<form method=\"post\" name= \"update_form\" action=\"addnote.php\">\n";
		echo "\t\t\t\t\t\t<input type=hidden name=pass_id value=".$asset_row['assetid'].">\n";
		echo "\t\t\t\t\t\t<input type=hidden name=signature value='". $_SESSION['username'] ."'>\n";
		echo "\t\t\t\t\t\t<input type=hidden name=pass_table value=\"asset_notes_tbl\">\n";
		echo "\t\t\t\t\t\t<textarea cols=\"50\" rows=\"4\" name=\"new_value\"></textarea>\n";
		echo "\t\t\t\t\t\t<button type=submit name=submit>Add Note</button>\n";
		echo "\t\t\t\t\t\t<a href=\"tag_details.php?asset=".$asset_row['assetid']."\"><button type=button>Cancel</button></a>\n";
		echo "\t\t\t\t\t</form>\n";
		echo "\t\t\t\t</td>\n";
		echo "\t\t\t</tr>\n\n";
	} 

	// Query for notes

	$query = "SELECT * FROM asset_notes_tbl WHERE assetid='".$asset_row['assetid']."' ORDER BY datetime DESC";

	$notes_result = pg_query($query);
	if (!$notes_result) {
			echo "<pre>Problem with query: " . $query . "\n\n";
			echo pg_last_error();
			echo "</pre>";
			exit();
	}

	// List notes, if any
	if ( pg_num_rows($notes_result) ) {
		while($note_row = pg_fetch_assoc($notes_result)) {
			echo "\t\t\t<tr>\n";
			echo "\t\t\t\t<td class=note-date-col>";
			echo date( 'n/d/Y g:i A' , strtotime($note_row['datetime']) );
			echo "</td>\n";
			// I'm thinking about just making this a full column instead of a little icon.
			echo "\t\t\t\t<td class=note-signature-col>";
			if ($note_row['signature']) {
				echo " <img src=note.gif title=\"Note added by: " . $note_row['signature'] . "\">";
			} else {
				echo "&nbsp;";
			}
			echo "</td>\n";
			echo "\t\t\t\t<td class=\"notetext\">" . $note_row['note'] . "</td>\n";
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
