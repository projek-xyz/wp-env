<?php

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $val = getenv($key);

        if (false === $val) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }

        if (is_string($val) && str_contains($val, '${')) {
            $val = preg_replace_callback('/\${([^}]+)}/', function ($m) {
                return env($m[1], $m[0]);
            }, $val);
        }

        return $val;
    }
}
