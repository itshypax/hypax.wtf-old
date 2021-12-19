<?php
// Include the configuration file
include_once 'config.php';
// Connect to MySQL with PDO function
function pdo_connect_mysql() {
    // Update the details below with your MySQL details
    try {
        $pdo = new PDO('mysql:host=' . db_host . ';dbname=' . db_name . ';charset=' . db_charset, db_user, db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $exception) {
    	// If there is an error with the connection, stop the script and output the error.
    	exit('Failed to connect to database!');
    }
    return $pdo;
}
// Template header, feel free to customize this
function template_header($title) {
echo <<<EOT
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>$title</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<script src="https://kit.fontawesome.com/a6a8f17a8d.js" crossorigin="anonymous"></script>
	</head>
	<body>
    <nav class="navtop">
    	<div>
    		<h1>Voting &amp; Poll System</h1>
            <a href="admin/login.php"><i class="fas fa-user-crown"></i>Einloggen</a>
    	</div>
    </nav>
EOT;
}
// Template footer
function template_footer() {
echo <<<EOT
    </body>
</html>
EOT;
}
?>
