<?php

/**
 * @author AlexanderC
 */

namespace Pyha\DI;

trait Injectable
{
    /**
     * get injectable things for this class
     *
     * @return array
     */
    public static function getInjectables()
    {
        return [];
    }

    /**
     * get array of aliases
     *
     * @return array
     */
    public static function getAliases()
    {
        return [];
    }
}
