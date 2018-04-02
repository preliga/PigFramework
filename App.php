<?php

/**
 * Created by PhpStorm.
 * User: Piotr
 */

namespace library\PigFramework;

use library\PigFramework\model\router\Routable;

/**
 * Class App
 */
class App
{
    /**
     * @var Routable
     */
    protected $router;

    public function __construct(Routable $router)
    {
        $this->router = $router;
    }

    public function run()
    {
        $length = strpos($_SERVER['REQUEST_URI'], '?');
        $url = $length > 0 ? substr($_SERVER['REQUEST_URI'], 0, $length) : $_SERVER['REQUEST_URI'];

        $this->router->route($url);
    }
}