<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Startup Rules
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
error_reporting(0);
ini_set('display_errors', 0);

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Credits
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// Maintained for 1mbsite by Anthony Rossbach


//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Future stuff
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//API for resources api.1mb.site/?action=resources&site=dalton


//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Functions
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
function has_ssl($domain) {
	//Function that when given a domain will validate if it has a SSL certificate
	$res = false;
	$orignal_parse = $domain;
	$stream = @stream_context_create( array( 'ssl' => array( 'capture_peer_cert' => true ) ) );
	$socket = @stream_socket_client( 'ssl://' . $orignal_parse . ':443', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $stream );

	// If we got a ssl certificate we check here, if the certificate domain
	// matches the website domain.
	if ( $socket ){
		$cont = stream_context_get_params( $socket );
		$cert_ressource = $cont['options']['ssl']['peer_certificate'];
		$cert = openssl_x509_parse( $cert_ressource );
		$listdomains=explode(',', $cert["extensions"]["subjectAltName"]);

		foreach ($listdomains as $v) {
			if (strpos($v, $orignal_parse) !== false) {
				$res=true;
			}
		}
	}
	return $res;
}
function canmakechange(){
	if (file_exists("proxy_authtoken.ruleconf")){
		$proxcode=file_get_contents("proxy_authtoken.ruleconf");
		$oldtoken = !empty($_GET["authtoken"]) ? strtolower($_GET["authtoken"]) : false;
		if ($oldtoken==$proxcode){
			return true;
		}else{
			return false;
		}
	}else{
		return true;
	}
}
function curlResponseHeaderCallback($ch, $headerLine) {
    if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1){
		if (strpos($cookie[1], 'flarum') !== false) {
			$cookiestring=$cookie[1];
			$pieces = explode("=", $cookiestring);
			setcookie($pieces[0], $pieces[1], time() + (86400 * 7), "/", ".nodehost.ca");
		}
	}
	return strlen($headerLine);
}

function isJson($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
}

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Convert old files
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
if (file_exists("username.txt")){
	$usernamemove=file_get_contents("username.txt");
	file_put_contents("proxy_username.ruleconf", $usernamemove);
	unlink("username.txt");
}
if (file_exists("version.txt")){
	$versionmove=file_get_contents("version.txt");
	file_put_contents("proxy_version.ruleconf", $versionmove);
	unlink("version.txt");
}

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Custom URL content
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
if (isset($_GET["rules"])){
	//Custom rules selector for 1mbsite panel
	header("Content-Type: text/plain");
	if ($_GET["rules"]=="ssl"){
		if (has_ssl($_SERVER['HTTP_HOST'])==true){
			echo "true";
		}else{
			echo "false";
		}
	}
	exit();
}

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Custom API
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
if (isset($_GET["api"])){
	//Custom rules selector for 1mbsite panel
	header("Content-Type: text/plain");
	$return="error";

	if (canmakechange()==true){
		if ($_GET["api"]=="check_active_ssl"){
			if (has_ssl($_SERVER['HTTP_HOST'])==true){
				$return="true";
			}else{
				$return="false";
			}
		}
		if ($_GET["api"]=="get_proxy_version"){
			if (!file_exists("proxy_version.ruleconf")){
				$return="1.0.0";
			}else{
				$return=file_get_contents("proxy_version.ruleconf");
			}
		}
		if ($_GET["api"]=="get_proxy_username"){
			if (!file_exists("proxy_username.ruleconf")){
				$return="false";
			}else{
				$return=file_get_contents("proxy_username.ruleconf");
			}
		}
		if ($_GET["api"]=="get_proxy_rules_wwwredirect"){
			if (!file_exists("proxy_rules_wwwredirect.ruleconf")){
				$return="false";
			}else{
				$return=file_get_contents("proxy_rules_wwwredirect.ruleconf");
			}
		}
		if ($_GET["api"]=="get_proxy_rules_cache"){
			if (!file_exists("proxy_rules_cache.ruleconf")){
				$return="true";
			}else{
				$return=file_get_contents("proxy_rules_cache.ruleconf");
			}
		}
		if ($_GET["api"]=="get_proxy_authtoken"){
			if (!file_exists("proxy_authtoken.ruleconf")){
				$return="false";
			}else{
				$return="true";
			}
		}
		if ($_GET["api"]=="set_proxy_username"){
			if (!file_exists("proxy_username.ruleconf")){
				$username = !empty($_GET["username"]) ? strtolower($_GET["username"]) : false;
				file_put_contents("proxy_username.ruleconf", $username);
				$return="set";
			}else{
				$return="alreay_set";
			}
		}
		if ($_GET["api"]=="set_proxy_authtoken"){
			if (!file_exists("proxy_authtoken.ruleconf")){
				$token = !empty($_GET["token"]) ? strtolower($_GET["token"]) : false;
				file_put_contents("proxy_authtoken.ruleconf", $token);
				$return="set";
			}else{
				$proxcode=file_get_contents("proxy_authtoken.ruleconf");
				$oldtoken = !empty($_GET["oldtoken"]) ? strtolower($_GET["oldtoken"]) : false;
				if ($oldtoken==$proxcode){
					$token = !empty($_GET["token"]) ? strtolower($_GET["token"]) : false;
					file_put_contents("proxy_authtoken.ruleconf", $token);
					$return="updated";
				}else{
					$return="authfail";
				}
			}
		}
		if ($_GET["api"]=="set_proxy_rules_wwwredirect"){
			$rule = !empty($_GET["rule"]) ? strtolower($_GET["rule"]) : false;
			file_put_contents("proxy_rules_wwwredirect.ruleconf", $rule);
			$return="set";
		}
		if ($_GET["api"]=="set_proxy_rules_cache"){
			$rule = !empty($_GET["rule"]) ? strtolower($_GET["rule"]) : false;
			file_put_contents("proxy_rules_cache.ruleconf", $rule);
			$return="set";
		}
	}else{
		$return="authfail";
	}
	echo $return;
	exit();
}

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Check for username file
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
if (!file_exists("proxy_username.ruleconf")){
	//If the username is not present then give the user a message, else save and redirect to site root
	$username = !empty($_GET["username"]) ? strtolower($_GET["username"]) : false;
	if ($username === false) {
		echo "<html><head></head><body><h1 style='text-align:center;margin-top:150px;'>This custom domain is not setup, click the link on the 1MB dashboard to finish setup.</h1></body></html>";
		exit();
	} else {
		file_put_contents("proxy_username.ruleconf", $username);
		echo "<h1>Success! This domain will now proxy traffic for https://$username.1mb.site</h1>";
		header("Location: https://".$_SERVER['HTTP_HOST']."");
		exit();
	}
}

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Build links and settings
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
$link_request=$_SERVER[REQUEST_URI];
if ($link_request=="/"){
	$link_request="";
}
$mydomain=str_replace("www.", "", $_SERVER['HTTP_HOST']);
$username=file_get_contents("proxy_username.ruleconf");
$cachecode=sha1($_SERVER[REQUEST_URI]);
$httpprefix="http://";
$source=''.$username.'.1mb.site';
$usecache=false;
$url = 'https://'.$username.'.1mb.site' . $link_request;

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ HTTPS redirect
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//Auto redirect to HTTPS first if not already using HTTPS
//AKA If the domain we are on now has a valid certificate we should send the user to the HTTPS version of our site...
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
	if (has_ssl($_SERVER['HTTP_HOST'])==true){
		//Only redirect if we have a valid ssl cert first
		$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
}else{
	$httpprefix="https://";
}

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Redirect away from www
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
if ($mydomain!=$_SERVER['HTTP_HOST']){
	if (file_exists("proxy_rules_wwwredirect.ruleconf")){
		$ruleis=file_get_contents("proxy_rules_wwwredirect.ruleconf");
		if ($ruleis=="true"){
			$redirect = 'https://' . $mydomain . $_SERVER['REQUEST_URI'];
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $redirect);
			exit();
		}
	}
}

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Check for cache version first
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
$allowcache=true;
if (file_exists("proxy_rules_cache.ruleconf")){
	$ruleis=file_get_contents("proxy_rules_cache.ruleconf");
	if ($ruleis=="false"){
		$usecache=false;
		$allowcache=false;
		header("1mbproxy-cache-time: disabled");
	}
}

if (file_exists("cache_static_".$cachecode."_timestamp.txt")){
	if ($allowcache==true){
		$cachetime=file_get_contents("cache_static_".$cachecode."_timestamp.txt");
		$checktime=(time()-(60*5));
		if ($checktime<=$cachetime){
			//Cache is good to use
			$usecache=true;
			$timeleft=$cachetime-$checktime;
			header("1mbproxy-cache-time: ".$timeleft."");
			header("1mbproxy-cache-archive: ".$cachetime."");
			header("1mbproxy-cache-now: ".$checktime."");
		}else{
			//Nope cache expired
			$usecache=false;
			header("1mbproxy-cache-time: refresh");
		}
	}
}

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Main script starts
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

if ($usecache==false){
	//######################################
	//###################################### Load fresh version
	//######################################

	//Send request, with the current visitors useragent
	$ch = curl_init();
	$headerarry=array();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION, "curlResponseHeaderCallback");
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); //Wait 2 seconds to connect
	curl_setopt($ch, CURLOPT_TIMEOUT, 8); //If cant process in 8 seconds its a timeout, our connection is 1GBPS so no problem should happen

	//If this reuqest is a post request forward on the POST data
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
	}

	//Check and set AUTHTOKEN
	if (file_exists("proxy_authtoken.ruleconf")){
		$token=file_get_contents("proxy_authtoken.ruleconf");
		array_push($headerarry,"X-AUTHTOKEN: ".$token."");
		header("Did-Auth-Token: YES");
	}else{
		header("Did-Auth-Token: NO");
	}

	array_push($headerarry,"X-PROXY-REFNH: true");

	if (!empty($headerarry)){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerarry);
	}

	//Run request and get response
	$response = curl_exec($ch);
	$type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

	//Check type here and change URL to new one for links and source content!
	if (strpos($type, 'text/html') !== false || strpos($type, 'text/css') !== false || strpos($type, 'application/javascript') !== false){
		$response = str_replace("https://".$source, "//".$_SERVER['HTTP_HOST']."", $response);
		$response = str_replace("http://".$source, "//".$_SERVER['HTTP_HOST']."", $response);
	}

	//Check if response failed, if so give the user a error message
	if (curl_error($ch)){
		if (!file_exists("cache_realtime_".$cachecode."_content.txt")){
			//No cache kill page
			header("1mbproxy-Cache: false");
			header("Content-Type: text/html");
			echo "<html><head></head><body><h1 style='text-align:center;margin-top:150px;'>We are unable to run this requst</h1></body></html>";
			exit();
		}else{
			//We have a cache version load it!
			header("1mbproxy-Cache: true");
			$content=file_get_contents("cache_realtime_".$cachecode."_content.txt");
			$type=file_get_contents("cache_realtime_".$cachecode."_type.txt");
			header("Content-type: $type");
			echo $content;
			exit();
		}
	}else{
		//No error we can save the resource
		//Set browser content type for the file and send that along so it renders as it should
		header("Content-type: $type");
		if (!file_exists("cache_realtime_".$cachecode."_content.txt")){
			header("1mbproxy-cache: saved");
		}else{
			header("1mbproxy-cache: updated");
		}

		//save realtime version first
		file_put_contents("cache_realtime_".$cachecode."_content.txt", $response);
		file_put_contents("cache_realtime_".$cachecode."_type.txt", $type);


		//save static version
		if (strpos($type, 'text/html') === false){
			file_put_contents("cache_static_".$cachecode."_content.txt", $response);
			file_put_contents("cache_static_".$cachecode."_type.txt", $type);
			file_put_contents("cache_static_".$cachecode."_timestamp.txt", time());
		}

	}

	//Close connection and paste response
	curl_close($ch);
	echo $response;

}else{
	//######################################
	//###################################### Load cache version
	//######################################
	header("1mbproxy-cache: true");
	$content=file_get_contents("cache_static_".$cachecode."_content.txt");
	$type=file_get_contents("cache_static_".$cachecode."_type.txt");
	header("Content-type: $type");
	echo $content;

}

?>
