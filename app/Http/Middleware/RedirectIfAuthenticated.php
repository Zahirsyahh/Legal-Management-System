<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // 🔥 DEBUG: Log untuk melihat apa yang terjadi
                \Log::info('RedirectIfAuthenticated triggered', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray(),
                    'path' => $request->path()
                ]);
                
                // 🔥 SEMUA ROLE HARUS DICOVER
                if ($user->hasRole('admin')) {
                    return redirect()->route('admin.dashboard');
                }
                
                if ($user->hasRole('legal')) {
                    return redirect()->route('legal.dashboard');
                }
                
                // Finance roles
                if ($user->hasRole('fin') || $user->hasRole('admin_fin')) {
                    // Bisa bedakan admin vs staff jika perlu
                    if ($user->hasRole('admin_fin')) {
                        return redirect()->route('finance.dashboard'); // admin dashboard
                    } else {
                        return redirect()->route('finance.dashboard.staff'); // staff dashboard
                    }
                }
                
                // Accounting roles
                if ($user->hasRole('acc') || $user->hasRole('admin_acc')) {
                    if ($user->hasRole('admin_acc')) {
                        return redirect()->route('accounting.dashboard');
                    } else {
                        return redirect()->route('accounting.dashboard.staff');
                    }
                }
                
                // Tax roles
                if ($user->hasRole('tax') || $user->hasRole('admin_tax')) {
                    if ($user->hasRole('admin_tax')) {
                        return redirect()->route('tax.dashboard');
                    } else {
                        return redirect()->route('tax.dashboard.staff');
                    }
                }
                
                if ($user->hasRole('user')) {
                    return redirect()->route('dashboard');
                }
                
                // 🔥 FALLBACK: Jika tidak ada role yang match, redirect ke home
                \Log::warning('No role matched for user', [
                    'user_id' => $user->id,
                    'roles' => $user->getRoleNames()->toArray()
                ]);
                
                return redirect('/home');
            }
        }

        return $next($request);
    }
}