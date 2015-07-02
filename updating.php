<?php

	require_once("functions.php");

	// Get passed data
	if ( isset($_POST['pass_table']) ) $current_tbl = $_POST['pass_table'];
	if ( isset($_POST['pass_assetid']) ) $current_asset = $_POST['pass_assetid'];
	if ( isset($_POST['pass_field']) ) $current_field = $_POST['pass_field'];
	if ( isset($_POST['pass_currentid']) ) $current_userid = $_POST['pass_currentid'];
	if ( isset($_POST['new_value']) ) $new_value = $_POST['new_value'];

	// Check assetid type (depends on table)
	if ($current_tbl == 'pc_detail_tbl') {
		$asset_identifier = 'pc_assetid';
	} elseif ($current_tbl == 'user_tbl') {
		$asset_identifier = 'userid';
	} else {
		$asset_identifier = 'assetid';
	}

	// Connect to DB
	connect_to_db();

	// Check for asset assignment change
	if ($current_tbl == 'assignment_tbl') {

		// Prevent redundant entries
		if ( $new_value == $current_userid ) {
			header( "Location: tag_details.php?asset=$current_asset&field=assign" );
		}

		$query = "INSERT INTO $current_tbl (datetime,assetid_fkey,userid_fkey) VALUES ('".date('m/d/Y h:i:s A')."',$current_asset,'$new_value')";
		$result = pg_query($query);
		query_error_check($result, $query);
	
		// Change asset status depending on movement
		if ( $current_userid == "1" ) {
			$query = "UPDATE asset_tbl SET status = 'Active' WHERE assetid ='$current_asset'";
			$result = pg_query($query);
			query_error_check($result, $query);
		}
		if ( $new_value == "1" ) {
			$query = "UPDATE asset_tbl SET status = 'Unknown' WHERE assetid ='$current_asset'";
			$result = pg_query($query);
			query_error_check($result, $query);
		}
		// Default after assignment change, return to original page
		header( "Location: tag_details.php?asset=$current_asset&field=status" );

		// Script ends here for Assignment changes.
		
	} else {
		
		// This section is for non-assignment database changes, such as asset_tbl, pc_detail_tbl, console_detail_tbl, user_tbl.
		
		$query = "SELECT * FROM $current_tbl WHERE $asset_identifier = $current_asset";
		$result = pg_query($query);

		// Create a new subtable entry for assets that are not yet in the table
		if (pg_num_rows($result) == 0) {
			$query = "INSERT INTO $current_tbl ($asset_identifier) VALUES ($current_asset)";
			$result = pg_query($query);
			query_error_check($result, $query, "Failed to create new asset");
		}

		// Check for duplication in key fields
		if ($current_field == 'sticker') {
			$query = "SELECT * FROM $current_tbl WHERE $current_field='$new_value'";
			$result = safe_query($query);
			query_error_check($result, $query, "Problem checking for duplicates");
		}

		if ( ($current_field != 'sticker') || (pg_num_rows($result) == 0) ){
			// If there are no duplicates in key fields, proceed

			// Check for a NULL value, and use the appropriate UPDATE query
			if (!$new_value) {$query = "UPDATE $current_tbl SET $current_field=NULL WHERE $asset_identifier = $current_asset";}
			            else {$query = "UPDATE $current_tbl SET $current_field='$new_value' WHERE $asset_identifier = $current_asset";}
			
			// Run update query
			$result = pg_query($query);
			query_error_check($result, $query, "Failed to update value");

			// Check for linked fields
			if ( ($current_tbl=="asset_tbl") && ($current_field=="active") && ($new_value=="f") ) {
				// Set status to Decomissioned
				$query = "UPDATE asset_tbl SET status='Decomissioned' WHERE assetid = $current_asset";
				$result = pg_query($query);
				query_error_check($result, $query, "Failed to set status to Decomissioned");

				// Assign to user "None"
				$query = "INSERT INTO assignment_tbl (datetime,assetid_fkey,userid_fkey) VALUES ('".date('m/d/Y h:i:s A')."',$current_asset,'199')";
				$result = pg_query($query);
				query_error_check($result, $query, "Failed to assign to user None");
			}

			// After a successful update, redirect to detail page
			if ($current_tbl == "user_tbl") {
				header( "Location: user_details.php?user=$current_asset&field=note" );
			} else {
				header( "Location: tag_details.php?asset=$current_asset&field=note" );
			}

		} else {
			// If there is a duplicate found
			html_header('HI Asset DB - Duplicate','');
			include("top_menu.php");
			echo "<h2>Duplicate found</h2>\n";
			echo "<p>Problem updating. " . display_format($current_field) . " $_POST[new_value] already exists.</p>\n";
			if ($current_tbl == "user_tbl") {
				echo "<p><a href=user_details.php?user=$current_asset&field=note>Click here to return</a></p>";
			} else {
				echo "<p><a href=tag_details.php?asset=$current_asset&field=note>Click here to return</a></p>";
			}
			echo "</pre></body></html>\n";
		}
	}
?>

