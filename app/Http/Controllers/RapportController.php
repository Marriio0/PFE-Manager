<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use App\Models\VersionRapport;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Rapport::with([
            'etudiant', 'encadrant', 'versions',
            'commentaires.user', 'validations.user',
        ]);

        if ($user->role === 'etudiant') {
            // Etudiant: ses propres rapports uniquement
            $query->where('etudiant_id', $user->id);
        } elseif ($user->role === 'encadrant') {
            // Encadrant: uniquement les rapports où il est assigné comme encadrant
            $query->where('encadrant_id', $user->id);
        } elseif ($user->role === 'jury') {
            // Jury: tous les rapports de sa filière
            if ($user->filiere) {
                $query->whereHas('etudiant', fn($q) => $q->where('filiere', $user->filiere));
            } else {
                return response()->json([]);
            }
        }
        // admin: tous les rapports

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'encadrant_id' => 'nullable|exists:users,id',
            'file_url'     => 'required|string',
        ]);

        $rapport = Rapport::create([
            'titre'        => $validated['titre'],
            'description'  => $validated['description'] ?? null,
            'date_depot'   => now()->toDateString(),
            'statut'       => 'soumis',
            'etudiant_id'  => $request->user()->id,
            'encadrant_id' => $validated['encadrant_id'] ?? null,
        ]);

        VersionRapport::create([
            'rapport_id'     => $rapport->id,
            'numero_version' => 1,
            'file_url'       => $validated['file_url'],
            'date_upload'    => now()->toDateString(),
        ]);

        return response()->json([
            'rapport' => $rapport->load(['etudiant','encadrant','versions','commentaires.user','validations.user']),
        ], 201);
    }

    public function show(Rapport $rapport)
    {
        return response()->json(
            $rapport->load(['etudiant','encadrant','versions','commentaires.user','validations.user'])
        );
    }

    public function update(Request $request, Rapport $rapport)
    {
        $user = $request->user();

        if ($user->role === 'encadrant') {
            // Encadrant ne peut agir que sur ses propres rapports assignés
            if ($rapport->encadrant_id !== $user->id) {
                return response()->json(['message' => 'Non autorisé — ce rapport ne vous est pas assigné'], 403);
            }
            if ($request->has('verified_by_encadrant')) {
                $rapport->update([
                    'verified_by_encadrant' => $request->verified_by_encadrant,
                    'verified_at'           => $request->verified_by_encadrant ? now() : null,
                ]);
                return response()->json([
                    'message' => 'OK',
                    'rapport' => $rapport->fresh(['etudiant','encadrant','versions','commentaires.user','validations.user'])
                ]);
            }
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Jury: peut seulement agir sur les rapports de sa filière
        if ($user->role === 'jury') {
            if (!$user->filiere || $rapport->etudiant->filiere !== $user->filiere) {
                return response()->json(['message' => 'Non autorisé — rapport hors de votre filière'], 403);
            }
        }

        $validated = $request->validate([
            'statut'       => 'sometimes|in:soumis,en_correction,resoumis,valide,refuse',
            'encadrant_id' => 'sometimes|nullable|exists:users,id',
        ]);

        $rapport->update($validated);

        return response()->json([
            'rapport' => $rapport->fresh(['etudiant','encadrant','versions','commentaires.user','validations.user'])
        ]);
    }

    public function destroy(Rapport $rapport)
    {
        $rapport->delete();
        return response()->json(['message' => 'Supprimé']);
    }
}