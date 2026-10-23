<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolTenant
{
    /**
     * Enforce tenant isolation by verifying school_id against authenticated user context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role !== 'admin') {
            if (!$user->school_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not linked to any active school tenant.',
                ], 403);
            }

            // Automatically scope request parameters to the user's school_id
            $request->merge(['school_id' => $user->school_id]);
        }

        return $next($request);
    }
}
