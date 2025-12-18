<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Role;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roleIds = [];
        foreach ($roles as $role) {
            $roleIds[] = match ($role) {
                'superadmin' => Role::SUPERADMIN,
                'admin' => Role::ADMIN,
                'alumni' => Role::ALUMNI,
                default => 0,
            };
        }

        // Superadmin has access to admin routes
        if (in_array(Role::ADMIN, $roleIds)) {
            $roleIds[] = Role::SUPERADMIN;
        }

        if (!in_array($user->role_id, $roleIds)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
