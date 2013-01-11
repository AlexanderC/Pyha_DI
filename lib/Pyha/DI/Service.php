<?php

/**
 * @author AlexanderC
 */

namespace Pyha\DI;

class Service
{
    /**
     * @var mixed
     */
    private $service;

    /**
     * @var object
     */
    private $instance;

    /**
     * @var array
     */
    private $args = [];

    /**
     * @param mixed $service
     */
    public function __construct($service)
    {
        $this->service = $service;
    }

    /**
     * add named arg
     *
     * @param string $name
     * @param mixed $value
     */
    public function addNamedArg($name, $value)
    {
        $this->args[$name] = $value;
    }

    /**
     * get named argument
     *
     * @param string $name
     * @return mixed
     * @throws \RuntimeException
     */
    public function getNamedArg($name)
    {
        if(!$this->existsNamedArg($name)) {
            throw new \RuntimeException("Named service argument '$name' does not exists.");
        }

        return $this->args[$name];
    }

    /**
     * get named args
     *
     * @return array
     */
    public function getNamedArgs()
    {
        return $this->args;
    }

    /**
     * check if exists an named arg
     *
     * @param string $name
     * @return bool
     */
    public function existsNamedArg($name)
    {
        return array_key_exists($name, $this->args);
    }

    /**
     * create a new instance
     *
     * @return object
     */
    public function create()
    {
        if(is_object($this->service)) {
            return $this->service;
        } else if($this->service instanceof \Closure) {
            $refl = new \ReflectionFunction($this->service);

            return $refl->invokeArgs($this->_getDeps($refl));
        } else { // assume class
            $refl = new \ReflectionClass($this->service);

            // case unable to instantiate
            if(!$refl->isInstantiable()) {
                throw new \RuntimeException("Class '{$this->service}' should be instantiable");
            }

            // case no constructor
            if(!$refl->hasMethod('__construct')) {
                return $refl->newInstance();
            }

            // add named args if any
            if(($traits = class_uses($this->service)) && in_array("Pyha\\DI\\Injectable", $traits)) {
                foreach(call_user_func([$this->service, 'getInjectables']) as $name => $value) {
                    $this->addNamedArg($name,$value);
                }
            }

            return $refl->newInstanceArgs($this->_getDeps($refl->getMethod('__construct')));
        }
    }

    /**
     * get all dependencies
     *
     * @param \ReflectionMethod|\ReflectionFunction  $refl
     * @return array
     */
    private function _getDeps($refl)
    {
        $args = [];

        /** @var \ReflectionParameter $param */
        foreach($refl->getParameters() as $param) {
            if(!$this->existsNamedArg($param->getName())) {
                if($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    if(false === ($piname = $this->_getParamInstanceName($param))) {
                        throw new \RuntimeException(
                            "Class '{$this->service}' holds more required parameters than were found.".
                            " Exception while preparing " . $param);
                    }

                    // get instance from factory
                    $args[] = Factory::getInstance()->get($piname);
                }
            } else {
                $args[] = $this->getNamedArg($param->getName());
            }
        }

        return $args;
    }

    /**
     * get class name for the param
     *
     * @param \ReflectionParameter $param
     * @return bool
     */
    private function _getParamInstanceName(\ReflectionParameter $param)
    {
        preg_match('/\[\s\<\w+?>\s([\w\\\\]+)/s', (string) $param, $matches);

        $type = isset($matches[1]) ? trim($matches[1]) : false;

        return in_array(mb_strtolower($type), [
            'callable', 'array'
        ]) ? false : $type;
    }

    /**
     * get instance only once
     *
     * @return object
     */
    public function getInstance()
    {
        if($this->instance) {
            return $this->instance;
        }

        $this->instance = $this->create();
        return $this->instance;
    }
}
