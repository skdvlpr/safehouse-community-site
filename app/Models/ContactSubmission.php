<?php

namespace App\Models;

use Database\Factories\ContactSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    /** @use HasFactory<ContactSubmissionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'desk',
        'subject',
        'message',
        'status',
        'ip_hash',
        'user_agent_hash',
        'gdpr_consent_at',
        'replied_at',
        'correlation_token',
        'outbound_message_id',
        'crm_case_id',
        'crm_lead_id',
        'crm_link_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gdpr_consent_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }
}
