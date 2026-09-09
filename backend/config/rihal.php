<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notification channels
    |--------------------------------------------------------------------------
    |
    | The notification subsystem records an in-app notification for every
    | guardian and (optionally) dispatches an external message. External
    | dispatch is a seam: set a driver below to actually send via that
    | gateway. When left null, dispatch is logged only (no external call),
    | which is the safe default for development.
    |
    | Supported drivers:
    |   null   - log only (default, no external send)
    |   smtp   - Laravel Mail (configure MAIL_* in .env)
    |   twilio - Twilio SMS (requires TWILIO_* credentials)
    |   bd_sms - a Bangladesh SMS gateway (configure BD_SMS_* credentials)
    |
    */

    'notifications' => [
        'sms_driver' => env('NOTIFICATION_SMS_DRIVER', 'null'),
        'email_driver' => env('NOTIFICATION_EMAIL_DRIVER', 'null'),

        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],

        'bd_sms' => [
            'api_url' => env('BD_SMS_API_URL'),
            'api_key' => env('BD_SMS_API_KEY'),
            'sender_id' => env('BD_SMS_SENDER_ID'),
        ],

        // Throttle: max sends per minute per user for notify endpoints.
        'send_rate_limit' => env('NOTIFICATION_SEND_RATE_LIMIT', 20),
    ],

];
