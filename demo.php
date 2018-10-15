<?php
include 'API_client.php';

session_start();

if(!isset($_SESSION)){
	session_start();
}


$API_SERVER_URL='http://localhost/API/API_Server/API_Server.php';

$API=new API_Client($API_SERVER_URL);

$result=$API->Resquest('Demo.get_data', array('size'));
var_dump($result);


?>