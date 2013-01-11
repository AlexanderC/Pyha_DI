What's dependency injection(DI)?
================================

Just look at the article and watch that awesome video:
http://blog.ircmaxell.com/2013/01/dependency-injection-programming-with.html

How do i register items(services)?
==================================

    If the class does not have any deps- just skip this step!!!

First of all you have to register all dependencies, but keep in mind that:

    class App
    {
        // you can use this trait and implement getInjectables()
        // that returns associative array with named params that
        // could not be skipped or added with defined default values
        use Pyha\DI\Injectable;

        public function getInjectables()
        {
            return [
                'stringHere' => "hello world!"
            ];
        }

        /**
         * @param string $stringHere
         */
        public function __construct(AnyClassHere $thing, $stringHere)
        {

        }
    }

    class Foo
    {
        public function __construct(App $app)
        {

        }
    }
    class Bar extends Foo { }

    // i would like to get it via DI Factory
    class WeWannaGet
    {
        public function __construct(Bar $bar)
        {

        }
    }

    // in order to get this working we need... //

    // 1. register only top dependency
    Pyha\DI\Factory::getInstance()->register(
        new App(new AnyClassHere(), 'string here')
    );
    // or
    Pyha\DI\Factory::getInstance()->register(
            Pyha\DI\Factory::getInstance()->get('App')
        );
    // or (if app does not use injectable trait or do not get named param via getInjectables() method)
    Pyha\DI\Factory::getInstance()->register(
                Pyha\DI\Factory::getInstance()->get('App', ['stringHere' => "hello world!"])
            );

    // that u can use
    Pyha\DI\Factory::getInstance()->get('WeWannaGet'); // to get instance only once, just what an singleton does
    Pyha\DI\Factory::getInstance()->create('WeWannaGet'); // to create new instance each time

To register aliases use addAlias() Factory method.

NOTE: if as a service parameter to the register method is provided an object
by default are registered several service aliases. This are:

	- All Interfaces names implemented
	- All parent classes from extension tree
	- All Traits used

If you do not wannt to register this aliases, take a look at the method prototype:
	
	public function register(	$name,
                             		$service,
                             		$registerSelfAsAlias = true,
                             		$registerInterfacesAsAliases = true,
                             		$registerParentsAsAliases = true);

Loading Library
===============

If you are not using Composer you may add to autoloader the lib path or even include the files manualy

Installing via Composer
=======================

To install it via composer just use following command:

    $ composer.phar install
