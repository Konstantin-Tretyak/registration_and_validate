<?php
//   Something like error_log($_SERVER["REQUEST_URI"]);
error_reporting(E_ALL);
ini_set('display_errors',1);

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new Exception("PHP error: $errstr");
}
set_error_handler("exception_error_handler");

session_start();

require __DIR__.'/../app.php';

$response = app_run();

$code = isset($response['code']) ? $response['code'] : 200;
http_response_code($code);

if (isset($response['headers'])) {
    foreach ($response['headers'] as $header => $value) {
        header("$header: $value");
    }
}

if (isset($response['body'])) {
    echo $response['body'];
}