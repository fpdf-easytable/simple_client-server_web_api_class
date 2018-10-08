<?php
include 'API_client.php';

session_start();

if(!isset($_SESSION)){
	session_start();
}
//$_SESSION['s']=12;
//




$API=new API_Client();

$result=$API->Resquest('Demo.get_data', array('size'));
var_dump($result);


?>