<?php 
if ($_SERVER["REQUEST_URI"] === "/") { 
	$resource = "/index.html"; 
} else { 
	$resource = $_SERVER["REQUEST_URI"]; 
} 
$ext = pathinfo($resource, PATHINFO_EXTENSION); 
if ($ext === "html") { 
	header("Content-Type: text/html"); 
} else if ($ext === "css") { 
	header("Content-Type: text/css"); 
} else if ($ext === "js") { 
	header("Content-Type: application/javascript"); 
} else if ($ext === "json") { 
	header("Content-Type: application/json; charset=utf-8"); 
	header("Access-Control-Allow-Origin: *"); 
} else { 
	header("Content-Type: text/html"); 
} 
if (!file_exists("username.txt")) {
	$username = !empty($_GET["username"]) ? strtolower($_GET["username"]) : false;
	if ($username === false) {
		echo "<h1>Initial Setup: Add a ?username=your_username after this script (Example ?username=dalton). It's all fun and games until your custom domain doesn't work for your account :p Make sure you enter your 1mbsite username correctly.</h1>"
	} else {
		file_put_contents("username.txt", $username);
		echo "<h1>Success! This domain will now proxy traffic for https://$username.1mb.site</h1>";
	}
} else {
	echo file_get_contents("https://$username.1mb.site" . $resource); 
}
?>