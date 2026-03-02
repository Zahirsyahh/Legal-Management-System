<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TblUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = TblUser::with('roles')->orderByDesc('id_user')->paginate(10);
        $roles = Role::all();
        
        $stats = [
        'total' => $users->total(),
        'admins' => TblUser::whereHas('roles', fn($q) => $q->where('name', 'admin'))->count(),
        'legal' => TblUser::whereHas('roles', fn($q) => $q->where('name', 'legal'))->count(),
        'staff_fin' => TblUser::whereHas('roles', fn($q) => $q->where('name', 'staff_fin'))->where('status_karyawan', 'AKTIF')->count(),
        'staff_acc' => TblUser::whereHas('roles', fn($q) => $q->where('name', 'staff_acc'))->where('status_karyawan', 'AKTIF')->count(),
        'staff_tax' => TblUser::whereHas('roles', fn($q) => $q->where('name', 'staff_tax'))->where('status_karyawan', 'AKTIF')->count(),
    ];
    
        return view('admin.users.index', compact('users', 'roles', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.TblUser::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = TblUser::create([
            'nama_user' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status_karyawan' => 'AKTIF',
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(TblUser $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(TblUser $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, TblUser $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:tbl_user,email,'.$user->id_user],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->update([
            'nama_user' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(TblUser $user)
    {
        // Prevent deleting yourself
        if ($user->id_user === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}