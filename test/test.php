<?php

require_once "../vendor/autoload.php";

$guzzler = new cvweiss\Guzzler();
$guzzler->call("https://example.org", "success", "failure");
$guzzler->finish();

function success($guzzler, $params, $content)
{
    echo "$content\n";
}

function failure($guzzler, $params, $exception)
{
    print_r($exception);
}
