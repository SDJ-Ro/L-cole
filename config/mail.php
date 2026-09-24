<?php
/**
 * =========================================================================
 * L'ÉCOLE — MAIL CONFIGURATION & "BOOK COVER" ROUTER
 * =========================================================================
 * Displays authentic @lecole.edu addresses on the front-end, but routes
 * all live notifications, OTP codes, and alerts to lecoletesting@gmail.com.
 * =========================================================================
 */

return [
    // Real destination inbox for all school communications
    'live_testing_inbox' => 'projectdrawio986@gmail.com',

    // Front-facing book cover branding
    'from_email'         => 'noreply@lecole.edu',
    'from_name'          => "L'École School Platform",

    // SMTP Settings (for live delivery via Gmail SMTP)
    'smtp' => [
        'host'       => 'smtp.gmail.com',
        'port'       => 587,
        'encryption' => 'tls',
        'username'   => getenv('SMTP_USER') ?: 'lecoletesting@gmail.com',
        'password'   => getenv('SMTP_PASS') ?: 'oylhqrbeajtyjknf', // Google App Password (16 characters)
    ],

    // Log path for auditing all dispatched emails
    'mail_log_file'      => dirname(__DIR__) . '/storage/logs/mail.log'
];
