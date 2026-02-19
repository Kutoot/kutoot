<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpSendRequest;
use App\Http\Requests\Auth\OtpVerifyRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OtpLoginController extends Controller
{
    public function __construct(public OtpService $otpService) {}

    public function create(): Response
    {
        return Inertia::render('Auth/OtpLogin', [
            'status' => session('status'),
            'debugOtp' => session('debugOtp'),
        ]);
    }

    public function sendOtp(OtpSendRequest $request): RedirectResponse
    {
        $identifier = $request->validated('identifier');
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        $user = User::query()
            ->when($isEmail, fn ($q) => $q->where('email', $identifier))
            ->when(! $isEmail, fn ($q) => $q->where('mobile', $identifier))
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $isEmail ? strstr($identifier, '@', true) : 'User ' . $identifier,
                'email' => $isEmail ? $identifier : null,
                'mobile' => $isEmail ? null : $identifier,
            ]);

            event(new Registered($user));
        }

        $otp = $this->otpService->generateOtp($user);
        $this->otpService->sendOtp($user, $otp, $isEmail ? 'email' : 'mobile');

        $flash = ['status' => 'OTP sent successfully! Check your '.($isEmail ? 'email' : 'phone').'.'];

        if (!app()->isProduction()) {
            $flash['debugOtp'] = $otp;
        }

        return back()->with($flash);
    }

    public function verifyOtp(OtpVerifyRequest $request): RedirectResponse
    {
        $identifier = $request->validated('identifier');
        $otp = $request->validated('otp');
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        $user = User::query()
            ->when($isEmail, fn ($q) => $q->where('email', $identifier))
            ->when(! $isEmail, fn ($q) => $q->where('mobile', $identifier))
            ->first();

        if (! $user) {
            // Fallback user creation if something went wrong between send/verify
            $user = User::create([
                'name' => $isEmail ? strstr($identifier, '@', true) : 'User ' . $identifier,
                'email' => $isEmail ? $identifier : null,
                'mobile' => $isEmail ? null : $identifier,
            ]);

            event(new Registered($user));
        }

        if (! $this->otpService->verifyOtp($user, $otp)) {
            throw ValidationException::withMessages([
                'otp' => __('Invalid or expired OTP. Please try again.'),
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
