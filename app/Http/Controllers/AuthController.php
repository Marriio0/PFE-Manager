<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InviteCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $role = $request->input('role');
        $needsInvite = in_array($role, ['encadrant', 'jury']);

        $validated = $request->validate([
            'nom'                   => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
            'role'                  => 'required|in:etudiant,encadrant,jury',
            'invite_code'           => $needsInvite ? 'required|string' : 'nullable|string',
            'cne'                   => 'nullable|string|max:50',
            'filiere'               => 'nullable|string|max:255',
            'niveau'                => 'nullable|string|max:100',
            'grade'                 => 'nullable|string|max:100',
            'departement'           => 'nullable|string|max:255',
        ]);

        // Vérifier le code d'invitation (seulement pour encadrant et jury)
        $invite = null;
        if ($needsInvite) {
            $invite = InviteCode::where('code', $validated['invite_code'])
                                ->where('used', false)
                                ->first();

            if (!$invite) {
                return response()->json([
                    'errors' => ['invite_code' => ['Code d\'invitation invalide ou déjà utilisé.']]
                ], 422);
            }

            if ($invite->role !== $validated['role']) {
                return response()->json([
                    'errors' => ['invite_code' => ['Ce code n\'est pas valide pour le rôle "' . $validated['role'] . '".']]
                ], 422);
            }
        }

        // Créer le compte en "pending"
        $user = User::create([
            'nom'         => $validated['nom'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'role'        => $validated['role'],
            'status'      => 'pending', // attend approbation admin
            'cne'         => $validated['cne'] ?? null,
            'filiere'     => $validated['filiere'] ?? null,
            'niveau'      => $validated['niveau'] ?? null,
            'grade'       => $validated['grade'] ?? null,
            'departement' => $validated['departement'] ?? null,
        ]);

        // Marquer le code comme utilisé (encadrant/jury uniquement)
        if ($invite) {
            $invite->update(['used' => true, 'used_by' => $user->id]);
        }

        return response()->json([
            'message' => 'Compte créé avec succès. En attente d\'approbation par l\'administrateur.',
            'status'  => 'pending',
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // Vérifier le statut du compte
        if ($user->status === 'pending') {
            return response()->json([
                'message' => 'Votre compte est en attente d\'approbation par l\'administrateur.',
                'status'  => 'pending',
            ], 403);
        }

        if ($user->status === 'rejected') {
            return response()->json([
                'message' => 'Votre compte a été refusé. Contactez l\'administrateur.',
                'status'  => 'rejected',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'    => 'Connexion réussie',
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }
}