<?php
	session_start(); // Ready the system for passing info between pages.

	echo "<!DOCTYPE html>\n";

	include("functions.php");
	
	html_header('BATS - Main Menu','');
	
	echo "\t\t<h1 id=\"index-banner\">BATS - Main Menu</h1>\n";
		
	if (isset($_SESSION['username'])) {
?>		
		<div class="group">
		<div id="asset-links">
			<ul>
				<li><a href="asset_list.php">Asset List</a></li>
				<li><a href="pc_list.php">Computers</a></li>
				<li><a href="new_asset.php">Add Asset to Database</a></li>
				<li><a href="multi_decom.php">Decommission Assets</a></li>
				<li>
					<FORM method="post" action="tag_details.php">
						Jump to Asset#: <INPUT class="sticker" type="text" name="tag_number" size="5"> <INPUT class="button" type="submit" value="&#10140;">
					</FORM>
				</li>
			</ul>
		</div>

		<div id="user-links">
			<ul>
				<li><a href="user_list.php">User List</a></li>
				<li><a href="user_list.php?add='t'">Add a user</a></li>
			</ul>
		</div>
		</div>
		
		<div id=\"log-out\">
			<ul>
				<li><a href="logout.php">Logged in as: <?php $_SESSION['username'] ?><br>Click here to logout.</a></li>
			</ul>
		</div>
<?php
	} else {
?>
		<div id="log-in-blurb">
			<p>This is a demonstration of BATS, a Basic Asset Tracking System developed by Geoffrey Emerson for tracking IT assets in a small to medium sized company.</p>
			<p>To tour the system, start by clicking "Log in".</p>
		</div>
		
		<div id="log-in">
			<ul>
				<li><a href=login.php>Log in</a></li>
			</ul>
		</div>
<?php
	}
?>
		
	</body>
</html>
