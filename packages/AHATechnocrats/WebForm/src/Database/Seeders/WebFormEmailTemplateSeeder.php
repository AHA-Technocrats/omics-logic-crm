<?php

namespace AHATechnocrats\WebForm\Database\Seeders;

use AHATechnocrats\EmailTemplate\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class WebFormEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Web Form – Thank You',
                'subject' => 'Thank you for contacting us, {{name}}',
                'content' => <<<'HTML'
<p style="font-size: 16px; color: #374151;">Hi {{name}},</p>
<p style="font-size: 16px; color: #374151;">Thank you for submitting <strong>{{form_title}}</strong>. We have received your inquiry and a member of our team will get back to you shortly.</p>
<p style="font-size: 16px; color: #374151;">If you have any urgent questions, reply to this email or call us using the phone number on our website.</p>
<p style="font-size: 16px; color: #374151;">Best regards,<br>OmicsLogic Team</p>
HTML,
            ],
            [
                'name' => 'Web Form – Submission Summary',
                'subject' => 'Your {{form_title}} submission details',
                'content' => <<<'HTML'
<p style="font-size: 16px; color: #374151;">Hi {{name}},</p>
<p style="font-size: 16px; color: #374151;">Here is a copy of the information you submitted via <strong>{{form_title}}</strong>:</p>
<table style="width: 100%; max-width: 640px; border-collapse: collapse; margin: 16px 0;">
    <tbody>
        <tr>
            <td style="padding: 8px 12px; color: #546e7a; font-size: 14px; border: 1px solid #e5e7eb; width: 35%;">Name</td>
            <td style="padding: 8px 12px; font-size: 14px; border: 1px solid #e5e7eb;">{{name}}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; color: #546e7a; font-size: 14px; border: 1px solid #e5e7eb;">Email</td>
            <td style="padding: 8px 12px; font-size: 14px; border: 1px solid #e5e7eb;">{{email}}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; color: #546e7a; font-size: 14px; border: 1px solid #e5e7eb;">Organization</td>
            <td style="padding: 8px 12px; font-size: 14px; border: 1px solid #e5e7eb;">{{organization}}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; color: #546e7a; font-size: 14px; border: 1px solid #e5e7eb;">Phone</td>
            <td style="padding: 8px 12px; font-size: 14px; border: 1px solid #e5e7eb;">{{phone}}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; color: #546e7a; font-size: 14px; border: 1px solid #e5e7eb;">Country</td>
            <td style="padding: 8px 12px; font-size: 14px; border: 1px solid #e5e7eb;">{{country}}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; color: #546e7a; font-size: 14px; border: 1px solid #e5e7eb;">Program interest</td>
            <td style="padding: 8px 12px; font-size: 14px; border: 1px solid #e5e7eb;">{{program}}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; color: #546e7a; font-size: 14px; border: 1px solid #e5e7eb; vertical-align: top;">Inquiry details</td>
            <td style="padding: 8px 12px; font-size: 14px; border: 1px solid #e5e7eb;">{{inquiry_details}}</td>
        </tr>
    </tbody>
</table>
<p style="font-size: 14px; color: #6b7280;">We will review your submission and follow up at {{email}}.</p>
HTML,
            ],
            [
                'name' => 'Web Form – Program Inquiry Follow-up',
                'subject' => 'Next steps for your {{program}} inquiry',
                'content' => <<<'HTML'
<p style="font-size: 16px; color: #374151;">Dear {{name}},</p>
<p style="font-size: 16px; color: #374151;">Thank you for your interest in <strong>{{program}}</strong> through <strong>{{form_title}}</strong>.</p>
<p style="font-size: 16px; color: #374151;">Based on your profile ({{education}} from {{country}}), our admissions team will review your inquiry and share relevant program details with you at <strong>{{email}}</strong>.</p>
<p style="font-size: 16px; color: #374151;"><strong>Your message:</strong></p>
<blockquote style="margin: 12px 0; padding: 12px 16px; border-left: 4px solid #3b82f6; background: #f9fafb; color: #374151;">{{inquiry_details}}</blockquote>
<p style="font-size: 16px; color: #374151;">We typically respond within 1–2 business days. Please keep an eye on your inbox.</p>
<p style="font-size: 16px; color: #374151;">Warm regards,<br>Admissions Team</p>
HTML,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::query()->updateOrCreate(
                ['name' => $template['name']],
                [
                    'subject' => $template['subject'],
                    'content' => $template['content'],
                ],
            );
        }
    }
}
