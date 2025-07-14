<?php 
session_start();

$json = file_get_contents('conf.config');
$config = json_decode($json, true);
?>