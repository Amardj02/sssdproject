<?php
require_once "../api/Controller.php";


Flight::route('POST /register', function () {

    $controller = new sssd\Controller();
    $controller->register();

});

Flight::route('POST /login', function () {

    $controller = new sssd\Controller();
    $controller->login();

});
Flight::route('POST /entertwofactormethodcode', function () {

    $controller = new sssd\Controller();    
    $controller->entertwofactormethodcode();

});