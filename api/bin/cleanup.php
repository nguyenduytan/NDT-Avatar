<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') exit(1);
$dir=getenv('CACHE_DIR');
if(!$dir || !is_dir($dir)) { fwrite(STDERR,"Set CACHE_DIR to an existing cache directory\n"); exit(1); }
$ttl=max(60,(int)(getenv('CACHE_TTL') ?: 86400));
$removed=0;
foreach(new DirectoryIterator($dir) as $file) {
    if($file->isFile() && !$file->isLink() && preg_match('/^(?:[a-f0-9]{64}\.bin|ndt-.+)$/D',$file->getFilename()) && $file->getMTime()<time()-$ttl) { if(unlink($file->getPathname())) $removed++; }
}
echo "Removed $removed expired cache files\n";
