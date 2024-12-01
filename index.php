<?php

declare(strict_types=1);

require "src/Controller.php";
require "src/db.php";
require "src/ErrorHandler.php";
require "src/Services.php";

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

header("Content-type: application/json; charset=UTF-8");

$parts = explode("/", $_SERVER["REQUEST_URI"]);

if($parts[1] != "testingapi") {
    http_response_code(404);
    exit;
}

$id = $parts[2] ?? NULL;

print_r($parts);


$database = new db("localhost", "product_db", "root", "");
$service = new Services($database);

$controller = new Controller($service);
$controller->proccessRequest($_SERVER["REQUEST_METHOD"], $id);

?>