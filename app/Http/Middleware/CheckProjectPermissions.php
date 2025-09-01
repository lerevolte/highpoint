<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $permission)
    {
        $project = $request->route('project');
        
        $user = $request->user();
        
        if ($user->projects()->where('project_id', $project->id)->wherePivot('is_admin', true)->exists()) {
            return $next($request);
        }
        
        $userProject = $user->projects()->where('project_id', $project->id)->first();
        
        if (!$userProject || !in_array($permission, json_decode($userProject->pivot->permissions, true))) {
            abort(403);
        }
        
        return $next($request);
    }
}
