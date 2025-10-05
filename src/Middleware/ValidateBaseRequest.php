<?php

namespace Vinhdev\Travel\Middleware;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Vinhdev\Travel\Contracts\Requests\BaseRequest;

class ValidateBaseRequest
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();

        if ($route) {
            $action = $route->getAction();

            // Lấy controller và method
            if (isset($action['controller'])) {
                [$controller, $method] = explode('@', $action['controller']);

                if (class_exists($controller) && method_exists($controller, $method)) {
                    $reflection = new \ReflectionMethod($controller, $method);
                    $parameters = $reflection->getParameters();

                    foreach ($parameters as $parameter) {
                        $type = $parameter->getType();

                        if ($type && !$type->isBuiltin()) {
                            $className = $type->getName();

                            // Check nếu là BaseRequest hoặc subclass
                            if (is_subclass_of($className, BaseRequest::class)) {
                                // Tạo instance - constructor sẽ tự động lấy data từ global request
                                $customRequest = new $className();

                                // Validate ngay
                                $customRequest->validateRequest();

                                // Bind vào container để controller có thể inject
                                $this->container->instance($className, $customRequest);
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}