<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use Illuminate\Http\Request;

class FiliereController extends Controller
{
    // GET /api/filieres — accessible à tous les utilisateurs connectés
    public function index()
    {
        return response()->json(Filiere::orderBy('nom')->get());
    }

    // POST /api/filieres — admin uniquement
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'nom'         => 'required|string|max:255|unique:filieres,nom',
            'description' => 'nullable|string|max:500',
        ]);

        $filiere = Filiere::create($validated);

        return response()->json(['filiere' => $filiere], 201);
    }

    // DELETE /api/filieres/{filiere} — admin uniquement
    public function destroy(Request $request, Filiere $filiere)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $filiere->delete();

        return response()->json(['message' => 'Filière supprimée']);
    }
}