<?php
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