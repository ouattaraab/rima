<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if ($user && $user->isLocked()) {
            return back()->withErrors(['username' => 'Compte temporairement bloque. Reessayez dans quelques minutes.'])->withInput();
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['username' => 'Ce compte est desactive.'])->withInput();
            }

            if ($user->isAgentCidec()) {
                Auth::logout();
                return back()->withErrors(['username' => 'Les agents CIDEC doivent utiliser l\'application mobile.'])->withInput();
            }

            $user->update(['last_login_at' => now(), 'failed_login_attempts' => 0, 'locked_until' => null]);

            $this->auditService->log('login', 'auth', $user->id, $request, 200, 'web', $user->id);

            $redirectTo = $user->isFinance() ? '/vehicles' : '/dashboard';
            return redirect()->intended($redirectTo);
        }

        if ($user) {
            $attempts = $user->failed_login_attempts + 1;
            $update = ['failed_login_attempts' => $attempts];
            if ($attempts >= 3) {
                $update['locked_until'] = now()->addMinutes(15);
                $user->update($update);
                $this->notifyAdminsAccountLocked($user);
            } else {
                $user->update($update);
            }
        }

        return back()->withErrors(['username' => 'Identifiants incorrects.'])->withInput();
    }

    private function notifyAdminsAccountLocked(User $lockedUser): void
    {
        $admins = User::where('role', 'admin_sodeci')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'account_locked',
                'title' => 'Compte verrouille',
                'message' => "Le compte de {$lockedUser->full_name} ({$lockedUser->username}) a ete verrouille apres 3 tentatives de connexion echouees.",
                'data' => [
                    'locked_user_id' => $lockedUser->id,
                    'locked_user_username' => $lockedUser->username,
                    'locked_until' => $lockedUser->locked_until?->toIso8601String(),
                ],
            ]);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $this->auditService->log('logout', 'auth', $user->id, $request, 200, 'web', $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
