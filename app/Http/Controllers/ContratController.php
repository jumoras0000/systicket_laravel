<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function index()
    {
        return view('pages.contrats.index');
    }

    public function create()
    {
        $projets = Projet::orderBy('name')->get();
        $clients = User::clients()->get();
        return view('pages.contrats.form', compact('projets', 'clients'));
    }

    public function show(Contrat $contrat)
    {
        $contrat->load(['projet.tickets.temps', 'client']);
        return view('pages.contrats.detail', compact('contrat'));
    }

    public function edit(Contrat $contrat)
    {
        $projets = Projet::orderBy('name')->get();
        $clients = User::clients()->get();
        return view('pages.contrats.form', compact('contrat', 'projets', 'clients'));
    }

    // --- API ---
    public function apiIndex(Request $request)
    {
        $query = Contrat::with(['projet', 'client']);
        $user = $request->user();

        if ($user->isClient()) {
            $query->where('client_id', $user->id);
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('project_id')) $query->where('project_id', $request->project_id);
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('reference', 'like', $s)->orWhere('notes', 'like', $s));
        }

        $contrats = $query->orderByDesc('created_at')->get()->map(function ($contrat) {
            $contrat->project_name = $contrat->projet?->name;
            $contrat->client_name = $contrat->client?->full_name;
            $contrat->consumed_hours = $contrat->consumed_hours;
            $contrat->remaining_hours = $contrat->remaining_hours;
            return $contrat;
        });

        return response()->json(['success' => true, 'data' => $contrats]);
    }

    public function apiShow(Contrat $contrat)
    {
        $contrat->load(['projet', 'client']);
        $contrat->project_name = $contrat->projet?->name;
        $contrat->client_name = $contrat->client?->full_name;
        $contrat->consumed_hours = $contrat->consumed_hours;
        $contrat->remaining_hours = $contrat->remaining_hours;
        $contrat->linked_tickets = $contrat->getLinkedTickets();

        return response()->json(['success' => true, 'data' => $contrat]);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|max:50|unique:contrats,reference',
            'project_id' => 'required|exists:projets,id',
            'client_id' => 'required|exists:users,id',
            'hours' => 'required|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,expired,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $contrat = Contrat::create($request->only([
            'reference', 'project_id', 'client_id', 'hours', 'rate', 'status', 'start_date', 'end_date', 'notes',
        ]));

        return response()->json(['success' => true, 'id' => $contrat->id], 201);
    }

    public function apiUpdate(Request $request, Contrat $contrat)
    {
        $request->validate([
            'reference' => 'sometimes|string|max:50|unique:contrats,reference,' . $contrat->id,
            'project_id' => 'sometimes|exists:projets,id',
            'client_id' => 'sometimes|exists:users,id',
            'hours' => 'sometimes|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,expired,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $contrat->update($request->only([
            'reference', 'project_id', 'client_id', 'hours', 'rate', 'status', 'start_date', 'end_date', 'notes',
        ]));

        return response()->json(['success' => true]);
    }

    public function apiDestroy(Contrat $contrat)
    {
        $contrat->delete();
        return response()->json(['success' => true]);
    }

    public function apiSummary(Contrat $contrat)
    {
        $tickets = $contrat->getLinkedTickets();
        $consumed = $contrat->consumed_hours;

        return response()->json([
            'success' => true,
            'data' => [
                'contrat' => $contrat,
                'tickets' => $tickets,
                'consumed_hours' => $consumed,
                'remaining_hours' => $contrat->hours - $consumed,
                'percentage' => $contrat->hours > 0 ? round(($consumed / $contrat->hours) * 100, 1) : 0,
            ],
        ]);
    }
}
