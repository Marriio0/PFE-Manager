<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use App\Models\Commentaire;
use Illuminate\Http\Request;

class CommentaireController extends Controller
{
    public function index(Rapport $rapport)
    {
        return response()->json(
            $rapport->commentaires()->with('user')->latest()->get()
        );
    }

    public function store(Request $request, Rapport $rapport)
    {
        $user = $request->user();

        // Encadrant: peut commenter uniquement ses propres rapports assignés
        if ($user->role === 'encadrant' && $rapport->encadrant_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé — ce rapport ne vous est pas assigné'], 403);
        }

        // Jury: peut commenter uniquement les rapports de sa filière
        if ($user->role === 'jury') {
            if (!$user->filiere || $rapport->etudiant->filiere !== $user->filiere) {
                return response()->json(['message' => 'Non autorisé — rapport hors de votre filière'], 403);
            }
        }

        // Etudiant: ne peut pas commenter
        if ($user->role === 'etudiant') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'contenu' => 'required|string',
            'type'    => 'nullable|string|in:feedback,correction,remarque,evaluation',
        ]);

        $commentaire = Commentaire::create([
            'rapport_id' => $rapport->id,
            'user_id'    => $user->id,
            'type'       => $validated['type'] ?? 'feedback',
            'contenu'    => $validated['contenu'],
        ]);

        return response()->json([
            'message'     => 'Commentaire ajouté avec succès',
            'commentaire' => $commentaire->load('user'),
        ], 201);
    }

    public function destroy(Commentaire $commentaire)
    {
        $commentaire->delete();
        return response()->json(['message' => 'Commentaire supprimé avec succès']);
    }
}