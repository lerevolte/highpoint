<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        // Получаем все проекты пользователя с информацией о правах
        $projects = auth()->user()->projects()
            ->withPivot('is_admin')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Отдельно получаем проекты, где пользователь является администратором
        $adminProjects = auth()->user()->projects()
            ->wherePivot('is_admin', true)
            ->withPivot('is_admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('projects.index', compact('projects', 'adminProjects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'domain' => 'required|string|max:255|unique:projects,domain',
        ]);

        return DB::transaction(function () use ($request) {
            $project = Project::create([
                'name' => $request->name,
                'currency' => $request->currency,
                'domain' => $request->domain,
            ]);

            // Добавляем создателя как администратора проекта
            $project->users()->attach(auth()->id(), [
                'is_admin' => true,
                'permissions' => json_encode([]) // Админ имеет все права
            ]);

            return redirect()->route('projects.show', $project)
                ->with('success', 'Проект успешно создан');
        });
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $isAdmin = auth()->user()->can('update', $project);
        // $isAdmin = auth()->user()->projects()
        //     ->where('project_id', $project->id)
        //     ->wherePivot('is_admin', true)
        //     ->exists();

        return view('projects.show', compact('project', 'isAdmin'));
    }

    public function edit(Project $project)
    {
        // Проверяем, является ли пользователь администратором проекта
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        // Проверка прав администратора
        $this->authorize('update', $project);

        $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'domain' => 'required|string|max:255|unique:projects,domain,'.$project->id,
        ]);

        $project->update([
            'name' => $request->name,
            'currency' => $request->currency,
            'domain' => $request->domain,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Проект успешно обновлен');
    }
}