<?php

namespace App\Services;

use App\Models\Volunteer;
use Illuminate\Http\Request;

class VolunteerService
{
    /**
     * @param  array{name: string, email: string, phone?: string|null, message?: string|null}  $data
     */
    public function store(array $data, Request $request): Volunteer
    {
        return Volunteer::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
            'ip_hash' => ContactSubmissionService::hashIp($request->ip()),
            'user_agent_hash' => ContactSubmissionService::hashUserAgent($request->userAgent()),
            'gdpr_consent_at' => now(),
        ]);
    }
}
