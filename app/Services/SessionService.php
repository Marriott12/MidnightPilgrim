<?php

namespace App\Services;

use App\Models\Session;
use App\Models\UserProfile;

class SessionService
{
    public function generateFingerprint(string $ip, string $userAgent): string
    {
        return hash('sha256', $ip . '|' . $userAgent);
    }

    public function findOrCreateProfile(string $fingerprint): UserProfile
    {
        return UserProfile::firstOrCreate(['fingerprint' => $fingerprint]);
    }

    public function findActiveSession(string $fingerprint): ?Session
    {
        return Session::where('fingerprint', $fingerprint)
            ->where('status', 'active')
            ->latest('created_at')
            ->first();
    }

    public function createSession(UserProfile $profile, string $fingerprint, string $mode): Session
    {
        return Session::create([
            'user_profile_id' => $profile->id,
            'fingerprint' => $fingerprint,
            'mode' => $mode,
            'status' => 'active',
        ]);
    }

    public function incrementSessionCounter(UserProfile $profile): void
    {
        $profile->session_count = ($profile->session_count ?? 0) + 1;
        $profile->save();
    }

    public function closeSession(Session $session): void
    {
        $session->status = 'closed';
        $session->save();
    }

    public function deleteSession(Session $session): void
    {
        $session->delete();
    }

    public function deleteUserProfile(string $fingerprint): void
    {
        $profile = UserProfile::where('fingerprint', $fingerprint)->first();
        if ($profile) {
            $profile->delete();
        }
    }

    public function resumeSession(Session $session): Session
    {
        // No-op for now, but could add logic for resuming
        return $session;
    }

    public function endSession(Session $session, UserProfile $profile, $patternEngine, $narrativeEngine): array
    {
        $patternEngine->createSnapshot($session);
        $patternEngine->updateProfileMetrics($profile, $session);
        $session->status = 'closed';
        $session->save();
        $reflection = $narrativeEngine->generateReflectionIfNeeded($profile);
        return [
            'success' => true,
            'reflection_available' => $reflection !== null,
        ];
    }
}
