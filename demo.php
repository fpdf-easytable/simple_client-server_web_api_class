<?php
include 'API_client.php';

$result=$API->Resquest('Demo.get_data', array('size'));
var_dump($result);


?>