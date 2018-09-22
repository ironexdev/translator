<?php

use DI\ContainerBuilder;
use Ironex\Example\IndexController;

ini_set("error_reporting", E_ALL);
ini_set("display_errors", "On");

require __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

init();

/**
 * @throws Exception
 */
function init()
{
    $containerBuilder = new ContainerBuilder;
    $containerBuilder->useAutowiring(true);
    $containerBuilder->useAnnotations(true);
    $container = $containerBuilder->build();

    if(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === "/")
    {
        $container->call([IndexController::class, "renderDefault"]);
    }
    else
    {
        http_response_code(404);
    }
}