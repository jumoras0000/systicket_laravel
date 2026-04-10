<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Validation;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function index()
    {
        return view('pages.validations.index');
    }

    // --- API ---
    public function apiToValidate(Request $request)
    {
        $user = $request->user();

        $query = Ticket::where('status', 'to-validate')
            ->with(['projet', 'creator', 'temps']);

        if ($user->isClient()) {
            $query->whereHas('projet', fn($q) => $q->where('client_id', $user->id));
        }

        $tickets = $query->orderByDesc('updated_at')->get();
        return response()->json(['success' => true, 'data' => $tickets]);
    }

    public function apiValidate(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:validated,refused',
            'comment' => 'nullable|string',
        ]);

        $user = $request->user();

        Validation::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'status' => $request->status,
            'comment' => $request->comment,
        ]);

        if ($request->status === 'validated') {
            $ticket->update(['status' => 'validated']);
        } else {
            $ticket->update(['status' => 'refused']);
        }

        return response()->json(['success' => true]);
    }

    public function apiHistory(Ticket $ticket)
    {
        $validations = Validation::where('ticket_id', $ticket->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $validations]);
    }
}
