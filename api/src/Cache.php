<?php
declare(strict_types=1);
namespace Ndt;

final class Cache
{
    public static function remember(string $key, callable $generate, bool $bypass=false): array
    {
        $dir=getenv('CACHE_DIR') ?: '';
        if($dir==='' || $bypass) return [$generate(),'BYPASS'];
        $path=$dir.'/'.$key.'.bin';
        $ttl=max(60,(int)(getenv('CACHE_TTL') ?: 86400));
        if(is_file($path) && filemtime($path)>time()-$ttl) {
            $data=file_get_contents($path);
            if($data!==false) return [$data,'HIT'];
        }
        $data=$generate();
        if(is_dir($dir) && is_writable($dir)) {
            $tmp=tempnam($dir,'ndt-');
            if($tmp!==false) { if(file_put_contents($tmp,$data,LOCK_EX)!==false) @rename($tmp,$path); if(is_file($tmp)) unlink($tmp); }
        }
        return [$data,'MISS'];
    }
}
