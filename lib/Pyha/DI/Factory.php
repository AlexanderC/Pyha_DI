<?php

/**
 * @author AlexanderC
 */

namespace Pyha\DI;

use Pyha\Helpers\Traits\Singleton;

class Factory
{
    use Singleton;

    /**
     * @var Container
     */
    private $services;

    /**
     * {@inherit}
     */
    public function _onAfterConstruct()
    {
        $this->services = new Container();
    }

    /**
     * Register new Service
     *
     * @param string $name
     * @param mixed $service
     * @return Factory
     */
    public function register($name,
                             $service,
                             $registerSelfAsAlias = true,
                             $registerInterfacesAsAliases = true,
                             $registerParentsAsAliases = true)
    {
        $this->services[$name] = new Service($service);

        // add service alias case an class
        if(is_object($service)) {

            // add class name as alias
            if($registerSelfAsAlias && ($class = get_class($service)) !== false) {
                $this->addAlias($name, $class);
            }

            // add interfaces as aliases
            if($registerInterfacesAsAliases && ($interfaces = @class_implements($service))) {
                foreach($interfaces as $interface) {
                    $this->addAlias($name, $interface);
                }
            }

            // add parent classes as aliases
            if($registerParentsAsAliases && ($parents = @class_parents($service))) {
                foreach(array_values($parents) as $class) {
                    $this->addAlias($name, $class);
                }
            }

            // add another user defined aliases
            if(($traits = class_uses($service)) && in_array("Pyha\\DI\\Injectable", $traits)) {
                foreach($service::getAliases() as $alias) {
                    $this->addAlias($name, $alias);
                }
            }
        }

        return $this;
    }

    /**
     * Add service alias
     *
     * @param string $name
     * @param string $alias
     * @return Factory
     */
    public function addAlias($name, $alias)
    {
        $this->services[$alias] = $this->services[$name];
        return $this;
    }

    /**
     * Get service object
     *
     * @param string $name
     * @return mixed
     */
    public function getService($name)
    {
        return $this->services[$name];
    }

    /**
     * get registered services
     *
     * @return Container
     */
    public function getServices()
    {
        return clone $this->services;
    }

    /**
     * get an service
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function get($name, array $args = NULL)
    {
        if(!$this->exists($name)) { // assume that this is the class name
            $this->register($name, $name); // assume that this is an class name
        }

        // add arguments case available
        if($args) {
            $service = clone $this->services[$name];
            foreach($args as $name => $value) {
                $service->addNamedArg($name, $value);
            }

            return $service->getInstance();
        }

        return $this->services[$name]->getInstance();
    }

    /**
     * create new service instance
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function create($name, array $args = NULL)
    {
        if(!$this->exists($name)) { // assume that this is the class name
            $this->register($name, $name); // assume that this is an class name
        }

        // add arguments case available
        if($args) {
            $service = clone $this->services[$name];
            foreach($args as $name => $value) {
                $service->addNamedArg($name, $value);
            }

            return $service->create();
        }

        return $this->services[$name]->create();
    }

    /**
     * check if service exists
     *
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->services[$name]);
    }
}
