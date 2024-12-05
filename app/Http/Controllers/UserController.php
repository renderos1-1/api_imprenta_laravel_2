<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with('role')->get(); // Load all users with their roles
        $roles = Role::all(); // Get all roles

        return view('adminuser', compact('users', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'dui' => [
                'required',
                'string',
                'regex:/^[0-9]{8}-[0-9]$/',
                'unique:users,dui'
            ],
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean'
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'dui' => $validated['dui'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => auth()->id()
        ]);

        return response()->json(['message' => 'Usuario creado exitosamente', 'user' => $user]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'dui' => [
                'required',
                'string',
                'regex:/^[0-9]{8}-[0-9]$/',
                Rule::unique('users')->ignore($user->id)
            ],
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean'
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return response()->json(['message' => 'Usuario actualizado exitosamente', 'user' => $user]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }
}
