<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function invite(Request $request, Project $project)
    {
        $request->validate([
            'email' => 'required|email',
            'permissions' => 'required|array'
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            // Если пользователя нет, можно создать или отправить приглашение на регистрацию
            // В этом примере предполагаем, что пользователь уже зарегистрирован
            return back()->with('error', 'Пользователь с таким email не найден');
        }
        
        $token = Str::random(60);
        
        $project->users()->attach($user->id, [
            'permissions' => json_encode($request->permissions),
            'invitation_token' => $token,
            'invited_at' => now()
        ]);
        
        // Отправка email с ссылкой на принятие приглашения
        Mail::to($user->email)->send(new ProjectInvitationMail($project, $token));
        
        return back()->with('success', 'Приглашение отправлено');
    }

    public function accept($token)
    {
        $projectUser = DB::table('project_user')
            ->where('invitation_token', $token)
            ->first();
            
        if (!$projectUser) {
            abort(404);
        }
        
        DB::table('project_user')
            ->where('id', $projectUser->id)
            ->update([
                'joined_at' => now(),
                'invitation_token' => null
            ]);
        
        auth()->login(User::find($projectUser->user_id));
        
        return redirect()->route('profile.edit')->with('success', 'Вы успешно присоединились к проекту');
    }
}
