<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function edit($user_id)
    {

        $user = User::findOrFail($user_id);

        // dd($user);
        return Inertia::render('User/Edit', [
            'user' => $user
        ]);

    }

    public function update(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'ativo' => 'required|boolean',
        ]);

        $user->update($validated);

        return redirect()->route('dashboard')->with('success', 'Usuário atualizado com sucesso!');
    }
}
