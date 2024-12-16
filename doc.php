<?php
require("vendor/autoload.php");
$openapi = \OpenApi\Generator::scan([ dirname(  __FILE__ ).'/api']);
header('Content-Type: application/json');
echo $openapi->toJson();