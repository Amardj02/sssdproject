<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require '../vendor/autoload.php';

require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../routes/UserRoutes.php';

Flight::register('userservice', "UserService");
Flight::register('userdao', "UserDao");


Flight::route('/', function () {
    echo 'hello world!';
});

Flight::start();

