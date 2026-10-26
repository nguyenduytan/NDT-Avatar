<?php
declare(strict_types=1);
const NDT_VERSION = '1.0.0';
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Ndt\\')) {
        $file = __DIR__ . '/' . substr($class, 4) . '.php';
        if (is_file($file)) require $file;
    }
});
