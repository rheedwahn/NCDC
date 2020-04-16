<?php

require "vendor/autoload.php";

header("Access-Control-Allow-Origin: *");
header("HTTP/1.1 200 OK");
header("Content-Type: application/json; charset=UTF-8");

$response = (new \App\Services\ScrapNcdcWebsite())->run();

echo json_encode($response, JSON_PRETTY_PRINT);

