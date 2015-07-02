<?php

	require_once("functions.php");
	html_header('BATS - Consoles View','');
	include("top_menu.php");
	
	connect_to_db();

	// Get sort variables
	if ( isset($_GET["sort"]) ) { 
		$sort_by = $_GET["sort"]; 
	} else {
		$sort_by = "department";
	}
	if ( isset($_GET["order"]) ) { 
		$order = $_GET["order"];
	} else {
		$order = "asc";
	}

	
	echo "<h1>List of Consoles by Department</h1>\n";
	echo "<p>\n";


// Wii
	echo "<h3>Wii Consoles</h3>\n";

	$query = "	SELECT * FROM asset_tbl a
			LEFT JOIN (
				SELECT DISTINCT ON (assetid_fkey) * FROM assignment_tbl 
				LEFT JOIN user_tbl ON (userid_fkey=userid)
				ORDER BY assetid_fkey, datetime desc
			) AS usernames
			ON assetid=assetid_fkey
			WHERE (a.active='t' OR a.active isnull) AND description LIKE 'Wii%'
			ORDER BY $sort_by $order, description asc, firstname asc, lastname asc";


	$result = safe_query($query);
	
	// Create data table

	echo "<table>\n";
	echo "<thead>";	
	echo "<tr>";

	// Column headings
	echo "<th>Sticker #</th>";
	echo "<th>Description</th>";
	echo "<th>Assigned To</th>";
	echo "<th>Department</th>";

	echo "</tr>\n";
	echo "</thead>";	
	echo "<tr>";

	$row_count = 0;
	$lastrow_dept = "";
	$cell = '<td> %s </td>';

	while($asset = pg_fetch_assoc($result)) {
		
		//Sum up dept totals
		if ( ($lastrow_dept != $asset['department']) and ($row_count!=0) ) {
			echo "<td>$lastrow_dept: $row_count</td></tr><tr><th colspan=4></th></tr>\n";
			$row_count = 0;
		} else {
			echo "</tr>\n";
		}
		echo "<td>";
		if (!$asset['sticker']) {
			echo "<a href=\"tag_details.php?asset=".$asset['assetid']."\">?</td>";
		} else {
			echo "<a href=\"tag_details.php?asset=".$asset['assetid']."\">".$asset['sticker']."</td>";
		}
		echo "<td class=left>".$asset['description']."</td>";
		printf($cell,$asset['firstname']." ".$asset['lastname']);
		printf($cell,$asset['department']);
		$lastrow_dept = $asset['department'];
		$row_count = $row_count+1;
	}
	echo "<td>$lastrow_dept: $row_count</td></tr>\n";
	echo "</tr>\n";
	echo "</table>\n\n";

// Xbox 360

	echo "<h3>Xbox 360 Consoles</h3>\n";

	$query = "	SELECT * FROM asset_tbl a
			LEFT JOIN (
				SELECT DISTINCT ON (assetid_fkey) * FROM assignment_tbl 
				LEFT JOIN user_tbl ON (userid_fkey=userid)
				ORDER BY assetid_fkey, datetime desc
			) AS usernames
			ON assetid=assetid_fkey
			WHERE (a.active='t' OR a.active isnull) AND description LIKE 'Xbox 360%'
			ORDER BY $sort_by $order, department asc, description asc, firstname asc, lastname asc";


	$result = safe_query($query);
	
	// Create data table

	echo "<table>\n";
	echo "<thead>";
	echo "<tr>";

	// Column headings
	echo "<th>THQ #</th>";
	echo "<th>Description</th>";
	echo "<th>Assigned To</th>";
	echo "<th>Department</th>";
	echo "</tr>\n";
	echo "</thead>";

	echo "<tr>";
	$row_count = 0;

	while($asset = pg_fetch_assoc($result)) {
		
		//Sum up dept totals
		if ( ($lastrow_dept != $asset['department']) and ($row_count!=0) ) {
			echo "<td>$lastrow_dept: $row_count</td></tr><tr><th colspan=4></th></tr>\n";
			$row_count = 0;
		} else {
			echo "</tr>\n";
		}
		echo "<td>";
		if (!$asset['sticker']) {
			echo "<a href=\"tag_details.php?asset=".$asset['assetid']."\">?</td>";
		} else {
			echo "<a href=\"tag_details.php?asset=".$asset['assetid']."\">".$asset['sticker']."</td>";
		}
		echo "<td class=left>".$asset['description']."</td>";
		printf($cell,$asset['firstname']." ".$asset['lastname']);
		printf($cell,$asset['department']);
		$lastrow_dept = $asset['department'];
		$row_count = $row_count+1;
	}
	echo "<td>$lastrow_dept: $row_count</td></tr>\n";
	echo "</tr>\n";
	echo "</table>\n\n";

// PS3

	echo "<h3>PS3 Consoles</h3>\n";

	$query = "	SELECT * FROM asset_tbl a
			LEFT JOIN (
				SELECT DISTINCT ON (assetid_fkey) * FROM assignment_tbl 
				LEFT JOIN user_tbl ON (userid_fkey=userid)
				ORDER BY assetid_fkey, datetime desc
			) AS usernames
			ON assetid=assetid_fkey
			WHERE (a.active='t' OR a.active isnull) AND description LIKE 'PS3%' AND type = 'Console'
			ORDER BY $sort_by $order, department asc, description asc, firstname asc, lastname asc";


	$result = safe_query($query);
	
	// Create data table

	echo "<table class=noframe>\n";
	echo "<thead>";
	echo "<tr>";

	// Column headings
	echo "<th>Sticker #</th>";
	echo "<th>Description</th>";
	echo "<th>Assigned To</th>";
	echo "<th>Department</th>";
	echo "</tr>\n";
	echo "</thead>";

	echo "<tr>";
	$row_count = 0;

	while($asset = pg_fetch_assoc($result)) {
		
		//Sum up dept totals
		if ( ($lastrow_dept != $asset['department']) and ($row_count!=0) ) {
			echo "<td>$lastrow_dept: $row_count</td></tr><tr><th colspan=4></th></tr>\n";
			$row_count = 0;
		} else {
			echo "</tr>\n";
		}
		echo "<td>";
		if (!$asset['sticker']) {
			echo "<a href=\"tag_details.php?asset=".$asset['assetid']."\">?</td>";
		} else {
			echo "<a href=\"tag_details.php?asset=".$asset['assetid']."\">".$asset['sticker']."</td>";
		}
		echo "<td class=left>".$asset['description']."</td>";
		printf($cell,$asset['firstname']." ".$asset['lastname']);
		printf($cell,$asset['department']);
		$lastrow_dept = $asset['department'];
		$row_count = $row_count+1;
	}
	echo "<td>$lastrow_dept: $row_count</td></tr>\n";
	echo "</tr>\n";
	echo "</table>\n\n";
?>

		</table>
	</body>
</html>
