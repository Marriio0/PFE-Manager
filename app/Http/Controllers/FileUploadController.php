<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:20480', // max 20 Mo
        ]);

        $path = $request->file('file')->store('rapports', 'public');

        // Utiliser l'URL de la requête pour supporter ngrok/proxies
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        $fileUrl = $baseUrl . '/storage/' . $path;

        return response()->json([
            'message' => 'Fichier uploadé avec succès',
            'file_url' => $fileUrl,
        ]);
    }
}