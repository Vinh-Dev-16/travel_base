<?php

namespace Vinhdev\Travel\Contracts\Requests;

use Closure;

class CheckPermissionPipe
{

    public function __construct(
        protected string $requiredPermission,
        protected array  $currentPermissions
    ) {
    }

    public function handle($user, Closure $next)
    {
        if (!in_array($this->requiredPermission, $this->currentPermissions)) {
            return false;
        }

        return $next($user);
    }
}