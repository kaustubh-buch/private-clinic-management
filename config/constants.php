<?php
return [
    'MAX_LENGTH' => [
        'FIRST_NAME' => 50,
        'LAST_NAME' => 50,
        'EMAIL' => 255,
        'PASSWORD' => 50,
        'OTP' => 6,
    ],
    'GLOBAL' => [
        'PATTERNS' => [
            'PASSWORD_RULES' => [
                'COMBINED' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^(){}[\].]).{8,}$/',
            ],
        ],
        'VERIFICATION_TIMER' => 20,
        'RESEND_EMAIL_COOLDOWN' => 40,
        'OTP' => [
            'OTP_EXPIRATION_MINUTES' => 10,
        ],
    ]
];
