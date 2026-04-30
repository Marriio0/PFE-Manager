<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use App\Models\VersionRapport;
use Illuminate\Http\Request;

class VersionRapportController extends Controller
{
    public function index(Rapport $rapport)
    {
        return response()->json([
            'versions' => $rapport->versions()->latest()->get()
        ]);
    }

  public function store(Request $request, Rapport $rapport)
{
    $validated = $request->validate([
        'numero_version' => 'required|integer|min:1',
        'file_url' => 'required|string|max:255',
    ]);

    $version = VersionRapport::create([
        'rapport_id' => $rapport->id,
        'numero_version' => $validated['numero_version'],
        'file_url' => $validated['file_url'],
        'date_upload' => now()->format('Y-m-d'),
    ]);

    return response()->json([
        'message' => 'Version enregistrée avec succès',
        'version' => $version,
    ], 201);
}
    public function destroy(VersionRapport $versionRapport)
    {
        $versionRapport->delete();

        return response()->json([
            'message' => 'Version supprimée avec succès'
        ]);
    }
}