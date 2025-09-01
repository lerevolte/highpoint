<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    
    public function expenseInput(Project $project)
    {
        return view('analytics.expense-input', [
            'project' => $project,
            'section' => 'expense-input'
        ]);
    }

    public function channelGrouping(Project $project)
    {
        return view('analytics.channel-grouping', [
            'project' => $project,
            'section' => 'channel-grouping'
        ]);
    }

}