<?php

/**
 * Created by PhpStorm.
 * User: Piotr
 */

use library\PigFramework\Router;

/**
 * Class App
 */
class App
{
    public function run()
    {
        $baseUrl = $_SERVER['HTTP_HOST'] . '/';

        $length = strpos($_SERVER['REQUEST_URI'], '?');
        $url = $length > 0 ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $_SERVER['REQUEST_URI'];

        $router = new Router($baseUrl);
        $router->route($url);
    }
}