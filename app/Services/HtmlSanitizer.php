<?php

namespace App\Services;

class HtmlSanitizer
{
    /**
     * Allowed HTML tags for rich text fields.
     */
    protected static array $allowedTags = [
        '<p>', '<br>', '<b>', '<strong>', '<i>', '<em>', '<u>', '<s>',
        '<ul>', '<ol>', '<li>', '<a>', '<pre>', '<code>', '<blockquote>',
        '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>', '<span>', '<div>',
        '<hr>'
    ];

    /**
     * Sanitize HTML content by stripping forbidden tags and dangerous attributes.
     */
    public static function clean(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // 1. Strip disallowed tags
        $allowedTagsString = implode('', static::$allowedTags);
        $clean = strip_tags($html, $allowedTagsString);

        // 2. Remove dangerous attributes like onerror, onclick, javascript: links
        $clean = preg_replace('/\s*on\w+="[^"]*"/i', '', $clean);
        $clean = preg_replace('/\s*on\w+=\'[^\']*\'/i', '', $clean);
        $clean = preg_replace('/\s*on\w+=\w+/i', '', $clean);
        $clean = preg_replace('/href=([\'"])\s*javascript:[^"\']*(\1)/i', 'href="#"', $clean);

        return trim($clean);
    }
}
