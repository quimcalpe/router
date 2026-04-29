<?php
namespace QuimCalpe\Router\Dispatcher;

use Psr\Container\ContainerInterface;
use RuntimeException;

abstract class AbstractDispatcher implements DispatcherInterface
{
    public function __construct(protected readonly ?ContainerInterface $container = null) {}

    /**
     * Resuelve un controller string "Class::method" a [Class, method].
     * Si no hay método, asume "index".
     *
     * @return array{0: class-string, 1: string}
     */
    protected function resolve(string $controller): array
    {
        $segments = explode("::", $controller);

        return [$segments[0], $segments[1] ?? "index"];
    }

    /**
     * @throws RuntimeException
     */
    protected function invoke(string $class, string $action, array $args): mixed
    {
        if (!method_exists($class, $action)) {
            throw new RuntimeException("No method {$action} in controller {$class}");
        }

        return $this->instance($class)->$action(...$args);
    }

    /**
     * Instancia un controller, opcionalmente vía PSR-11 si hay container.
     */
    protected function instance(string $class): object
    {
        if ($this->container?->has($class)) {
            return $this->container->get($class);
        }

        return new $class();
    }
}
