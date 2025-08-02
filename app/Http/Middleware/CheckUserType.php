<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $userType): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        switch ($userType) {
            case 'admin':
                if (!$user->isAdmin()) {
                    return response()->json(['message' => 'Access denied. Admin required.'], 403);
                }
                break;
            case 'companion':
                if (!$user->isCompanion()) {
                    return response()->json(['message' => 'Access denied. Companion required.'], 403);
                }
                break;
            case 'client':
                if (!$user->isClient()) {
                    return response()->json(['message' => 'Access denied. Client required.'], 403);
                }
                break;
            default:
                return response()->json(['message' => 'Invalid user type.'], 400);
        }

        return $next($request);
    }
}
