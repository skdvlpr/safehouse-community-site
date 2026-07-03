<?php

namespace App\Services;

use App\Models\ContactSubmission;
use Illuminate\Http\Request;

class ContactSubmissionService
{
    /**
     * @param  array{name: string, email: string, message: string, desk: string}  $data
     */
    public function store(array $data, Request $request): ContactSubmission
    {
        $submission = ContactSubmission::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'desk' => $data['desk'],
            'message' => $data['message'],
            'status' => 'new',
            'ip_hash' => self::hashIp($request->ip()),
            'user_agent_hash' => self::hashUserAgent($request->userAgent()),
            'gdpr_consent_at' => now(),
            'crm_link_status' => 'pending',
        ]);

        app(ContactSubmissionNotifier::class)->notify($submission);

        return $submission;
    }

    public static function hashIp(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        return hash('sha256', $ip);
    }

    public static function hashUserAgent(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        return hash('sha256', $userAgent);
    }
}
