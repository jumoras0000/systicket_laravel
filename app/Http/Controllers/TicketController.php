<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Projet;
use App\Models\Temps;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    // --- WEB Routes ---
    public function index()
    {
        return view('pages.tickets.index');
    }

    public function create()
    {
        $projets = $this->getAccessibleProjets();
        $collaborateurs = User::equipe()->get();
        return view('pages.tickets.form', compact('projets', 'collaborateurs'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['projet.client', 'creator', 'assignees', 'commentaires.user', 'temps.user']);
        return view('pages.tickets.detail', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $projets = $this->getAccessibleProjets();
        $collaborateurs = User::equipe()->get();
        $ticket->load('assignees');
        return view('pages.tickets.form', compact('ticket', 'projets', 'collaborateurs'));
    }

    // --- API Routes ---
    public function apiIndex(Request $request)
    {
        $query = Ticket::with(['projet.client', 'creator', 'assignees']);
        $user = $request->user();

        // Filtrage par rôle
        if ($user->isCollaborateur()) {
            $projectIds = $user->projets()->pluck('projets.id')
                ->merge($user->projetsAsManager()->pluck('id'))
                ->unique();
            $query->whereIn('project_id', $projectIds);
        } elseif ($user->isClient()) {
            $query->forClient($user->id);
        }

        // Filtres
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('project_id')) $query->where('project_id', $request->project_id);
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('title', 'like', $search)->orWhere('description', 'like', $search));
        }

        $total = $query->count();
        $tickets = $query->orderByDesc('created_at');

        if ($request->filled('limit')) {
            $tickets = $tickets->limit((int)$request->limit)
                ->offset((int)($request->offset ?? 0));
        }

        $tickets = $tickets->get()->map(function ($ticket) {
            $ticket->spent_hours = $ticket->spent_hours;
            $ticket->client_name = $ticket->client_name;
            $ticket->assignee_names = $ticket->assignee_names;
            $ticket->creator_name = $ticket->creator?->full_name;
            $ticket->project_name = $ticket->projet?->name;
            return $ticket;
        });

        return response()->json(['success' => true, 'data' => $tickets, 'total' => $total]);
    }

    public function apiShow(Ticket $ticket)
    {
        $ticket->load(['projet.client', 'creator', 'assignees', 'commentaires.user', 'temps.user']);
        $ticket->spent_hours = $ticket->spent_hours;
        $ticket->client_name = $ticket->client_name;
        $ticket->assignee_names = $ticket->assignee_names;
        $ticket->creator_name = $ticket->creator?->full_name;
        $ticket->project_name = $ticket->projet?->name;

        return response()->json(['success' => true, 'data' => $ticket]);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projets,id',
            'status' => 'nullable|in:new,in-progress,waiting-client,done,to-validate,validated,refused',
            'priority' => 'nullable|in:low,normal,high,critical',
            'type' => 'nullable|in:included,billable',
            'estimated_hours' => 'nullable|numeric|min:0',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'project_id' => $request->project_id,
            'status' => $request->status ?? 'new',
            'priority' => $request->priority ?? 'normal',
            'type' => $request->type ?? 'included',
            'estimated_hours' => $request->estimated_hours ?? 0,
            'created_by' => $request->user()->id,
        ]);

        if ($request->has('assignees')) {
            $ticket->assignees()->sync($request->assignees);
        }

        return response()->json(['success' => true, 'id' => $ticket->id], 201);
    }

    public function apiUpdate(Request $request, Ticket $ticket)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projets,id',
            'status' => 'nullable|in:new,in-progress,waiting-client,done,to-validate,validated,refused',
            'priority' => 'nullable|in:low,normal,high,critical',
            'type' => 'nullable|in:included,billable',
            'estimated_hours' => 'nullable|numeric|min:0',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        $data = $request->only(['title', 'description', 'project_id', 'status', 'priority', 'type', 'estimated_hours']);

        // Transition billable -> done => to-validate
        if (isset($data['status']) && $data['status'] === 'done' && $ticket->type === 'billable') {
            $data['status'] = 'to-validate';
        }

        $ticket->update($data);

        if ($request->has('assignees')) {
            $ticket->assignees()->sync($request->assignees);
        }

        return response()->json(['success' => true]);
    }

    public function apiDestroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['success' => true]);
    }

    // Commentaires
    public function apiComments(Ticket $ticket)
    {
        $comments = $ticket->commentaires()->with('user')->orderBy('created_at')->get();
        return response()->json(['success' => true, 'data' => $comments]);
    }

    public function apiAddComment(Request $request, Ticket $ticket)
    {
        $request->validate(['content' => 'required|string']);

        $comment = $ticket->commentaires()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        return response()->json(['success' => true, 'id' => $comment->id], 201);
    }

    // Assignees
    public function apiAssignees(Ticket $ticket)
    {
        return response()->json(['success' => true, 'data' => $ticket->assignees]);
    }

    public function apiAddAssignee(Request $request, Ticket $ticket)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $ticket->assignees()->syncWithoutDetaching([$request->user_id]);
        return response()->json(['success' => true, 'data' => $ticket->fresh()->assignees]);
    }

    public function apiRemoveAssignee(Request $request, Ticket $ticket)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $ticket->assignees()->detach($request->user_id);
        return response()->json(['success' => true, 'data' => $ticket->fresh()->assignees]);
    }

    // Time entries
    public function apiTimeEntries(Ticket $ticket)
    {
        $entries = $ticket->temps()->with('user')->orderByDesc('date')->get();
        return response()->json(['success' => true, 'data' => $entries]);
    }

    // To validate
    public function apiToValidate(Request $request)
    {
        $query = Ticket::with(['projet.client'])
            ->whereIn('status', ['to-validate', 'validated', 'refused']);

        if ($request->user()->isClient()) {
            $query->forClient($request->user()->id);
        }

        $tickets = $query->orderByDesc('created_at')->get()->map(function ($ticket) {
            $ticket->spent_hours = $ticket->spent_hours;
            $ticket->project_name = $ticket->projet?->name;
            $contrat = $ticket->projet?->activeContrat;
            $ticket->contract_rate = $contrat?->rate;
            return $ticket;
        });

        return response()->json(['success' => true, 'data' => $tickets]);
    }

    private function getAccessibleProjets()
    {
        $user = Auth::user();
        if ($user->isAdmin()) return Projet::all();
        if ($user->isCollaborateur()) {
            return Projet::forUser($user->id)->get();
        }
        return Projet::forClient($user->id)->get();
    }
}
