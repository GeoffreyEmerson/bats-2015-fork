<?php require_once("functions.php") ?>

		<header>
			<nav id=top-menu>
				<ul>
					<li><a href=index.php>BATS</a></li>
					<li><a href="asset_list.php">Assets</a></li>
					<li><a href="user_list.php">Users</a></li>
					<li><a href="console_by_dept.php">Views</a></li>
					<li><FORM method="post" action="tag_details.php">Jump to Sticker#:	<INPUT class="sticker" type="text" name="tag_number"  size="5"><INPUT type="submit" value="&#10140;"></FORM></li>
					<li>User: <?php session_check(); echo "{$_SESSION['username']}"; ?></li>
					<li><a href="logout.php">[Log out]</a></li>
				</ul>
			</nav>
		</header>

