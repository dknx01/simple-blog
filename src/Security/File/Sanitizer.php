<?php

namespace App\Security\File;

class Sanitizer
{
    private const ALLOWED_PATHS = [
        '/Anderes',
        '/Dokumente',
        '/Stammtische',
        '/Wiki',
    ];
    public static function securePath(string $path): string
    {
        $path = self::removeDotsAndTilde($path);
        $path = self::ensureAllowedDirectories($path);
        return $path;
    }

    /**
     * @param string $path
     * @return string
     */
    public static function removeDotsAndTilde(string $path): string
    {
        if (preg_match('@^(\.+|~)@', $path, $matches)) {
            $path = substr($path, strlen($matches[1]));
        }
        return $path;
    }

    /**
     * @param string $path
     * @return string
     */
    private static function ensureAllowedDirectories(string $path): string
    {
        if (!preg_match('@^/.*@', $path)) {
            return  $path;
        }
        $pattern = '@^(.*)(?:';
        $pattern .= implode('|', self::ALLOWED_PATHS);
        $pattern .= ')/.*$@';
        if (!preg_match($pattern, $path, $matches)) {
            return '';
        }

        if ($matches[1] !== '') {
            $path = substr($path, strlen($matches[1]));
        }

        return $path;
    }
}