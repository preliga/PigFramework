<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 2018-03-31
 * Time: 17:17
 */

namespace library\PigFramework\model;

class Registry
{
    /**
     * @var Registry
     */
    private static $instance;

    private static $objects = [];

    private $registryPath;

    public function __construct($registryPath)
    {
        $this->registryPath = $registryPath;
    }

    public static function getInstance(): Registry
    {
        if (empty(self::$instance)) {
            self::$instance = new self(Config::getInstance()->getConfig('registryPath'));
        }

        return self::$instance;
    }

    public function __get($name)
    {
        if (empty(self::$objects[$name])) {

            $file = "{$this->registryPath}{$name}.php";
            if (file_exists($file)) {
                self::$objects[$name] = include($file);
            } else {
                die(var_dump("Brak pliku: $file"));
            }

        }

        return self::$objects[$name];
    }
}