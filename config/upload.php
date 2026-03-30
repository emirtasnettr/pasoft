<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CRM document uploads (customer / policy / claim / task)
    |--------------------------------------------------------------------------
    |
    | Extensions are validated with Laravel's File rule (content-aware).
    |
    */

    'document_extensions' => [
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'txt',
        'csv',
    ],

    'document_max_kilobytes' => 20480,

];
