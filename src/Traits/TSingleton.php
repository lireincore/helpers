<?php

namespace LireinCore\Helpers\Traits;

trait TSingleton
{
    /**
     * @var static
     */
    protected static $instance = null;

    protected function __construct() {}
    protected function __clone() {}
    protected function __sleep() {}
    protected function __wakeup() {}

    final public static function getInstance()
    {
        if(static::$instance === null){
            static::$instance = new static;
            static::$instance->init();
        }
        return static::$instance;
    }

    public function init(){}
}