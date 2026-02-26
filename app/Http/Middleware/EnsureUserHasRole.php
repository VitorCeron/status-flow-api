<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $role
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $requiredRole = RoleEnum::from($role);

        if ($request->user()?->role !== $requiredRole) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
