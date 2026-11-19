<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            $originalAdmin = User::find($request->session()->get('impersonate.original_id'));

            if (
                $user?->isEmployee()
                && filled($request->session()->get('impersonate.token'))
                && $originalAdmin?->isAdmin()
            ) {
                return redirect()->route('employee.dashboard');
            }

            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
