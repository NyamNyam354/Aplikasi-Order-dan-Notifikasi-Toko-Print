<?php

namespace App\Constants;

class OrderConstant
{
    public const MAX_FILE_SIZE = 102400; // 100MB in KB
    public const MAX_NOTES_LENGTH = 1000;
    public const MIN_CANCEL_REASON_LENGTH = 10;
    public const ORDER_PER_PAGE = 15;
    public const NOTIFICATION_PER_PAGE = 15;
    public const RECENT_ORDERS_LIMIT = 5;
    public const SIGNED_URL_EXPIRATION = 60;
    public const RATE_LIMIT_MAX_ATTEMPTS = 5;
    public const RATE_LIMIT_DECAY_MINUTES = 1;

    public const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'ppt', 'pptx',
        'xls', 'xlsx', 'jpg', 'jpeg', 'png',
    ];

    public const ALLOWED_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
    ];
}
