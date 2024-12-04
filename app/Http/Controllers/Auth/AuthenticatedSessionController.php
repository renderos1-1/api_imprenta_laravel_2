<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Repositories\UserLogRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected $userLogRepository;

    public function __construct(UserLogRepository $userLogRepository)
    {
        $this->userLogRepository = $userLogRepository;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validate the DUI format
        $request->validate([
            'dui' => ['required', 'string', 'regex:/^[0-9]{8}-[0-9]$/'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'dui' => $request->input('dui'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Log the login activity
            $this->userLogRepository->log($request->input('dui'), 'login');

            // Update last_login timestamp
            Auth::user()->update(['last_login' => now()]);

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return back()->withErrors([
            'dui' => trans('auth.failed'),
        ])->onlyInput('dui');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log the logout activity before destroying the session
        if (Auth::check()) {
            $this->userLogRepository->log(Auth::user()->dui, 'logout');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
