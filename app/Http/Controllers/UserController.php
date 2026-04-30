<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InviteCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // ── GET /api/users — liste tous les users (admin) ──
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        return response()->json(User::latest()->get());
    }

    // ── GET /api/users/pending — comptes en attente (admin) ──
    public function pending(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        return response()->json(
            User::where('status', 'pending')->latest()->get()
        );
    }

    // ── POST /api/users/{user}/approve — approuver un compte (admin) ──
    public function approve(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        $user->update(['status' => 'approved']);
        return response()->json(['message' => 'Compte approuvé', 'user' => $user->fresh()]);
    }

    // ── POST /api/users/{user}/reject — refuser un compte (admin) ──
    public function reject(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        $user->update(['status' => 'rejected']);
        return response()->json(['message' => 'Compte refusé', 'user' => $user->fresh()]);
    }

    // ── GET /api/encadrants — liste des encadrants approuvés ──
    public function encadrants(Request $request)
    {
        $encadrants = User::where('role', 'encadrant')
            ->where('status', 'approved')
            ->select('id', 'nom', 'email', 'grade', 'departement', 'filiere')
            ->orderBy('nom')
            ->get();
        return response()->json($encadrants);
    }

    // ── GET /api/invite-codes — liste des codes (admin) ──
    public function inviteCodes(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        return response()->json(
            InviteCode::with('usedBy:id,nom,email')
                      ->orderBy('created_at', 'desc')
                      ->get()
        );
    }

    // ── POST /api/invite-codes — générer un code (admin) ──
    public function generateInviteCode(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:etudiant,encadrant,jury',
        ]);

        $code = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));

        $invite = InviteCode::create([
            'code'       => $code,
            'role'       => $validated['role'],
            'used'       => false,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['invite' => $invite], 201);
    }

    // ── DELETE /api/invite-codes/{code} — supprimer un code (admin) ──
    public function deleteInviteCode(Request $request, InviteCode $inviteCode)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        $inviteCode->delete();
        return response()->json(['message' => 'Code supprimé']);
    }

    // ── PUT /api/users/{user} — modifier role/filière (admin) ──
    public function update(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'role'        => 'sometimes|in:etudiant,encadrant,jury,admin',
            'filiere'     => 'sometimes|nullable|string|max:255',
            'niveau'      => 'sometimes|nullable|string|max:100',
            'grade'       => 'sometimes|nullable|string|max:100',
            'departement' => 'sometimes|nullable|string|max:255',
            'status'      => 'sometimes|in:pending,approved,rejected',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour',
            'user'    => $user->fresh(),
        ]);
    }

    // ── DELETE /api/users/{user} ──
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte'], 422);
        }
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}