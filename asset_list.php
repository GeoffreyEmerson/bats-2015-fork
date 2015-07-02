<?php	

	// Page timer start
	$stimer = explode( ' ', microtime() );
	$stimer = $stimer[1] + $stimer[0];

	require_once("functions.php");
	html_header('BATS - Asset List','');
	include("top_menu.php");
	
	connect_to_db();
	
	// Check for passed sort variables
	if ( isset($_GET["active"]) ) { $active = $_GET["active"]; } else { $active = 't'; }
	if ( isset($_GET["sort"]) ) { $sort_by = $_GET["sort"]; } else  { $sort_by = "sticker"; }
	if ( isset($_GET["order"]) ) { $order = $_GET["order"]; } else { $order = "desc"; }
	if ( isset($_GET["search"]) AND isset($_GET["key"]) ) {
		$query_field = $_GET["search"];
		$query_key = $_GET["key"];
		$search_string = "AND lower($query_field) = lower('$query_key')";
		$pass_array['search']=$query_field;
		$pass_array['key']=$query_key;
	} else {
		$search_string = "";
	}
	
	// Cannot use order() on non-text
	if ( ($sort_by == "assetid") OR ($sort_by == "sticker") OR ($sort_by == "purchasedate") ) {
		$sort_string = "$sort_by $order";
	} else {
		$sort_string = "lower($sort_by) $order";
	}
	
	$query = "SELECT * FROM asset_tbl	a
			LEFT JOIN (
				SELECT DISTINCT ON (assetid_fkey) * FROM assignment_tbl 
				LEFT JOIN user_tbl ON (userid_fkey=userid)
				ORDER BY assetid_fkey, datetime desc
			) AS usernames
			ON assetid=assetid_fkey
			WHERE (a.active='$active' OR a.active isnull) $search_string
			ORDER BY $sort_string";

	$pass_array['query']=$query;

//	$result = pg_query($query);
	$result = safe_query($query);	
	
// Create data table
	
	echo "\t\t<h1 id=\"asset-headline\">Asset List</h1>\n";

/*
	echo "<pre>Debug info:\n\nSort by: $sort_by \nOrder: $order</pre>\n\n";  // This section is can be uncommented to help debug a table display error
	echo "<pre>Query: ".$pass_array['query']."</pre>\n";
	echo "<pre>Search: ".$pass_array['search']."</pre>\n";
	echo "<pre>Key: ".$pass_array['key']."</pre>\n";
*/

	echo "\t\t<table class=\"main-table\">\n";
	echo "\t\t\t<thead class=\"main-thead\">\n";
	echo "\t\t\t\t<tr class=\"main-thead-row\">\n";

	// Display column names with sorting options
	column_head('asset_list', $pass_array, $sort_by, 'sticker', $order, 'desc');
	column_head('asset_list', $pass_array, $sort_by, 'type', $order, 'asc');
	column_head('asset_list', $pass_array, $sort_by, 'description', $order, 'asc');
	column_head('asset_list', $pass_array, $sort_by, 'serial', $order, 'asc');
	column_head('asset_list', $pass_array, $sort_by, 'purchasedate', $order, 'asc');
	column_head('asset_list', $pass_array, $sort_by, 'firstname', $order, 'asc');
	column_head('asset_list', $pass_array, $sort_by, 'department', $order, 'asc');

	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t</thead>\n";
	echo "\t\t\t<tbody class=\"main-tbody\">\n";

	$row_count = 0;

	while($asset = pg_fetch_assoc($result)) {

		$row_count = $row_count + 1;
		echo "\t\t\t\t<tr class=\"main-tbody-row\">\n";
		echo "\t\t\t\t\t<td class=\"main-tbody-cell\">";
		if (!$asset['sticker']) {
			echo "<a href=\"tag_details.php?asset=".$asset['assetid']."\">?</td>\n";
		} else {
			echo "<a href=\"tag_details.php?asset=".$asset['assetid']."\">".$asset['sticker']."</td>\n";
		}
		echo "\t\t\t\t\t<td class=\"main-tbody-cell\"><a href=\"asset_list.php?search=type&key=".htmlspecialchars($asset['type'])."\">".$asset['type']."</a></td>\n";
		echo "\t\t\t\t\t<td class=left><a href=\"asset_list.php?search=description&key=".htmlspecialchars($asset['description'])."\">".htmlspecialchars($asset['description'])."</a></td>\n";
		echo "\t\t\t\t\t<td>".$asset['serial']."</td>\n";
		echo "\t\t\t\t\t<td>".$asset['purchasedate']."</td>\n";
		if ( ($asset['firstname']=="") || ($asset['firstname'] == "IT") ) {
			echo "\t\t\t\t\t<td>".$asset['status']."</td>\n";
		} else { // When the asset is assigned to a standard user, combine first and last name into a single cell.
			echo "\t\t\t\t\t<td><a href=user_details.php?user=".$asset['userid'].">".$asset['firstname']." ".$asset['lastname']."</a></td>\n";
		}
		echo "\t\t\t\t\t<td><a href=\"asset_list.php?search=department&key=".htmlspecialchars($asset['department'])."\">".$asset['department']."</a></td>\n";
		echo "\t\t\t\t</tr>\n";
		
	}

	echo "\t\t\t</tbody>\n";
	echo "\t\t</table>\n\n";

	//Get page timer result
	$etimer = explode( ' ', microtime() );
	$etimer = $etimer[1] + $etimer[0];
	echo "\t\t<p style=\"margin-top:2em; text-align:left\">";
	echo "<b>" . $row_count . " rows retrieved.</b></p>\n";
	printf("\t\t<p>Page generated in <b>%f</b> seconds.</p>\n\n", ($etimer-$stimer) );

	//Mini-form for saving data to CSV
	echo "\t\t<p>\n";
	echo "\t\t\t<form name=\"form\" method=\"post\" action=\"".$_SERVER['REQUEST_URI']."\">\n";
	echo "\t\t\t\t<INPUT TYPE=hidden NAME=pass_select VALUE=\"" . $query ."\">\n";
	echo "\t\t\t\tSave to: <INPUT TYPE=text NAME=\"save_to\">\n";
	echo "\t\t\t\t<INPUT TYPE=submit VALUE=\"Save As CSV\">\n";
	echo "\t\t\t</form>\n";
	echo "\t\t</p>\n";

	
	echo "\t</body>\n";
	echo "</html>\n";
?>
	
