<?php
declare(strict_types=1);
namespace Ndt;
use InvalidArgumentException;
use Throwable;

final class App
{
    public static function run(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');
        header('X-NDT-Version: '.NDT_VERSION);
        header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; sandbox");
        header('Access-Control-Allow-Origin: '.(getenv('CORS_ORIGIN') ?: '*'));
        header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, If-None-Match');
        header('Access-Control-Expose-Headers: ETag, X-NDT-Cache, X-NDT-Version');
        header('Cache-Control: no-store');
        $method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path=rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
        try {
            if(!in_array($path,['','/api','/api/index.php','/api/v1/avatar','/api/v1/meta','/health'],true)) { self::error(404,'not_found','Endpoint not found'); return; }
            if($method==='OPTIONS') { header('Access-Control-Max-Age: 86400'); http_response_code(204); return; }
            if(!in_array($method,['GET','HEAD'],true)) { header('Allow: GET, HEAD, OPTIONS'); self::error(405,'method_not_allowed','Use GET or HEAD'); return; }
            if($path==='/health') {
                $ready=extension_loaded('gd') && extension_loaded('intl') && extension_loaded('mbstring');
                try { Renderer::font(false); Renderer::font(true); } catch(Throwable) { $ready=false; }
                http_response_code($ready ? 200 : 503);
                self::json(['status'=>$ready?'ok':'degraded','version'=>NDT_VERSION]); return;
            }
            if($path==='/api/v1/meta') { self::json(['version'=>NDT_VERSION,'formats'=>['svg','png','webp','json'],'styles'=>['initials','identicon','rings'],'limits'=>['size'=>[16,2048],'length'=>[1,8],'name'=>128],'defaults'=>['name'=>'John Doe','size'=>64,'format'=>'png','background'=>'dddddd','color'=>'222222','length'=>2,'font-size'=>.4375,'uppercase'=>true]]); return; }
            $query=$_GET;
            if(!isset($query['format'])) { $query['format']=str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'image/svg+xml') ? 'svg' : 'png'; header('Vary: Accept'); }
            $o=Options::from($query);
            $bypass=array_key_exists('no-cache',$query);
            [$body,$cache]=Cache::remember($o->key(),static fn()=>Renderer::render($o),$bypass);
            $etag='"'.hash('sha256',$body).'"';
            header('ETag: '.$etag);
            header('X-NDT-Cache: '.$cache);
            header('Cache-Control: '.($bypass ? 'no-store' : 'public, max-age=86400, stale-while-revalidate=3600'));
            header('Content-Type: '.match($o->format){'svg'=>'image/svg+xml; charset=utf-8','json'=>'application/json; charset=utf-8','webp'=>'image/webp',default=>'image/png'});
            $tags=explode(',',$_SERVER['HTTP_IF_NONE_MATCH'] ?? '');
            foreach($tags as $tag) if(!$bypass && (trim($tag)==='*' || preg_replace('/^W\//','',trim($tag))===$etag)) { http_response_code(304); return; }
            if(isset($query['download']) && $query['download']==='1') header('Content-Disposition: attachment; filename="ndt-avatar.'.$o->format.'"');
            header('Content-Length: '.strlen($body));
            if($method!=='HEAD') echo $body;
        } catch(InvalidArgumentException $e) { self::error(400,'invalid_parameter',$e->getMessage()); }
        catch(Throwable $e) { error_log('NDT Avatar: '.$e->getMessage()); self::error(500,'render_failed','Unable to generate avatar'); }
    }
    private static function json(array $data): void { header('Content-Type: application/json; charset=utf-8'); if(($_SERVER['REQUEST_METHOD'] ?? 'GET')!=='HEAD') echo json_encode($data,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE); }
    private static function error(int $status,string $code,string $message): void { http_response_code($status); header('Cache-Control: no-store'); self::json(['error'=>['code'=>$code,'message'=>$message,'details'=>(object)[]]]); }
}
