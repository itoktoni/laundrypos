<?php

namespace App\Models;

class WebsiteSetting
{
    public static function field_name(): string
    {
        return 'name';
    }

    /** @deprecated JSON path, kept for one-time migration */
    public static function contentPath(): string
    {
        return base_path('content/website_settings/1.json');
    }

    /** Read raw settings (migrates JSON → .env once if exists) */
    public static function raw(): array
    {
        // One-time migration: if JSON exists, copy to .env then delete
        $jsonPath = static::contentPath();
        if (file_exists($jsonPath)) {
            $decoded = json_decode((string) file_get_contents($jsonPath), true);
            if (is_array($decoded) && ! empty($decoded)) {
                static::migrateJsonToEnv($decoded);
            }
            // Remove JSON after migration so we don't loop
            @unlink($jsonPath);
            @rmdir(dirname($jsonPath));
        }

        // Return current env-backed values as array (for merged compatibility)
        return [
            'name' => env('APP_NAME', config('website.name')),
            'tagline' => env('WEBSITE_TAGLINE', config('website.tagline')),
            'description' => env('WEBSITE_DESCRIPTION', config('website.description')),
            'alamat' => env('WEBSITE_ALAMAT', config('website.alamat')),
            'telepon' => env('WEBSITE_TELEPON', config('website.telepon')),
            'email' => env('WEBSITE_EMAIL', config('website.email')),
            'logo' => env('WEBSITE_LOGO', config('website.logo')),
            'favicon' => env('WEBSITE_FAVICON', config('website.favicon')),
            'colors' => ['primary' => env('WEBSITE_COLOR_PRIMARY', config('website.colors.primary'))],
            'footer_text' => env('WEBSITE_FOOTER_TEXT', config('website.footer_text')),
            'offline_enabled' => filter_var(env('WEBSITE_OFFLINE_ENABLED', config('website.offline_enabled', true)), FILTER_VALIDATE_BOOLEAN),
            'staff_target' => (int) env('STAFF_TARGET_MONTHLY', config('website.staff_target', 100)),
            'staff_fee' => (int) env('STAFF_FEE_PER_ORDER', config('website.staff_fee', 1000)),
            'store_latitude' => env('STORE_LATITUDE', config('website.store_latitude', '-6.2000000')),
            'store_longitude' => env('STORE_LONGITUDE', config('website.store_longitude', '106.8166660')),
            'store_radius' => (int) env('STORE_RADIUS_M', config('website.store_radius', 50)),
        ];
    }

    /** Persist to .env (replaces JSON) */
    public static function persist(array $data): void
    {
        $map = [];

        if (array_key_exists('name', $data)) {
            $map['APP_NAME'] = (string) $data['name'];
        }
        if (array_key_exists('tagline', $data)) {
            $map['WEBSITE_TAGLINE'] = (string) ($data['tagline'] ?? '');
        }
        if (array_key_exists('description', $data)) {
            $map['WEBSITE_DESCRIPTION'] = (string) ($data['description'] ?? '');
        }
        if (array_key_exists('alamat', $data)) {
            $map['WEBSITE_ALAMAT'] = (string) ($data['alamat'] ?? '');
        }
        if (array_key_exists('telepon', $data)) {
            $map['WEBSITE_TELEPON'] = (string) ($data['telepon'] ?? '');
        }
        if (array_key_exists('email', $data)) {
            $map['WEBSITE_EMAIL'] = (string) ($data['email'] ?? '');
        }
        if (array_key_exists('logo', $data)) {
            $map['WEBSITE_LOGO'] = (string) ($data['logo'] ?? '');
        }
        if (array_key_exists('favicon', $data)) {
            $map['WEBSITE_FAVICON'] = (string) ($data['favicon'] ?? '');
        }
        if (isset($data['colors']['primary'])) {
            $map['WEBSITE_COLOR_PRIMARY'] = (string) $data['colors']['primary'];
        }
        if (array_key_exists('footer_text', $data)) {
            $map['WEBSITE_FOOTER_TEXT'] = (string) ($data['footer_text'] ?? '');
        }
        if (array_key_exists('offline_enabled', $data)) {
            $map['WEBSITE_OFFLINE_ENABLED'] = $data['offline_enabled'] ? 'true' : 'false';
        }
        if (array_key_exists('staff_target', $data)) {
            $map['STAFF_TARGET_MONTHLY'] = (string) (int) $data['staff_target'];
        }
        if (array_key_exists('staff_fee', $data)) {
            $map['STAFF_FEE_PER_ORDER'] = (string) (int) $data['staff_fee'];
        }
        if (array_key_exists('store_latitude', $data)) {
            $map['STORE_LATITUDE'] = (string) $data['store_latitude'];
        }
        if (array_key_exists('store_longitude', $data)) {
            $map['STORE_LONGITUDE'] = (string) $data['store_longitude'];
        }
        if (array_key_exists('store_radius', $data)) {
            $map['STORE_RADIUS_M'] = (string) (int) $data['store_radius'];
        }

        if (! empty($map)) {
            static::putEnv($map);
        }

        // Also hot-update current request config so merged() reflects immediately
        if (isset($map['APP_NAME'])) {
            config(['website.name' => $map['APP_NAME']]);
            config(['app.name' => $map['APP_NAME']]);
        }
        if (isset($map['WEBSITE_TAGLINE'])) {
            config(['website.tagline' => $map['WEBSITE_TAGLINE']]);
        }
        if (isset($map['WEBSITE_DESCRIPTION'])) {
            config(['website.description' => $map['WEBSITE_DESCRIPTION']]);
        }
        if (isset($map['WEBSITE_ALAMAT'])) {
            config(['website.alamat' => $map['WEBSITE_ALAMAT']]);
        }
        if (isset($map['WEBSITE_TELEPON'])) {
            config(['website.telepon' => $map['WEBSITE_TELEPON']]);
        }
        if (isset($map['WEBSITE_EMAIL'])) {
            config(['website.email' => $map['WEBSITE_EMAIL']]);
        }
        if (isset($map['WEBSITE_LOGO'])) {
            config(['website.logo' => $map['WEBSITE_LOGO']]);
        }
        if (isset($map['WEBSITE_FAVICON'])) {
            config(['website.favicon' => $map['WEBSITE_FAVICON']]);
        }
        if (isset($map['WEBSITE_COLOR_PRIMARY'])) {
            config(['website.colors.primary' => $map['WEBSITE_COLOR_PRIMARY']]);
        }
        if (isset($map['WEBSITE_FOOTER_TEXT'])) {
            config(['website.footer_text' => $map['WEBSITE_FOOTER_TEXT']]);
        }
        if (isset($map['WEBSITE_OFFLINE_ENABLED'])) {
            config(['website.offline_enabled' => $map['WEBSITE_OFFLINE_ENABLED'] === 'true']);
        }
        if (isset($map['STAFF_TARGET_MONTHLY'])) {
            config(['website.staff_target' => (int) $map['STAFF_TARGET_MONTHLY']]);
        }
        if (isset($map['STAFF_FEE_PER_ORDER'])) {
            config(['website.staff_fee' => (int) $map['STAFF_FEE_PER_ORDER']]);
        }
        if (isset($map['STORE_LATITUDE'])) {
            config(['website.store_latitude' => $map['STORE_LATITUDE']]);
        }
        if (isset($map['STORE_LONGITUDE'])) {
            config(['website.store_longitude' => $map['STORE_LONGITUDE']]);
        }
        if (isset($map['STORE_RADIUS_M'])) {
            config(['website.store_radius' => (int) $map['STORE_RADIUS_M']]);
        }
    }

    private static function migrateJsonToEnv(array $decoded): void
    {
        $map = [];
        if (isset($decoded['name'])) {
            $map['APP_NAME'] = (string) $decoded['name'];
        }
        if (isset($decoded['tagline'])) {
            $map['WEBSITE_TAGLINE'] = (string) $decoded['tagline'];
        }
        if (isset($decoded['description'])) {
            $map['WEBSITE_DESCRIPTION'] = (string) $decoded['description'];
        }
        if (isset($decoded['alamat'])) {
            $map['WEBSITE_ALAMAT'] = (string) $decoded['alamat'];
        }
        if (isset($decoded['telepon'])) {
            $map['WEBSITE_TELEPON'] = (string) $decoded['telepon'];
        }
        if (isset($decoded['email'])) {
            $map['WEBSITE_EMAIL'] = (string) $decoded['email'];
        }
        if (isset($decoded['logo'])) {
            $map['WEBSITE_LOGO'] = (string) $decoded['logo'];
        }
        if (isset($decoded['favicon'])) {
            $map['WEBSITE_FAVICON'] = (string) $decoded['favicon'];
        }
        if (isset($decoded['colors']['primary'])) {
            $map['WEBSITE_COLOR_PRIMARY'] = (string) $decoded['colors']['primary'];
        }
        if (isset($decoded['footer_text'])) {
            $map['WEBSITE_FOOTER_TEXT'] = (string) $decoded['footer_text'];
        }
        if (isset($decoded['offline_enabled'])) {
            $map['WEBSITE_OFFLINE_ENABLED'] = $decoded['offline_enabled'] ? 'true' : 'false';
        }
        if (isset($decoded['staff_target'])) {
            $map['STAFF_TARGET_MONTHLY'] = (string) (int) $decoded['staff_target'];
        }
        if (isset($decoded['staff_fee'])) {
            $map['STAFF_FEE_PER_ORDER'] = (string) (int) $decoded['staff_fee'];
        }
        if (! empty($map)) {
            static::putEnv($map);
        }
    }

    private static function putEnv(array $map): void
    {
        $path = base_path('.env');
        if (! file_exists($path) || ! is_writable($path)) {
            return;
        }
        $content = file_get_contents($path);

        foreach ($map as $key => $value) {
            $quoted = static::envQuote((string) $value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
            $line = $key . '=' . $quoted;
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content = rtrim($content) . PHP_EOL . $line . PHP_EOL;
            }
        }

        file_put_contents($path, $content);
    }

    private static function envQuote(string $value): string
    {
        if ($value === '' || $value === 'true' || $value === 'false') {
            return $value;
        }
        // Needs quoting if contains space, #, ", ', or is numeric with leading zeros
        if (preg_match('/[\s#\'"]/', $value) || preg_match('/^".*"$/', $value)) {
            return '"' . addcslashes($value, '"') . '"';
        }

        return $value;
    }

    public static function merged(): array
    {
        // Config already reads from .env, so just return config
        return config('website');
    }

    public static function primaryColor(): string
    {
        $settings = static::merged();

        return $settings['colors']['primary'] ?? '#00288e';
    }

    public static function fileUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'public/')) {
            return '/storage/' . substr($path, 7);
        }

        if (str_starts_with($path, 'storage/')) {
            return '/storage/' . substr($path, 8);
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return '/' . ltrim($path, '/');
    }

    public static function generatePalette(string $hex): array
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g: $h = ($b - $r) / $d + 2;
                    break;
                case $b: $h = ($r - $g) / $d + 4;
                    break;
                default: $h = 0;
            }
            $h = round($h * 60);
        }

        $s = round($s * 100);
        $l = round($l * 100);

        return [
            'primary' => "hsl({$h}, {$s}%, {$l}%)",
            'on_primary' => $l > 50 ? "hsl({$h}, 10%, 10%)" : "hsl({$h}, 10%, 98%)",
            'primary_container' => "hsl({$h}, {$s}%, " . min($l + 25, 90) . '%)',
            'on_primary_container' => $l > 50 ? "hsl({$h}, 30%, 15%)" : "hsl({$h}, 20%, 90%)",
            'primary_fixed' => "hsl({$h}, {$s}%, " . min($l + 35, 95) . '%)',
            'primary_fixed_dim' => "hsl({$h}, {$s}%, " . min($l + 30, 90) . '%)',
        ];
    }
}
