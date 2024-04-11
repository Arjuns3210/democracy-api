<?php
/*
   *   created by : Arjun Singh
   *   Created On : 09-Jan-2024
   *   Uses :  To display message on admin panel
*/
return [
    'non_mandatory_token' => [
        'startup_api',
        'language.list',
        'policies.show',
        'user.logout',
        'faqs.list',
        'home.list',
        'notification_token.update',
        'contest.featured_list',
        'contest.live_list',
        'home.list',
        'contest.list',
        'contest.show'
    ],
    'startup_data' => [
    	"home" => [
    		"recently_played" => ":based_on_login",
    		"suggest_question" => ":based_on_login"
    	],
        "personal" => [
            'profile'           => ":based_on_login",
            "enrolled_contests" => ":based_on_login"
        ],
        "profile" => [
            "personal" => [
                "edit_profile" => ":based_on_login",
                "addresses" => ":based_on_login"
            ],
            "other" => [
                "delete_account" => ":based_on_login",
                "logout" => ":based_on_login",
                "login" => ":based_on_logout"
            ]
        ],
        "other" => [
            "about_us"             => true,
            "contact_us"           => true,
            "notification_setting" => ":based_on_login",
            "share_app"            => true,
            "faq"                  => true,
            "tnc"                  => true,
            "privacy_policy"       => true
        ],
        "action" => [
            "logout" => ":based_on_login",
            "login"  => ":based_on_logout",
        ],
        "extra_tabs" => [
            "notification_icon" => ":based_on_login"
        ]
    ],
    'login_with' => [
        "skip_login_android" => true,
        "skip_login_ios" => true,
        "otp" => true,
        "password" => false,
        "fb" => false,
        "google" => false,
        "apple" => false
    ],
    'sms_send' => false,
    'sms_template' => 'YOUR OTP IS {#var1#}',
    'sms_url' => env('SMS_URL', NULL),
    'sms_api_key' => env('SMS_API_KEY',NULL),
    'sms_template_id' => env('SMS_TEMPLATE_ID',NULL),
    'sms_username' => env('SMS_USERNAME',NULL),
    'sms_sender_id'=> env('SMS_SENDER_ID',NULL),
    'sms_route'=> env('SMS_ROUTE',NULL),
    'web_url'=> env('SMS_URL',NULL),
    'image_base_url' =>env('APP_URL',NULL),
    'media_base_url' =>env('IMAGE_BASE_URL',NULL),
    'home_data_limit'     => 5,
    'recently_played_limit'     => 3,
    "low_threshold"            => "2",
    "mid_threshold"            => "5",
    "hig_threshold"            => "10",
    "threshold"                => "100",
    "above_threshold"          => "1000",
    "contest_winner_rank_array" => [
        1,
        2,
        3,
        4
    ],
    'contest_details_more_contests_limit' => 5,
    'enrolled_contests_count' => 5,
    'cancelled_contest_message' => 'Contest cancelled due to insufficient participants.'
];

