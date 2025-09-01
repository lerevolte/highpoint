<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\MarketingCost;
use App\Models\Project;
use Illuminate\Http\Request;

class ExpenseInputController extends Controller
{
    public function index(Project $project)
    {
        $costs = $project->marketingCosts()->latest('date')->paginate(15);
        return view('analytics.expense-input', compact('project', 'costs'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'source' => 'nullable|string|max:255',
            'medium' => 'nullable|string|max:255',
            'campaign' => 'nullable|string|max:255',
            'cost' => 'required|numeric|min:0',
        ]);

        $project->marketingCosts()->create($validated);

        return back()->with('success', 'Расход успешно добавлен.');
    }

    public function destroy(Project $project, MarketingCost $cost)
    {
        // Убедимся, что расход принадлежит этому проекту
        if ($cost->project_id !== $project->id) {
            abort(403);
        }
        $cost->delete();
        return back()->with('success', 'Расход удален.');
    }
}