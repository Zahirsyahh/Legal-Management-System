<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterDepartment;
use App\Models\MasterJabatan;
use App\Models\TblUser;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class MasterDepartmentController extends Controller
{
    // ── Helper: always load all three counts ────────────────────────────
    private function allCounts(): array
    {
        return [
            'departmentCount' => MasterDepartment::count(),
            'jabatanCount'    => MasterJabatan::count(),
            'userCount'       => TblUser::count(),
        ];
    }

    public function index(Request $request)
    {
        $type      = $request->get('type', 'department');
        $counts    = $this->allCounts();
        $isAjax    = $request->ajax();

        // ── DEPARTMENTS ──────────────────────────────────────────────────
        if ($type === 'department') {
            $query = MasterDepartment::query();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('nama_departemen', 'like', "%{$s}%")
                      ->orWhere('kode_pendek', 'like', "%{$s}%");
                });
            }

            $sortBy    = $request->get('sort', 'nama_departemen');
            $sortOrder = $request->get('order', 'asc');
            $validSorts = ['nama_departemen', 'kode_pendek', 'kode_departemen', 'created_at'];
            if (in_array($sortBy, $validSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $departments = $query->paginate(15)->withQueryString();

            if ($isAjax) {
                return view('departments.admin.master-departments._table_partial',
                    array_merge(compact('departments', 'type'), $counts));
            }

            return view('departments.admin.master-departments.index',
                array_merge(compact('departments', 'type'), $counts));
        }

        // ── JABATAN ──────────────────────────────────────────────────────
        if ($type === 'jabatan') {
            $query = MasterJabatan::query();

            if ($request->filled('search')) {
                $query->where('nama_jabatan', 'like', '%' . $request->search . '%');
            }

            // Tambahkan sorting dinamis
            $sortBy    = $request->get('sort', 'nama_jabatan');
            $sortOrder = $request->get('order', 'asc');
            $validSorts = ['nama_jabatan', 'kode_jabatan', 'level_jabatan', 'created_at'];
            
            if (in_array($sortBy, $validSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('nama_jabatan', 'asc');
            }

            $jabatans = $query->paginate(15)->withQueryString();

            if ($isAjax) {
                return view('departments.admin.master-departments._table_partial',
                    array_merge(compact('jabatans', 'type'), $counts));
            }

            return view('departments.admin.master-departments.index',
                array_merge(compact('jabatans', 'type'), $counts));
        }

        // ── USERS ────────────────────────────────────────────────────────
        if ($type === 'user') {
            $query = TblUser::query();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('nama_user',  'like', "%{$s}%")
                      ->orWhere('username', 'like', "%{$s}%")
                      ->orWhere('email',    'like', "%{$s}%")
                      ->orWhere('nip',      'like', "%{$s}%");
                });
            }

            if ($request->filled('department')) {
                $query->where('kode_department', $request->department);
            }

            $sortBy    = $request->get('sort', 'nama_user');
            $sortOrder = $request->get('order', 'asc');
            $validSorts = ['id_user', 'nama_user', 'username', 'email', 'jabatan', 'kode_department'];
            if (in_array($sortBy, $validSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $users       = $query->with('department')->paginate(15)->withQueryString();
            $departments = MasterDepartment::orderBy('nama_departemen')->get();

            if ($isAjax) {
                return view('departments.admin.master-departments._table_partial',
                    array_merge(compact('users', 'type', 'departments'), $counts));
            }

            return view('departments.admin.master-departments.index',
                array_merge(compact('users', 'type', 'departments'), $counts));
        }

        abort(404);
    }

    // ── DEPARTMENT CRUD ──────────────────────────────────────────────────
    public function create()
    {
        $type = request()->get('type', 'department');
        if ($type === 'user') {
            return $this->createUser();
        }
        return view('departments.admin.master-departments.create-user', compact('type'));
    }

    public function store(Request $request)
    {
        $type = $request->get('type', 'department');
        if ($type === 'user') {
            return $this->storeUser($request);
        }

        $validated = $request->validate([
            'kode_pendek'     => 'required|string|max:10|unique:tbl_department,kode_pendek',
            'nama_departemen' => 'required|string|max:255',
        ]);

        MasterDepartment::create($validated);

        return redirect()->route('admin.master-departments.index', ['type' => 'department'])
            ->with('success', 'Department created successfully');
    }

    public function edit($id)
    {
        $type = request()->get('type', 'department');
        if ($type === 'user') {
            return $this->editUser($id);
        }
        $department = MasterDepartment::findOrFail($id);
        return view('departments.admin.master-departments.edit', compact('department', 'type'));
    }

    public function update(Request $request, $id)
    {
        $type = $request->get('type', 'department');
        if ($type === 'user') {
            return $this->updateUser($request, $id);
        }

        $department = MasterDepartment::findOrFail($id);
        $validated  = $request->validate([
            'kode_pendek'     => 'required|string|max:10|unique:tbl_department,kode_pendek,' . $id . ',kode_departemen',
            'nama_departemen' => 'required|string|max:255',
        ]);

        $department->update($validated);

        return redirect()->route('admin.master-departments.index', ['type' => 'department'])
            ->with('success', 'Department updated successfully');
    }

    public function destroy($id)
    {
        $type = request()->get('type', 'department');
        if ($type === 'user') {
            return $this->destroyUser($id);
        }

        MasterDepartment::findOrFail($id)->delete();

        return redirect()->route('admin.master-departments.index', ['type' => 'department'])
            ->with('success', 'Department deleted successfully');
    }

    // ── USER CRUD ────────────────────────────────────────────────────────
    public function createUser()
    {
        $type        = 'user';
        $departments = MasterDepartment::orderBy('nama_departemen')->get();
        $roles       = Role::all();
        $allRoles    = Role::all();
        return view('departments.admin.master-departments.create-user',
            compact('departments', 'type', 'roles', 'allRoles'));
    }

    protected function storeUser(Request $request)
    {
        $validated = $request->validate([
            'id_user'         => 'nullable|integer|unique:tbl_user,id_user|min:1|max:4294967295',
            'nama_user'       => 'required|string|max:100',
            'username'        => 'required|string|max:50|unique:tbl_user,username',
            'email'           => 'nullable|email|max:100|unique:tbl_user,email',
            'password'        => 'nullable|string|min:6|max:50',
            'nip'             => 'nullable|string|max:11',
            'jabatan'         => 'required|string|max:100',
            'kode_department' => 'nullable|string|max:5',
            'hak_akses'       => 'nullable|integer|min:1|max:6',
            'status_karyawan' => 'required|string|in:AKTIF,TIDAK AKTIF',
            'kode_status_kepegawaian' => 'required|integer',
            'no_hp'           => 'nullable|string|max:16',
            'no_ktp'          => 'nullable|string|max:18',
            'tgl_masuk_karyawan' => 'nullable|date',
            'tgl_lahir'       => 'nullable|date',
            'tempat_lahir'    => 'nullable|string|max:100',
            'jenkel'          => 'nullable|string|in:Laki-Laki,Perempuan',
            'agama'           => 'nullable|string|max:50',
            'status_kawin'    => 'nullable|string|in:KAWIN,BELUM KAWIN,TIDAK KAWIN,-',
            'npwp'            => 'nullable|string|max:30',
            'pendidikan'      => 'nullable|string|max:50',
            'alamat_karyawan' => 'nullable|string|max:200',
            'gol_darah'       => 'nullable|string|max:3',
            'kewarganegaraan' => 'nullable|string|max:100',
            'role'            => 'nullable|string',
            'roles'           => 'nullable|array',
        ]);

        if (!$request->filled('id_user')) {
            $lastUser             = TblUser::orderBy('id_user', 'desc')->first();
            $validated['id_user'] = $lastUser ? $lastUser->id_user + 1 : 1000;
        }

        $validated['hak_akses']                = $validated['hak_akses'] ?? 2;
        $validated['status_karyawan']          = $validated['status_karyawan'] ?? 'AKTIF';
        $validated['kode_status_kepegawaian']  = $validated['kode_status_kepegawaian'] ?? 1;
        $validated['password']                 = bcrypt($request->filled('password')
            ? $request->password
            : ($validated['nip'] ?? $validated['username'] ?? 'password123'));

        try {
            $user = TblUser::create($validated);
            if ($request->filled('roles')) {
                $user->syncRoles($request->roles);
            } elseif ($request->filled('role')) {
                $user->assignRole($request->role);
            }

            return redirect()->route('admin.master-departments.index', ['type' => 'user'])
                ->with('success', 'User berhasil ditambahkan! ID: ' . $user->id_user);
        } catch (\Exception $e) {
            \Log::error('Error creating user: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    protected function editUser($id)
    {
        $user        = TblUser::findOrFail($id);
        $departments = MasterDepartment::orderBy('nama_departemen')->get();
        $type        = 'user';
        $roles       = Role::all();
        $allRoles    = Role::all();
        return view('departments.admin.master-departments.edit',
            compact('user', 'departments', 'type', 'roles', 'allRoles'));
    }

    protected function updateUser(Request $request, $id)
    {
        $user      = TblUser::findOrFail($id);
        $validated = $request->validate([
            'nama_user'       => 'required|string|max:100',
            'username'        => 'required|string|max:50|unique:tbl_user,username,' . $id . ',id_user',
            'email'           => 'nullable|email|max:100|unique:tbl_user,email,' . $id . ',id_user',
            'password'        => 'nullable|string|min:6|max:50',
            'nip'             => 'nullable|string|max:11',
            'jabatan'         => 'nullable|string|max:100',
            'kode_department' => 'nullable|string|max:5',
            'hak_akses'       => 'nullable|integer|min:1|max:6',
            'status_karyawan' => 'nullable|string|in:AKTIF,TIDAK AKTIF',
            'no_hp'           => 'nullable|string|max:16',
            'no_ktp'          => 'nullable|string|max:18',
            'tgl_masuk_karyawan' => 'nullable|date',
            'tgl_lahir'       => 'nullable|date',
            'tempat_lahir'    => 'nullable|string|max:100',
            'jenkel'          => 'nullable|string|in:Laki-Laki,Perempuan',
            'agama'           => 'nullable|string|max:50',
            'status_kawin'    => 'nullable|string|in:KAWIN,BELUM KAWIN,TIDAK KAWIN,-',
            'npwp'            => 'nullable|string|max:30',
            'pendidikan'      => 'nullable|string|max:50',
            'alamat_karyawan' => 'nullable|string|max:200',
            'role'            => 'nullable|string',
            'roles'           => 'nullable|array',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }

        try {
            $user->update($validated);
            if ($request->filled('roles')) {
                $user->syncRoles($request->roles);
            } elseif ($request->filled('role')) {
                $user->syncRoles([$request->role]);
            }

            return redirect()->route('admin.master-departments.index', ['type' => 'user'])
                ->with('success', 'User berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('Error updating user: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    protected function destroyUser($id)
    {
        try {
            $user     = TblUser::findOrFail($id);
            $userName = $user->display_name ?? $user->nama_user;
            $user->roles()->detach();
            $user->delete();

            return redirect()->route('admin.master-departments.index', ['type' => 'user'])
                ->with('success', "User '{$userName}' berhasil dihapus!");
        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()]);
        }
    }
}