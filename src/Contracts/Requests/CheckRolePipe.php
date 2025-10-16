<?php

namespace Vinhdev\Travel\Contracts\Requests;

use Closure;

class CheckRolePipe
{
    public function __construct(
        protected string $requiredRole,
        protected array  $currentRoles
    ) {
    }

    public function handle($user, Closure $next)
    {
        if (!in_array($this->requiredRole, $this->currentRoles)) {
            return false;
        }

        return $next($user);
    }
}