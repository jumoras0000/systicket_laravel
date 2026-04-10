<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('pages.users.index');
    }

    public function create()
    {
        return view('pages.users.form');
    }

    public function edit(User $user)
    {
        return view('pages.users.form', compact('user'));
    }

    public function profil()
    {
        return view('pages.profil');
    }

    // --- API ---
    public function apiIndex(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) $query->role($request->role);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('first_name', 'like', $s)
                ->orWhere('last_name', 'like', $s)
                ->orWhere('email', 'like', $s));
        }

        $users = $query->orderBy('last_name')->get();
        return response()->json(['success' => true, 'data' => $users]);
    }

    public function apiShow(User $user)
    {
        $user->load(['projets', 'tickets']);
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,collaborateur,client',
            'status' => 'nullable|in:active,inactive',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'status' => $request->input('status', 'active'),
            'phone' => $request->phone,
        ]);

        return response()->json(['success' => true, 'id' => $user->id], 201);
    }

    public function apiUpdate(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:admin,collaborateur,client',
            'status' => 'sometimes|in:active,inactive',
            'phone' => 'nullable|string|max:20',
        ]);

        $data = $request->only(['first_name', 'last_name', 'email', 'role', 'status', 'phone']);
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $data['password'] = $request->password;
        }

        $user->update($data);
        return response()->json(['success' => true]);
    }

    public function apiDestroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Impossible de supprimer votre propre compte.'], 403);
        }

        $user->delete();
        return response()->json(['success' => true]);
    }

    public function apiProfil(Request $request)
    {
        $user = $request->user();
        $user->load(['projets', 'tickets']);
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function apiUpdateProfil(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($request->only(['first_name', 'last_name', 'email', 'phone']));
        return response()->json(['success' => true, 'data' => $user->fresh()]);
    }

    public function apiCollaborateurs()
    {
        return response()->json([
            'success' => true,
            'data' => User::equipe()->get(['id', 'first_name', 'last_name', 'email']),
        ]);
    }

    public function apiClients()
    {
        return response()->json([
            'success' => true,
            'data' => User::clients()->get(['id', 'first_name', 'last_name', 'email']),
        ]);
    }
}
