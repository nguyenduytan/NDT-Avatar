<?php
declare(strict_types=1);
namespace Ndt;
use InvalidArgumentException;
use Normalizer;

final readonly class Options
{
    public function __construct(
        public string $initials, public int $width, public int $height,
        public string $background, public string $color, public string $format,
        public float $fontSize, public float $radius, public bool $bold,
        public string $style, public string $seed, public string $gradient,
    ) {}

    public static function from(array $q): self
    {
        foreach ($q as $key => $value) {
            if (!is_scalar($value) || strlen((string)$value) > 2048) throw new InvalidArgumentException("Invalid parameter: $key");
        }
        $name = (string)($q['name'] ?? 'John Doe');
        if (!mb_check_encoding($name, 'UTF-8') || mb_strlen($name) > 128) throw new InvalidArgumentException('name must be valid UTF-8, at most 128 characters');
        $name = Normalizer::normalize($name) ?: '';
        $name = trim(preg_replace('/[\p{C}\s]+/u', ' ', $name) ?? '');
        $length = self::integer($q, 'length', 2, 1, 8);
        $words = preg_split('/[\s\-]+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        if (!$words) $initials = '?';
        elseif (count($words) === 1) $initials = grapheme_substr($words[0], 0, $length) ?: '?';
        else {
            $selected = $length === 2 ? [$words[0], $words[count($words)-1]] : array_slice($words, 0, $length);
            $initials = implode('', array_map(static fn($word) => grapheme_substr($word, 0, 1), $selected));
        }
        if (self::boolean($q, 'uppercase', true)) $initials = mb_strtoupper($initials);
        $size = self::integer($q, 'size', 64, 16, 2048);
        $width = self::integer($q, 'width', $size, 16, 2048);
        $height = self::integer($q, 'height', $size, 16, 2048);
        $seed = (string)($q['seed'] ?? $name);
        $palette = ['3459bc','ac334f','176f68','7440a8','a55123','336477','365b39','a83e55'];
        $bg = (string)($q['background'] ?? 'ddd');
        if ($bg === 'random') $bg = $palette[hexdec(substr(hash('sha256',$seed),0,4)) % count($palette)];
        $bg = self::color($bg, true);
        $color = (string)($q['color'] ?? '222');
        if ($color === 'auto') {
            $hex = $bg === 'transparent' ? 'ffffff' : $bg;
            $channels = array_map(static function($v) { $v=hexdec($v)/255; return $v <= .04045 ? $v/12.92 : (($v+.055)/1.055)**2.4; }, str_split($hex,2));
            $l = .2126*$channels[0]+.7152*$channels[1]+.0722*$channels[2];
            $color = ($l+.05)/.05 >= 1.05/($l+.05) ? '000000' : 'ffffff';
        }
        $format = strtolower((string)($q['format'] ?? 'png'));
        if (!in_array($format,['png','svg','webp','json'],true)) throw new InvalidArgumentException('format must be png, svg, webp or json');
        $style = (string)($q['style'] ?? 'initials');
        if (!in_array($style,['initials','identicon','rings'],true)) throw new InvalidArgumentException('style must be initials, identicon or rings');
        $font = self::number($q, 'font-size', .4375, .1, 1);
        $radius = self::boolean($q, 'rounded', false) ? 50 : self::number($q, 'radius', 0, 0, 50);
        return new self($initials,$width,$height,$bg,self::color($color),$format,$font,$radius,self::boolean($q,'bold',false),$style,$seed,isset($q['gradient']) && $q['gradient']!=='' ? self::color((string)$q['gradient']) : '');
    }

    private static function integer(array $q,string $key,int $default,int $min,int $max): int
    {
        $s=(string)($q[$key] ?? $default);
        if (!preg_match('/^\d+$/D',$s) || (int)$s<$min || (int)$s>$max) throw new InvalidArgumentException("$key must be an integer between $min and $max");
        return (int)$s;
    }
    private static function number(array $q,string $key,float $default,float $min,float $max): float
    {
        $s=$q[$key] ?? $default;
        if (!is_numeric($s) || !is_finite((float)$s) || (float)$s<$min || (float)$s>$max) throw new InvalidArgumentException("$key must be between $min and $max");
        return (float)$s;
    }
    private static function boolean(array $q,string $key,bool $default): bool
    {
        if (!isset($q[$key])) return $default;
        $value=filter_var($q[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($value===null) throw new InvalidArgumentException("$key must be a boolean");
        return $value;
    }
    private static function color(string $value,bool $transparent=false): string
    {
        if ($transparent && $value==='transparent') return $value;
        $value=strtolower(ltrim($value,'#'));
        if (!preg_match('/^(?:[a-f0-9]{3}|[a-f0-9]{6})$/D',$value)) throw new InvalidArgumentException('Colors must contain 3 or 6 hexadecimal digits');
        return strlen($value)===3 ? implode('',array_map(static fn($v)=>$v.$v,str_split($value))) : $value;
    }
    public function key(): string { return hash('sha256',NDT_VERSION.json_encode($this,JSON_THROW_ON_ERROR)); }
}
