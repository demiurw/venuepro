<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialiteController extends Controller
{
    /**
     * Redirect to the OAuth provider
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth provider callback
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['oauth' => 'Unable to authenticate with ' . ucfirst($provider) . '. Please try again.']);
        }

        return $this->handleSocialUser($socialUser, $provider);
    }

    /**
     * Handle the authenticated social user
     */
    protected function handleSocialUser($socialUser, string $provider): RedirectResponse
    {
        return DB::transaction(function () use ($socialUser, $provider) {
            // Check if user exists by email
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Update existing user with OAuth info
                $this->updateUserOAuthInfo($user, $socialUser, $provider);
            } else {
                // Create new user
                $user = $this->createUserFromSocial($socialUser, $provider);
                event(new Registered($user));
            }

            // Log the user in
            Auth::login($user, true);
            $user->recordLogin();

            // Set tenant context if user has company
            if ($user->company_id) {
                session(['company_id' => $user->company_id]);
            }

            return redirect()->intended(route('dashboard'));
        });
    }

    /**
     * Update existing user with OAuth information
     */
    protected function updateUserOAuthInfo(User $user, $socialUser, string $provider): void
    {
        $oauthProviders = $user->oauth_providers ?? [];
        
        // Add this provider if not already present
        if (!in_array($provider, $oauthProviders)) {
            $oauthProviders[] = $provider;
        }

        $user->update([
            'auth_method' => 'oauth',
            'oauth_providers' => $oauthProviders,
            'oauth_id' => $socialUser->getId(),
            'email_verified_at' => $user->email_verified_at ?? now(), // Auto-verify email for OAuth
        ]);
    }

    /**
     * Create a new user from social authentication
     */
    protected function createUserFromSocial($socialUser, string $provider): User
    {
        // Extract name parts
        $name = $socialUser->getName() ?? '';
        $nameParts = explode(' ', trim($name), 2);
        $firstName = $nameParts[0] ?? 'User';
        $lastName = $nameParts[1] ?? '';

        // For new OAuth users, we need to determine their company context
        // This could be enhanced with company domain matching or invitation system
        $defaultCompany = $this->getDefaultCompanyForNewUser($socialUser->getEmail());

        return User::create([
            'email' => $socialUser->getEmail(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'auth_method' => 'oauth',
            'oauth_providers' => [$provider],
            'oauth_id' => $socialUser->getId(),
            'email_verified_at' => now(), // Auto-verify email for OAuth
            'status' => 'active', // OAuth users are auto-activated
            'user_type' => 'invitee', // Default role
            'company_id' => $defaultCompany?->id,
            'role_id' => $this->getDefaultRoleId(),
        ]);
    }

    /**
     * Get default company for new OAuth user
     * This is a placeholder - in production you might:
     * - Match by email domain
     * - Use invitation system
     * - Allow user to select/create company
     */
    protected function getDefaultCompanyForNewUser(string $email): ?Company
    {
        // Extract domain from email
        $domain = Str::after($email, '@');
        
        // Try to find company by domain (if you have domain matching)
        // return Company::where('domain', $domain)->first();
        
        // For now, return null - user will need to be assigned to a company
        return null;
    }

    /**
     * Get default role ID for new users
     */
    protected function getDefaultRoleId(): int
    {
        // Return the default role ID - adjust based on your role setup
        // This should match your roles table default role
        return 1; // Assuming role ID 1 is the default user role
    }

    /**
     * Validate the OAuth provider
     */
    protected function validateProvider(string $provider): void
    {
        if (!in_array($provider, ['google', 'microsoft'])) {
            abort(404, 'Provider not supported');
        }
    }
}