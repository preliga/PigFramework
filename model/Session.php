<?php

/**
 * Created by PhpStorm.
 * User: Piotr
 */


namespace library\PigFramework\model;

/**
 * Class Session
 * @package library\Pig\model
 */
class Session
{
    protected static $instance;

    /**
     * @return Session
     */
    public static function getInstance(): Session
    {
        if (empty(self::$instance)) {
            self::$instance = new Session();
        }

        return self::$instance;
    }

    /**
     * Session constructor.
     */
    public function __construct()
    {
        session_start();
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $_SESSION[$name] ?? null;
    }

    /**
     *
     */
    public function __destruct()
    {
        session_destroy();
    }
}

