<?php

return [
    'key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
];
