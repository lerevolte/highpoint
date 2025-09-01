<?php
namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Выполняется перед всеми другими проверками. Админ может всё.
     */
    public function before(User $user, $ability)
    {
        //if ($user->isSuperAdmin()) { // Предположим, у вас есть роль суперадмина
            return true;
        //}
    }

    /**
     * Проверка, может ли пользователь просматривать проект.
     */
    public function view(User $user, Project $project)
    {
        return $project->users->contains($user);
        //return $user->projects()->where('project_id', $project->id)->exists();
    }


    /**
     * Проверка, может ли пользователь обновлять проект (настройки, команда и т.д.).
     * Только администратор проекта.
     */
    public function update(User $user, Project $project)
    {
        return $project->users()->where('user_id', $user->id)->where('is_admin', true)->exists();
        // return $user->projects()
        //     ->where('project_id', $project->id)
        //     ->wherePivot('is_admin', true)
        //     ->exists();
    }

    /**
     * Проверка, может ли пользователь удалять проект.
     */
    public function delete(User $user, Project $project)
    {
        return $this->update($user, $project); // Обычно те же права, что и на обновление
    }
    
    /**
     * Проверка кастомных прав, например, на просмотр аналитики.
     */
    public function viewAnalytics(User $user, Project $project)
    {
        $permissions = $project->users()->where('user_id', $user->id)->value('permissions');
        $permissions = json_decode($permissions, true) ?? [];

        // Администратор может всё, либо проверяем конкретное право
        return $this->update($user, $project) || in_array('view_analytics', $permissions);
    }

    public function manageTeam(User $user, Project $project)
    {
        return $this->update($user, $project);
    }
}
