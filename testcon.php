<?php

require_once 'config_default.php';


$connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);


if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
} else {
    echo "Database connection successful!";
}


$connection->close();

