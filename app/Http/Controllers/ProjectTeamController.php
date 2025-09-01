<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Permission;

class ProjectTeamController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('update', $project);
        
        $team = $project->users()->orderByPivot('is_admin', 'desc')->get();
        $permissions = Permission::all()->groupBy('group');
        
        return view('projects.team.index', compact('project', 'team', 'permissions'));
    }

    public function invite(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,slug'
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if ($project->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Пользователь уже в команде');
        }
        
        $token = Str::random(60);
        
        $project->users()->attach($user->id, [
            'permissions' => json_encode($request->permissions),
            'invitation_token' => $token,
            'invited_at' => now()
        ]);
        
        // Отправка email с приглашением
        Mail::to($user->email)->send(new ProjectInvitation($project, $token));
        
        return back()->with('success', 'Приглашение отправлено');
    }

    public function updatePermissions(Request $request, Project $project, User $user)
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,slug'
        ]);
        
        $project->users()->updateExistingPivot($user->id, [
            'permissions' => json_encode($request->permissions)
        ]);
        
        return back()->with('success', 'Права обновлены');
    }
}
