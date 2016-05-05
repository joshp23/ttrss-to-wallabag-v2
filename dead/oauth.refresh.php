<?php
$endpoint = '$wallabag_url' + 'oauth/v2/token';	
$refresh_params = array(
	"client_id" => "$wallabag_client_id",
	"client_secret" => "$wallabag_client_secret",
	"refresh_token" => "$refresh_token",
	"grant_type" => "refresh_token");
 
$refresh_query = http_build_query ($refresh_params);
 
$refresh_contextData = array ( 
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
                    	    "Content-Length: ".strlen($refresh_query)."\r\n".
                    	    "User-Agent:MyAgent/1.0\r\n",
                'content'=> $refresh_query );
 
$refresh_context = stream_context_create (array ( 'http' => $refresh_contextData ));
 
$refresh_result =  file_get_contents (
                  "$endpoint",  // page url
                  false,
                  $refresh_context);
$wallabag_access_token = substr($refresh_result, 17, 86);
// $wallabag_refresh_token = substr($refresh_result, 175, 86);
?>
