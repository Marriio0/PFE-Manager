<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use App\Models\Validation;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function index(Rapport $rapport)
    {
        return response()->json([
            'validations' => $rapport->validations()->with('user')->latest()->get()
        ]);
    }

    public function store(Request $request, Rapport $rapport)
    {
        $user = $request->user();

        // Seuls jury et admin peuvent valider
        if (!in_array($user->role, ['jury', 'admin'])) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Jury: uniquement les rapports de sa filière
        if ($user->role === 'jury') {
            if (!$user->filiere || $rapport->etudiant->filiere !== $user->filiere) {
                return response()->json(['message' => 'Non autorisé — rapport hors de votre filière'], 403);
            }
        }

        $validated = $request->validate([
            'decision'    => 'required|in:en_attente,valide,refuse',
            'commentaire' => 'nullable|string',
        ]);

        $validation = Validation::create([
            'rapport_id'    => $rapport->id,
            'user_id'       => $user->id,
            'decision'      => $validated['decision'],
            'commentaire'   => $validated['commentaire'] ?? null,
            'date_decision' => now()->toDateString(),
        ]);

        return response()->json([
            'message'    => 'Validation ajoutée avec succès',
            'validation' => $validation->load('user', 'rapport'),
        ], 201);
    }

    public function destroy(Validation $validation)
    {
        $validation->delete();
        return response()->json(['message' => 'Validation supprimée avec succès']);
    }
}