<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\ChannelGroup;
use App\Models\ChannelGroupRule;
use App\Models\LeadSource;
use App\Models\Project;
use Illuminate\Http\Request;

class ChannelGroupingController extends Controller
{
    public function index(Project $project)
    {
        $groups = $project->channelGroups()->with('rules')->get();
        
        // Получаем источники, которые еще не добавлены ни в одну группу
        $assignedSources = ChannelGroupRule::whereIn('channel_group_id', $groups->pluck('id'))
            ->get(['source', 'medium'])
            ->map(fn($item) => $item->source . '|' . $item->medium)
            ->toArray();

        $availableSources = LeadSource::where('project_id', $project->id)
            ->get()
            ->filter(fn($item) => !in_array($item->source . '|' . $item->medium, $assignedSources));

        return view('analytics.channel-grouping', compact('project', 'groups', 'availableSources'));
    }

    public function store(Request $request, Project $project)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $project->channelGroups()->create($request->only('name'));
        return back()->with('success', 'Группа успешно создана.');
    }

    public function addRule(Request $request, Project $project, ChannelGroup $group)
    {
        $request->validate(['source_pair' => 'required|string']);
        [$source, $medium] = explode('|', $request->source_pair);

        $group->rules()->create(compact('source', 'medium'));
        return back()->with('success', 'Правило добавлено.');
    }

    public function removeRule(Project $project, ChannelGroupRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Правило удалено.');
    }

    public function destroy(Project $project, ChannelGroup $group)
    {
        $group->delete();
        return back()->with('success', 'Группа удалена.');
    }
}