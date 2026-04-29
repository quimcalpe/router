<?php
namespace QuimCalpe\Router\Dispatcher;

use Psr\Container\ContainerInterface;
use QuimCalpe\Router\Exception\ActionNotFoundException;
use QuimCalpe\Router\Exception\ControllerNotFoundException;

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
     * @throws ControllerNotFoundException
     * @throws ActionNotFoundException
     */
    protected function invoke(string $class, string $action, array $args): mixed
    {
        if (!$this->canResolve($class)) {
            throw new ControllerNotFoundException("Controller {$class} not found");
        }
        $instance = $this->instance($class);
        if (!method_exists($instance, $action)) {
            throw new ActionNotFoundException("No method {$action} in controller {$class}");
        }

        return $instance->$action(...$args);
    }

    /**
     * Resuelve un controller a una instancia. Override completo de la
     * decisión container-vs-fallback. Para customizar sólo el fallback
     * (cuando no hay container o no conoce la clase) override `instantiate()`.
     */
    protected function instance(string $class): object
    {
        if ($this->container?->has($class)) {
            return $this->container->get($class);
        }

        return $this->instantiate($class);
    }

    /**
     * Hook de instanciación cuando no hay container PSR-11 configurado o
     * cuando el container no conoce la clase. Override para enchufar una
     * factory propia (Laravel container, Auryn, etc.) sin envolverla en PSR-11.
     */
    protected function instantiate(string $class): object
    {
        return new $class();
    }

    /**
     * @internal Visible para que un override de `instance()`/`instantiate()`
     * pueda reusar la heurística de existencia. No forma parte de la API estable.
     */
    protected function canResolve(string $class): bool
    {
        return $this->container?->has($class) || class_exists($class);
    }
}
