<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Projet;
use App\Models\Temps;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.dashboard');
    }

    public function rapports()
    {
        return view('pages.rapports');
    }

    // --- API ---
    public function apiStats(Request $request)
    {
        $user = $request->user();

        $hoursMonth = Temps::whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
        $hoursBudget = Contrat::where('status', 'active')->sum('hours');
        $hoursConsumed = Temps::sum('hours');

        if ($user->isAdmin()) {
            $stats = [
                'tickets_total' => Ticket::count(),
                'tickets_open' => Ticket::open()->count(),
                'to_validate' => Ticket::toValidate()->count(),
                'projets_total' => Projet::count(),
                'projets_active' => Projet::active()->count(),
                'users_total' => User::count(),
                'contrats_active' => Contrat::where('status', 'active')->count(),
                'hours_month' => (float) $hoursMonth->sum('hours'),
                'hours_budget' => (float) $hoursBudget,
                'hours_consumed' => (float) $hoursConsumed,
            ];
        } elseif ($user->isCollaborateur()) {
            $projectIds = $user->projets()->pluck('projets.id');
            $stats = [
                'tickets_total' => Ticket::whereIn('project_id', $projectIds)->count(),
                'tickets_open' => Ticket::whereIn('project_id', $projectIds)->open()->count(),
                'my_tickets' => $user->tickets()->count(),
                'projets_total' => $projectIds->count(),
                'projets_active' => Projet::whereIn('id', $projectIds)->active()->count(),
                'hours_month' => (float) Temps::forUser($user->id)
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->sum('hours'),
                'hours_budget' => (float) Contrat::whereIn('project_id', $projectIds)->where('status', 'active')->sum('hours'),
                'hours_consumed' => (float) Temps::forUser($user->id)->sum('hours'),
                'to_validate' => 0,
            ];
        } else {
            $projectIds = Projet::forClient($user->id)->pluck('id');
            $stats = [
                'tickets_total' => Ticket::whereIn('project_id', $projectIds)->count(),
                'tickets_open' => Ticket::whereIn('project_id', $projectIds)->open()->count(),
                'to_validate' => Ticket::toValidate()
                    ->whereHas('projet', fn($q) => $q->where('client_id', $user->id))
                    ->count(),
                'projets_total' => $projectIds->count(),
                'projets_active' => Projet::whereIn('id', $projectIds)->active()->count(),
                'contrats_active' => Contrat::where('client_id', $user->id)->where('status', 'active')->count(),
                'hours_month' => (float) Temps::whereIn('project_id', $projectIds)
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->sum('hours'),
                'hours_budget' => (float) Contrat::where('client_id', $user->id)->where('status', 'active')->sum('hours'),
                'hours_consumed' => (float) Temps::whereIn('project_id', $projectIds)->sum('hours'),
            ];
        }

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function apiCharts(Request $request)
    {
        $user = $request->user();

        // Tickets par statut
        $ticketsByStatusQuery = Ticket::selectRaw('status, COUNT(*) as count');
        if ($user->isCollaborateur()) {
            $ticketsByStatusQuery->whereIn('project_id', $user->projets()->pluck('projets.id'));
        } elseif ($user->isClient()) {
            $ticketsByStatusQuery->whereIn('project_id', Projet::forClient($user->id)->pluck('id'));
        }
        $ticketsByStatus = $ticketsByStatusQuery->groupBy('status')->get()->map(fn($r) => [
            'status' => $r->status,
            'count' => (int) $r->count,
        ])->values();

        // Tickets par priorité
        $ticketsByPriorityQuery = Ticket::selectRaw('priority, COUNT(*) as count');
        if ($user->isClient()) {
            $ticketsByPriorityQuery->whereIn('project_id', Projet::forClient($user->id)->pluck('id'));
        }
        $ticketsByPriority = $ticketsByPriorityQuery->groupBy('priority')->get()->map(fn($r) => [
            'priority' => $r->priority,
            'count' => (int) $r->count,
        ])->values();

        // Heures par projet (top 10)
        $hoursByProject = Temps::selectRaw('project_id, SUM(hours) as total')
            ->groupBy('project_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('projet')
            ->get()
            ->map(fn($t) => [
                'name' => $t->projet?->name,
                'total' => (float) $t->total,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'tickets_by_status' => $ticketsByStatus,
                'tickets_by_priority' => $ticketsByPriority,
                'hours_by_project' => $hoursByProject,
            ],
        ]);
    }

    public function apiRecentActivity(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 10);

        $ticketQuery = Ticket::with('projet')
            ->when($user->isCollaborateur(), fn($q) => $q->whereIn('project_id', $user->projets()->pluck('projets.id')))
            ->when($user->isClient(), fn($q) => $q->whereIn('project_id', Projet::forClient($user->id)->pluck('id')));

        $recentTickets = (clone $ticketQuery)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        // Recent activity: mix of temps and ticket changes
        $recentTemps = Temps::with(['user', 'projet'])
            ->when($user->isCollaborateur(), fn($q) => $q->forUser($user->id))
            ->when($user->isClient(), fn($q) => $q->whereIn('project_id', Projet::forClient($user->id)->pluck('id')))
            ->orderByDesc('date')
            ->limit($limit)
            ->get()
            ->map(fn($t) => [
                'type' => 'temps',
                'user_name' => $t->user?->full_name,
                'hours' => (float) $t->hours,
                'project_name' => $t->projet?->name,
                'activity_date' => $t->date,
            ]);

        $recentTicketActivity = (clone $ticketQuery)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn($t) => [
                'type' => 'ticket',
                'user_name' => $t->creator?->full_name,
                'label' => $t->title,
                'activity_date' => $t->updated_at,
            ]);

        $recentActivity = $recentTemps->merge($recentTicketActivity)
            ->sortByDesc('activity_date')
            ->take($limit)
            ->values();

        // Featured projects
        $featuredProjects = Projet::active()
            ->withCount('tickets')
            ->with('client')
            ->when($user->isCollaborateur(), fn($q) => $q->forUser($user->id))
            ->when($user->isClient(), fn($q) => $q->forClient($user->id))
            ->orderByDesc('tickets_count')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'client_name' => $p->client?->full_name,
                'tickets_count' => $p->tickets_count,
            ]);

        return response()->json(['success' => true, 'data' => [
            'recent_tickets' => $recentTickets,
            'recent_activity' => $recentActivity,
            'featured_projects' => $featuredProjects,
        ]]);
    }

    public function apiRapports(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $projectId = $request->input('project_id');
        $clientId = $request->input('client_id');

        // Tickets query with filters
        $ticketQuery = Ticket::query();
        if ($projectId) $ticketQuery->where('project_id', $projectId);
        if ($clientId) {
            $clientProjectIds = Projet::where('client_id', $clientId)->pluck('id');
            $ticketQuery->whereIn('project_id', $clientProjectIds);
        }
        if ($dateFrom) $ticketQuery->where('created_at', '>=', $dateFrom);
        if ($dateTo) $ticketQuery->where('created_at', '<=', $dateTo . ' 23:59:59');

        $totalTickets = $ticketQuery->count();

        // Hours query with filters
        $tempsQuery = Temps::query();
        if ($projectId) $tempsQuery->where('project_id', $projectId);
        if ($clientId) {
            $clientProjectIds = $clientProjectIds ?? Projet::where('client_id', $clientId)->pluck('id');
            $tempsQuery->whereIn('project_id', $clientProjectIds);
        }
        if ($dateFrom && $dateTo) {
            $tempsQuery->dateRange($dateFrom, $dateTo);
        }

        $totalHours = (float) $tempsQuery->sum('hours');

        // Projects count
        $projetQuery = Projet::query();
        if ($projectId) $projetQuery->where('id', $projectId);
        if ($clientId) $projetQuery->where('client_id', $clientId);
        $totalProjects = $projetQuery->count();

        // Tickets by status
        $ticketsByStatusQuery = Ticket::selectRaw('status, COUNT(*) as count');
        if ($projectId) $ticketsByStatusQuery->where('project_id', $projectId);
        if ($clientId) {
            $clientProjectIds = $clientProjectIds ?? Projet::where('client_id', $clientId)->pluck('id');
            $ticketsByStatusQuery->whereIn('project_id', $clientProjectIds);
        }
        $ticketsByStatus = $ticketsByStatusQuery->groupBy('status')->get()->map(fn($r) => [
            'status' => $r->status,
            'count' => (int) $r->count,
        ])->values();

        // Hours by project
        $hbpQuery = Temps::selectRaw('project_id, SUM(hours) as total')
            ->groupBy('project_id')
            ->orderByDesc('total')
            ->with('projet');
        if ($projectId) $hbpQuery->where('project_id', $projectId);
        if ($dateFrom && $dateTo) $hbpQuery->dateRange($dateFrom, $dateTo);
        $hoursByProject = $hbpQuery->get()->map(fn($t) => [
            'name' => $t->projet?->name,
            'total' => (float) $t->total,
        ]);

        // Hours by user
        $hbuQuery = Temps::selectRaw('user_id, SUM(hours) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user');
        if ($projectId) $hbuQuery->where('project_id', $projectId);
        if ($dateFrom && $dateTo) $hbuQuery->dateRange($dateFrom, $dateTo);
        $hoursByUser = $hbuQuery->get()->map(fn($t) => [
            'name' => $t->user?->full_name,
            'total' => (float) $t->total,
        ]);

        // Billing data
        $billing = Contrat::with(['projet', 'client'])
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->get()
            ->map(fn($c) => [
                'client_name' => $c->client?->full_name,
                'project_name' => $c->projet?->name,
                'consumed_hours' => (float) $c->consumed_hours,
                'rate' => (float) $c->rate,
            ]);

        return response()->json(['success' => true, 'data' => [
            'total_tickets' => $totalTickets,
            'total_hours' => $totalHours,
            'total_projects' => $totalProjects,
            'tickets_by_status' => $ticketsByStatus,
            'hours_by_project' => $hoursByProject,
            'hours_by_user' => $hoursByUser,
            'billing' => $billing,
        ]]);
    }

    private function rapportHeuresParProjet($from, $to)
    {
        $query = Temps::selectRaw('project_id, SUM(hours) as total_hours, COUNT(*) as entries')
            ->groupBy('project_id')
            ->with('projet');

        if ($from && $to) $query->dateRange($from, $to);

        return $query->get()->map(fn($r) => [
            'project_id' => $r->project_id,
            'project_name' => $r->projet?->name,
            'total_hours' => (float) $r->total_hours,
            'entries' => $r->entries,
        ]);
    }

    private function rapportHeuresParUtilisateur($from, $to)
    {
        $query = Temps::selectRaw('user_id, SUM(hours) as total_hours, COUNT(*) as entries')
            ->groupBy('user_id')
            ->with('user');

        if ($from && $to) $query->dateRange($from, $to);

        return $query->get()->map(fn($r) => [
            'user_id' => $r->user_id,
            'user_name' => $r->user?->full_name,
            'total_hours' => (float) $r->total_hours,
            'entries' => $r->entries,
        ]);
    }

    private function rapportTicketsParStatus()
    {
        return Ticket::selectRaw('status, priority, COUNT(*) as count')
            ->groupBy('status', 'priority')
            ->get();
    }

    private function rapportContrats()
    {
        return Contrat::with(['projet', 'client'])->get()->map(fn($c) => [
            'id' => $c->id,
            'reference' => $c->reference,
            'project_name' => $c->projet?->name,
            'client_name' => $c->client?->full_name,
            'hours' => $c->hours,
            'consumed_hours' => $c->consumed_hours,
            'remaining_hours' => $c->remaining_hours,
            'status' => $c->status,
        ]);
    }
}
