<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // GET /api/users — admin فقط
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json(User::latest()->get());
    }

    // GET /api/encadrants — liste des encadrants (pour les étudiants lors de la soumission)
    public function encadrants(Request $request)
    {
        $encadrants = User::where('role', 'encadrant')
            ->select('id', 'nom', 'email', 'grade', 'departement', 'filiere')
            ->orderBy('nom')
            ->get();

        return response()->json($encadrants);
    }

    // PUT /api/users/{user} — تبديل الرول + filiere
    public function update(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'role'    => 'sometimes|in:etudiant,encadrant,jury,admin',
            'filiere' => 'sometimes|nullable|string|max:255',
            'niveau'  => 'sometimes|nullable|string|max:100',
            'grade'   => 'sometimes|nullable|string|max:100',
            'departement' => 'sometimes|nullable|string|max:255',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user->fresh(),
        ]);
    }

    // DELETE /api/users/{user} — حذف مستخدم
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    }
}