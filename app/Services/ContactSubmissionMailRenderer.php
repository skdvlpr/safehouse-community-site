<?php

namespace App\Services;

use App\Models\ContactSubmission;
use App\Support\ContactDeskOptions;

class ContactSubmissionMailRenderer
{
    public function __construct(
        private readonly ContactSportelloMailSettings $templates,
    ) {}

    /**
     * @return array{html: string, text: string, subject: string}
     */
    public function render(ContactSubmission $submission, string $locale = 'it'): array
    {
        $variables = $this->variables($submission);
        $htmlBody = $this->replacePlaceholders($this->templates->bodyHtmlForLocale($locale), $variables);
        $crmBlock = $this->crmMetadataBlock($submission);

        $html = $this->wrapHtml($htmlBody, $crmBlock['html']);
        $text = $this->plainBody($htmlBody, $crmBlock['text']);

        $subjectTemplate = $this->templates->subjectForLocale($locale);
        $subject = $this->replacePlaceholders($subjectTemplate, $variables);

        if (trim($subject) === '') {
            $token = trim((string) $submission->correlation_token);
            $subject = $token !== ''
                ? '[SH-'.$token.'] Nuovo messaggio — '.$submission->name
                : 'Nuovo messaggio dal modulo contatti — '.$submission->name;
        }

        return [
            'html' => $html,
            'text' => $text,
            'subject' => $subject,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function variables(ContactSubmission $submission): array
    {
        $desk = ContactDeskOptions::deskConfig($submission->desk);
        $deskLabel = trim((string) ($desk['label'] ?? ''));
        $caseType = trim((string) ($desk['case_type'] ?? ContactDeskOptions::caseTypeForDesk($submission->desk) ?? ''));
        $token = trim((string) $submission->correlation_token);
        $reference = $token !== '' ? '[SH-'.$token.']' : '';
        $submittedAt = $submission->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i')
            ?? now()->format('d/m/Y H:i');

        return [
            '{{name}}' => trim($submission->name),
            '{{email}}' => trim($submission->email),
            '{{message}}' => trim($submission->message),
            '{{desk_label}}' => $deskLabel !== '' ? $deskLabel : 'Sportello',
            '{{case_type}}' => $caseType !== '' ? $caseType : 'Other',
            '{{reference}}' => $reference,
            '{{reference_token}}' => $token,
            '{{submitted_at}}' => $submittedAt,
            '{{request_id}}' => (string) $submission->id,
        ];
    }

    /**
     * CRM intake requires these exact plain-text lines in the email body.
     *
     * @return array{html: string, text: string}
     */
    public function crmMetadataBlock(ContactSubmission $submission): array
    {
        $desk = ContactDeskOptions::deskConfig($submission->desk);
        $deskLabel = trim((string) ($desk['label'] ?? ''));
        $caseType = trim((string) ($desk['case_type'] ?? ContactDeskOptions::caseTypeForDesk($submission->desk) ?? 'Other'));

        if ($deskLabel === '') {
            $deskLabel = 'Sportello';
        }

        if ($caseType === '') {
            $caseType = 'Other';
        }

        $token = trim((string) $submission->correlation_token);
        $lines = array_filter([
            'Nome: '.trim($submission->name),
            'Email: '.trim($submission->email),
            'Sportello: '.$deskLabel,
            'Tipo segnalazione: '.$caseType,
            $token !== '' ? 'Riferimento: [SH-'.$token.']' : null,
        ]);

        $text = implode("\n", $lines);

        $htmlItems = implode('', array_map(
            static fn (string $line): string => '<li>'.e($line).'</li>',
            $lines,
        ));

        $html = <<<HTML
<hr style="border:0;border-top:1px solid #e4e4e7;margin:24px 0;">
<p style="font-size:12px;color:#71717a;margin:0 0 8px;">Dati per CRM (non rimuovere)</p>
<ul style="font-size:13px;color:#18181b;margin:0;padding-left:20px;">{$htmlItems}</ul>
HTML;

        return [
            'html' => $html,
            'text' => $text,
        ];
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function replacePlaceholders(string $template, array $variables): string
    {
        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    private function wrapHtml(string $bodyHtml, string $crmHtml): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="it">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:24px;background:#f4f4f5;font-family:system-ui,-apple-system,sans-serif;color:#18181b;">
<div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:16px;padding:28px;border:1px solid #e4e4e7;">
{$bodyHtml}
{$crmHtml}
</div>
</body>
</html>
HTML;
    }

    private function plainBody(string $htmlBody, string $crmText): string
    {
        $text = trim(html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />', '</li>'], ["\n\n", "\n", "\n", "\n", "\n"], $htmlBody)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text."\n\n".$crmText);
    }
}
