<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\BladePermission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:blade_permissions,id'
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        $role->permissions()->attach($validated['permissions']);

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'role' => $role
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:blade_permissions,id'
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        $role->permissions()->sync($validated['permissions']);

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'role' => $role
        ]);
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el rol porque tiene usuarios asignados'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado exitosamente'
        ]);
    }
}
