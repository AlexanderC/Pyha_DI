<?php

/**
 * Singleton pattern trait
 * 
 * @author AlexanderC <alexander.moldova@gmail.com>
 */

namespace Pyha\Helpers\Traits;

trait Singleton
{
    /**
     * @var object|NULL
     */
    private static $__self;

    /**
     * method called after parent object
     * instantiation with primary input
     * parameters provided
     */
    protected function _onAfterConstruct()
    {
    }

    /**
     * assure no clone
     */
    final private function __clone()
    {

    }

    /**
     * Assure no constructor
     */
    final private function __construct()
    {
    }

    /**
     * Get object instance
     *
     * @return \stdClass
     */
    final public static function getInstance()
    {
        if (!(static::$__self instanceof static)) {
            static::$__self = new static;
            call_user_func_array([static::$__self, "_onAfterConstruct"], func_get_args());
        }

        return static::$__self;
    }

    /**
     * called when trying to access inaccessible static methods
     * 
     * @param string $name
     * @param array $params
     * @return mixed
     */
    final public static function __callStatic($name, array $params)
    {
        return call_user_func_array([static::getInstance(), $name], $params);
    }

}
