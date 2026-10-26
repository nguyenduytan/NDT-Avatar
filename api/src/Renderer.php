<?php
declare(strict_types=1);
namespace Ndt;
use RuntimeException;

final class Renderer
{
    public static function font(bool $bold): string
    {
        $directory = getenv('FONT_DIR') ?: __DIR__.'/../fonts';
        $file=$directory.'/NotoSans-'.($bold ? 'Bold' : 'Regular').'.ttf';
        if (!is_file($file)) throw new RuntimeException('Required font is unavailable');
        return $file;
    }
    public static function render(Options $o): string
    {
        if ($o->format==='json') return json_encode(['version'=>NDT_VERSION,'initials'=>$o->initials,'width'=>$o->width,'height'=>$o->height,'background'=>$o->background,'color'=>$o->color,'style'=>$o->style,'svg'=>self::svg($o)],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
        if ($o->format==='svg') return self::svg($o);
        return self::raster($o);
    }
    private static function shapes(Options $o): array
    {
        $hash=hash('sha256',$o->seed);
        $shapes=[];
        if ($o->style==='identicon') {
            for($y=0;$y<5;$y++) for($x=0;$x<3;$x++) {
                if(hexdec($hash[$y*3+$x])%2===0) continue;
                foreach(array_unique([$x,4-$x]) as $col) $shapes[]=['rect',($col+1)/7,($y+1)/7,1/7,1/7];
            }
        } elseif ($o->style==='rings') {
            for($i=0;$i<4;$i++) $shapes[]=['ellipse',.2+hexdec($hash[$i*2])/25,.2+hexdec($hash[$i*2+1])/25,.14+$i*.08,.14+$i*.08];
        }
        return $shapes;
    }
    private static function svg(Options $o): string
    {
        $w=$o->width; $h=$o->height; $r=min($w,$h)*$o->radius/100;
        $bg=$o->background==='transparent' ? 'none' : '#'.$o->background;
        $defs='<clipPath id="clip"><rect width="'.$w.'" height="'.$h.'" rx="'.$r.'"/></clipPath>';
        if($o->gradient!=='') { $defs.='<linearGradient id="gradient" x2="0" y2="1"><stop stop-color="'.$bg.'"/><stop offset="1" stop-color="#'.$o->gradient.'"/></linearGradient>'; $bg='url(#gradient)'; }
        $body='<rect width="'.$w.'" height="'.$h.'" fill="'.$bg.'"/>';
        if($o->style==='initials') {
            $text=htmlspecialchars($o->initials, ENT_QUOTES|ENT_XML1, 'UTF-8');
            $size=min($w,$h)*$o->fontSize;
            $body.='<text x="50%" y="50%" dy=".35em" text-anchor="middle" font-family="Noto Sans,Arial,sans-serif" font-size="'.$size.'" font-weight="'.($o->bold ? 700 : 400).'" fill="#'.$o->color.'">'.$text.'</text>';
        } else foreach(self::shapes($o) as [$type,$x,$y,$sw,$sh]) {
            $body.=$type==='rect' ? '<rect x="'.($x*$w).'" y="'.($y*$h).'" width="'.($sw*$w).'" height="'.($sh*$h).'" fill="#'.$o->color.'"/>' : '<ellipse cx="'.($x*$w).'" cy="'.($y*$h).'" rx="'.($sw*$w/2).'" ry="'.($sh*$h/2).'" fill="none" stroke="#'.$o->color.'" stroke-width="'.(min($w,$h)*.025).'"/>';
        }
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'" role="img"><title>Avatar</title><defs>'.$defs.'</defs><g clip-path="url(#clip)">'.$body.'</g></svg>';
    }
    private static function rgb(string $hex): array { return array_map('hexdec',str_split($hex,2)); }
    private static function raster(Options $o): string
    {
        if(!extension_loaded('gd')) throw new RuntimeException('GD extension is required');
        // Supersample small avatars; cap work at a 2048px edge.
        $scale=max($o->width,$o->height)<=512 ? 2 : 1;
        $w=$o->width*$scale; $h=$o->height*$scale;
        $im=imagecreatetruecolor($w,$h);
        imagealphablending($im,false); imagesavealpha($im,true);
        $transparent=imagecolorallocatealpha($im,0,0,0,127);
        imagefill($im,0,0,$transparent);
        $a=self::rgb($o->background==='transparent' ? '000000' : $o->background);
        $b=self::rgb($o->gradient ?: ($o->background==='transparent' ? '000000' : $o->background));
        for($y=0;$y<$h;$y++) {
            $t=$y/max(1,$h-1);
            $rgb=[]; for($c=0;$c<3;$c++) $rgb[]=(int)round($a[$c]+($b[$c]-$a[$c])*$t);
            $alpha=$o->background==='transparent' ? (int)round(127*(1-($o->gradient ? $t : 0))) : 0;
            imageline($im,0,$y,$w-1,$y,imagecolorallocatealpha($im,...[...$rgb,$alpha]));
        }
        imagealphablending($im,true);
        $fg=imagecolorallocate($im,...self::rgb($o->color));
        if($o->style==='initials') {
            $font=self::font($o->bold);
            $size=min($w,$h)*$o->fontSize*.75;
            $box=imagettfbbox($size,0,$font,$o->initials);
            $textWidth=max($box[0],$box[2],$box[4],$box[6])-min($box[0],$box[2],$box[4],$box[6]);
            if($textWidth>$w*.86) { $size*=$w*.86/$textWidth; $box=imagettfbbox($size,0,$font,$o->initials); }
            $left=min($box[0],$box[2],$box[4],$box[6]); $right=max($box[0],$box[2],$box[4],$box[6]);
            $top=min($box[1],$box[3],$box[5],$box[7]); $bottom=max($box[1],$box[3],$box[5],$box[7]);
            imagettftext($im,$size,0,(int)(($w-($right-$left))/2-$left),(int)(($h-($bottom-$top))/2-$top),$fg,$font,$o->initials);
        } else foreach(self::shapes($o) as [$type,$x,$y,$sw,$sh]) {
            if($type==='rect') imagefilledrectangle($im,(int)round($x*$w),(int)round($y*$h),(int)round(($x+$sw)*$w)-1,(int)round(($y+$sh)*$h)-1,$fg);
            else { imagesetthickness($im,max(1,(int)(min($w,$h)*.025))); imageellipse($im,(int)($x*$w),(int)($y*$h),(int)($sw*$w),(int)($sh*$h),$fg); }
        }
        imagealphablending($im,false);
        $r=min($w,$h)*$o->radius/100;
        if($r>0) for($y=0;$y<(int)ceil($r);$y++) for($x=0;$x<(int)ceil($r);$x++) {
            if(($x+.5-$r)**2+($y+.5-$r)**2>$r**2) foreach([[$x,$y],[$w-1-$x,$y],[$x,$h-1-$y],[$w-1-$x,$h-1-$y]] as [$px,$py]) imagesetpixel($im,$px,$py,$transparent);
        }
        if($scale>1) {
            $small=imagecreatetruecolor($o->width,$o->height); imagealphablending($small,false); imagesavealpha($small,true);
            imagecopyresampled($small,$im,0,0,0,0,$o->width,$o->height,$w,$h); $im=$small;
        }
        ob_start();
        try { if($o->format==='webp') imagewebp($im,null,90); else imagepng($im,null,6); return (string)ob_get_contents(); }
        finally { ob_end_clean(); }
    }
}
