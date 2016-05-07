<?php
$endpoint = $wallabag_url . '/oauth/v2/token';
$params = array(
	"client_id" => "$wallabag_client_id",
	"client_secret" => "$wallabag_client_secret",
	"username" => "$wallabag_username",
	"password" => "$wallabag_password",
	"grant_type" => "password");
$query = http_build_query ($params);
$contextData = array ( 
		'method' => 'POST',
		'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
		    	    "Content-Length: ".strlen($query)."\r\n".
		    	    "User-Agent:TTRss/1.0\r\n",
		'content'=> $query );
$context = stream_context_create (array ( 'http' => $contextData ));
$result =  file_get_contents (
		  $endpoint,
		  false,
		  $context);
// Is there a better way to isolate this from the ugly string returned from Wallabag?
$wallabag_access_token = substr($result, 17, 86);
// Uncomment the next line in order to expose the refresh token.
// $refresh_token = substr($result, 175, 86);
// Set the api endpoint for use later
$wallabag_api = $wallabag_url . '/api/entries.json';
?>
