<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    // RF-025: Ver perfil
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');

        return Inertia::render('Profile/Show', [
            'user' => $user,
        ]);
    }

    // RF-025: Editar perfil
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'            => 'required|string|max:100',
            'phone'           => 'nullable|string|unique:users,phone,' . $user->id,
            'address'         => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:100',
            'department'      => 'nullable|string|max:100',
            'document_type'   => 'nullable|string',
            'document_number' => 'nullable|string|max:20',
            'birth_date'      => 'nullable|date',
        ]);

        // Actualizar datos del usuario
        $user->update([
            'name'  => $request->name,
            'phone' => $request->phone,
        ]);

        // Actualizar o crear perfil
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'address'         => $request->address,
                'city'            => $request->city,
                'department'      => $request->department,
                'document_type'   => $request->document_type,
                'document_number' => $request->document_number,
                'birth_date'      => $request->birth_date,
            ]
        );

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    // RF-027: Cambio de contrasena
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contrasena actual es incorrecta.']);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return back()->with('success', 'Contrasena actualizada correctamente.');
    }
}