<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjetController extends Controller
{
    public function index()
    {
        return view('pages.projets.index');
    }

    public function create()
    {
        $clients = User::clients()->get();
        $collaborateurs = User::equipe()->get();
        return view('pages.projets.form', compact('clients', 'collaborateurs'));
    }

    public function show(Projet $projet)
    {
        $projet->load(['client', 'manager', 'users', 'tickets.temps', 'contrats']);
        return view('pages.projets.detail', compact('projet'));
    }

    public function edit(Projet $projet)
    {
        $clients = User::clients()->get();
        $collaborateurs = User::equipe()->get();
        $projet->load('users');
        return view('pages.projets.form', compact('projet', 'clients', 'collaborateurs'));
    }

    // --- API ---
    public function apiIndex(Request $request)
    {
        $query = Projet::with(['client', 'manager']);
        $user = $request->user();

        if ($user->isCollaborateur()) {
            $query->forUser($user->id);
        } elseif ($user->isClient()) {
            $query->forClient($user->id);
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('name', 'like', $s)->orWhere('description', 'like', $s));
        }

        $projets = $query->orderByDesc('created_at')->get()->map(function ($projet) {
            $projet->client_name = $projet->client?->full_name;
            $projet->manager_name = $projet->manager?->full_name;
            $projet->tickets_count = $projet->tickets()->count();
            $contrat = $projet->activeContrat;
            $projet->contract_hours = $contrat?->hours;
            $projet->contract_rate = $contrat?->rate;
            $projet->total_hours = $projet->total_hours;
            return $projet;
        });

        return response()->json(['success' => true, 'data' => $projets]);
    }

    public function apiShow(Projet $projet)
    {
        $projet->load(['client', 'manager', 'users', 'tickets.temps', 'contrats']);
        $projet->client_name = $projet->client?->full_name;
        $projet->manager_name = $projet->manager?->full_name;

        return response()->json(['success' => true, 'data' => $projet]);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:active,paused,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'manager_id' => 'nullable|exists:users,id',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        $projet = Projet::create($request->only([
            'name', 'description', 'client_id', 'status', 'start_date', 'end_date', 'manager_id',
        ]));

        if ($request->has('assignees')) {
            $projet->users()->sync($request->assignees);
        }

        return response()->json(['success' => true, 'id' => $projet->id], 201);
    }

    public function apiUpdate(Request $request, Projet $projet)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:active,paused,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'manager_id' => 'nullable|exists:users,id',
            'assignees' => 'nullable|array',
        ]);

        $projet->update($request->only([
            'name', 'description', 'client_id', 'status', 'start_date', 'end_date', 'manager_id',
        ]));

        if ($request->has('assignees')) {
            $projet->users()->sync($request->assignees);
        }

        return response()->json(['success' => true]);
    }

    public function apiDestroy(Projet $projet)
    {
        $projet->delete();
        return response()->json(['success' => true]);
    }

    public function apiAssignees(Projet $projet)
    {
        return response()->json(['success' => true, 'data' => $projet->users]);
    }

    public function apiAddAssignee(Request $request, Projet $projet)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $projet->users()->syncWithoutDetaching([$request->user_id]);
        return response()->json(['success' => true, 'data' => $projet->fresh()->users]);
    }

    public function apiRemoveAssignee(Request $request, Projet $projet)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $projet->users()->detach($request->user_id);
        return response()->json(['success' => true, 'data' => $projet->fresh()->users]);
    }

    public function apiTickets(Projet $projet)
    {
        $tickets = $projet->tickets()->with('temps')->orderByDesc('created_at')->get();
        return response()->json(['success' => true, 'data' => $tickets]);
    }

    public function apiContrat(Projet $projet)
    {
        $contrat = $projet->activeContrat;
        return response()->json(['success' => true, 'data' => $contrat]);
    }

    public function apiCounts()
    {
        $counts = Projet::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json(['success' => true, 'data' => $counts]);
    }
}
