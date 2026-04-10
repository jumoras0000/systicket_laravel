<?php

namespace App\Http\Controllers;

use App\Models\Temps;
use App\Models\Projet;
use Illuminate\Http\Request;

class TempsController extends Controller
{
    public function index()
    {
        return view('pages.temps.index');
    }

    // --- API ---
    public function apiIndex(Request $request)
    {
        $query = Temps::with(['ticket', 'projet', 'user']);
        $user = $request->user();

        if ($user->isCollaborateur()) {
            $query->forUser($user->id);
        } elseif ($user->isClient()) {
            $projectIds = Projet::forClient($user->id)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        }

        if ($request->filled('project_id')) $query->forProject($request->project_id);
        if ($request->filled('user_id')) $query->forUser($request->user_id);
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $temps = $query->orderByDesc('date')->get()->map(function ($t) {
            $t->ticket_title = $t->ticket?->title;
            $t->project_name = $t->projet?->name;
            $t->user_name = $t->user?->full_name;
            return $t;
        });

        return response()->json(['success' => true, 'data' => $temps]);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'project_id' => 'nullable|exists:projets,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'nullable|string',
        ]);

        $projectId = $request->project_id;
        if (!$projectId && $request->ticket_id) {
            $ticket = \App\Models\Ticket::find($request->ticket_id);
            $projectId = $ticket?->project_id;
        }

        $temps = Temps::create([
            'ticket_id' => $request->ticket_id,
            'project_id' => $projectId,
            'user_id' => $request->user()->id,
            'date' => $request->date,
            'hours' => $request->hours,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'id' => $temps->id], 201);
    }

    public function apiUpdate(Request $request, Temps $temp)
    {
        $request->validate([
            'date' => 'sometimes|date',
            'hours' => 'sometimes|numeric|min:0.25|max:24',
            'description' => 'nullable|string',
        ]);

        $temp->update($request->only(['date', 'hours', 'description']));
        return response()->json(['success' => true]);
    }

    public function apiDestroy(Temps $temp)
    {
        $temp->delete();
        return response()->json(['success' => true]);
    }

    public function apiWeekSummary(Request $request)
    {
        $user = $request->user();
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $query = Temps::dateRange($startOfWeek, $endOfWeek);
        if ($user->isCollaborateur()) $query->forUser($user->id);

        $entries = $query->with(['ticket', 'projet'])->get();

        $byDay = [];
        foreach ($entries as $entry) {
            $day = $entry->date instanceof \Carbon\Carbon ? $entry->date->toDateString() : (string) $entry->date;
            if (!isset($byDay[$day])) $byDay[$day] = 0;
            $byDay[$day] += (float) $entry->hours;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'entries' => $entries,
                'by_day' => $byDay,
                'total' => $entries->sum('hours'),
            ],
        ]);
    }

    public function apiMonthTotal(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $query = Temps::dateRange($startDate, $endDate);
        if ($user->isCollaborateur()) $query->forUser($user->id);

        $total = $query->sum('hours');

        return response()->json([
            'success' => true,
            'data' => ['month' => $month, 'year' => $year, 'total_hours' => $total],
        ]);
    }

    public function apiHoursByProject(Request $request)
    {
        $user = $request->user();

        $query = Temps::selectRaw('project_id, SUM(hours) as total_hours')
            ->groupBy('project_id')
            ->with('projet');

        if ($user->isCollaborateur()) $query->forUser($user->id);
        if ($user->isClient()) {
            $projectIds = Projet::forClient($user->id)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $data = $query->get()->map(fn($r) => [
            'project_id' => $r->project_id,
            'project_name' => $r->projet?->name,
            'total_hours' => (float) $r->total_hours,
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function apiHoursByUser(Request $request)
    {
        $query = Temps::selectRaw('user_id, SUM(hours) as total_hours')
            ->groupBy('user_id')
            ->with('user');

        if ($request->filled('project_id')) $query->forProject($request->project_id);
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $data = $query->get()->map(fn($r) => [
            'user_id' => $r->user_id,
            'user_name' => $r->user?->full_name,
            'total_hours' => (float) $r->total_hours,
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }
}
