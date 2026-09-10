<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $allowedRoles = array_map(
            function (string $role): string {
                $enum = UserRole::tryFrom($role);

                return $enum !== null ? $enum->value : $role;
            },
            $roles
        );

        if (! in_array($user->role->value, $allowedRoles, true)) {
            abort(403, 'Unauthorized. Insufficient permissions.');
        }

        return $next($request);
    }
}
