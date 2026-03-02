<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\TblUser;

class HrmsLoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // ========================================
        // 1. VALIDATION
        // ========================================
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // ========================================
        // 2. FIND USER IN tbl_user
        // ========================================
        $user = TblUser::where('email', $request->email)
            ->where('status_karyawan', 'AKTIF')
            ->first();

        if (!$user) {
            \Log::warning('Login failed: User not found or inactive', [
                'email' => $request->email,
            ]);
            
            return back()->withErrors([
                'email' => 'Akun tidak ditemukan atau tidak aktif',
            ])->onlyInput('email');
        }

        // ========================================
        // 3. VERIFY PASSWORD
        // ========================================
        if (!Hash::check($request->password, $user->password)) {
            \Log::warning('Login failed: Wrong password', [
                'email' => $request->email,
                'user_id' => $user->id_user,
            ]);
            
            return back()->withErrors([
                'password' => 'Password salah',
            ])->onlyInput('email');
        }

        // ========================================
        // 4. LOGIN USER (AUTH SESSION)
        // ========================================
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // ========================================
        // 5. LOAD ROLES & PERMISSIONS (EAGER LOAD)
        // ========================================
        $user = Auth::user();
        $user->load('roles', 'permissions');
        
        \Log::info('User logged in successfully', [
            'user_id' => $user->id_user,
            'email' => $user->email,
            'name' => $user->nama_user,
            'roles' => $user->getRoleNames()->toArray(),
            'permissions_count' => $user->getAllPermissions()->count(),
        ]);

        // ========================================
        // 6. REDIRECT BASED ON ROLE
        // ========================================
        
        // ADMIN role
        if ($user->hasRole('admin')) {
            return redirect()->route('dashboard'); // admin.dashboard redirect ke dashboard utama
        }
        
        // LEGAL role
        if ($user->hasRole('legal')) {
            return redirect()->route('dashboard'); // legal.dashboard redirect ke dashboard utama
        }
        
        // FINANCE - Admin dan Staff
        if ($user->hasRole('admin_fin')) {
            return redirect()->route('finance-admin.dashboard');
        }
        
        if ($user->hasRole('staff_fin')) {
            return redirect()->route('finance-staff.dashboard');
        }
        
        // ACCOUNTING - Admin dan Staff
        if ($user->hasRole('admin_acc')) {
            return redirect()->route('accounting-admin.dashboard');
        }
        
        if ($user->hasRole('staff_acc')) {
            return redirect()->route('accounting-staff.dashboard');
        }
        
        // TAX - Admin dan Staff
        if ($user->hasRole('admin_tax')) {
            return redirect()->route('tax-admin.dashboard');
        }
        
        if ($user->hasRole('staff_tax')) {
            return redirect()->route('tax-staff.dashboard');
        }
        
        // USER role (default)
        if ($user->hasRole('user')) {
            return redirect()->route('dashboard');
        }
        
        // Fallback - jika tidak ada role yang cocok
        \Log::warning('User logged in but no matching role found', [
            'user_id' => $user->id_user,
            'roles' => $user->getRoleNames()->toArray(),
        ]);
        
        // Default redirect ke dashboard utama
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $userId = Auth::id();
        $email = Auth::user()->email ?? 'unknown';
        
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        \Log::info('User logged out', [
            'user_id' => $userId,
            'email' => $email,
        ]);
        
        return redirect()->route('login');
    }
}