<?php

namespace YOOtheme\Application;

use YOOtheme\Container;
use YOOtheme\Event;

class EventLoader
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * Constructor.
     *
     * @param EventDispatcher $dispatcher
     */
    public function __construct($dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?: Event::getDispatcher();
    }

    /**
     * Load event listeners.
     *
     * @param Container $container
     * @param array     $configs
     */
    public function __invoke(Container $container, array $configs)
    {
        foreach ($configs as $events) {
            foreach ($events as $event => $listeners) {
                foreach ($listeners as $class => $parameters) {

                    $parameters = (array) $parameters;

                    if (is_string($parameters[0])) {
                        $parameters = [$parameters];
                    }

                    foreach ($parameters as $params) {
                        $this->addListener($container, $event, $class, ...$params);
                    }
                }
            }
        }
    }

    /**
     * Adds a listener.
     *
     * @param Container $container
     * @param string    $event
     * @param string    $class
     * @param string    $method
     * @param array     $params
     */
    public function addListener(Container $container, $event, $class, $method, ...$params)
    {
        $this->dispatcher->addListener($event, function (...$arguments) use ($container, $class, $method) {

            $callback = [$class, $method];

            if ($method[0] === '@') {
                $callback = join($callback);
            }

            return $container->call($callback, $arguments);

        }, ...$params);
    }
}
