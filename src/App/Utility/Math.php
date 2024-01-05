<?php declare(strict_types=1);

namespace App\Utility;

class Math
{
    private const SCALE = 8;

    public static function deltaPercent(string $val1, string $val2, int $percentScale = self::SCALE): string
    {
        [$min, $max] = self::minMax($val1, $val2);

        $deltaAbs = self::deltaAbs($val1, $val2);
        $upper = bcmul($deltaAbs, '100', self::SCALE);

        return bcdiv($upper, $min, $percentScale);
    }

    public static function deltaAbs(string $val1, string $val2): string
    {
        [$min, $max] = self::minMax($val1, $val2);

        return bcsub($max, $min, self::SCALE);
    }

    public static function minMax(string $val1, string $val2): array
    {
        return bccomp($val1, $val2, self::SCALE) === -1 ? [$val1, $val2] : [$val2, $val1];
    }

    public static function sub(string $val1, string $val2): string
    {
        return bcsub($val1, $val2, self::SCALE);
    }

    public static function sum(string $val1, string $val2): string
    {
        return bcadd($val1, $val2, self::SCALE);
    }

    public static function mul(string $val1, string $val2): string
    {
        return bcmul($val1, $val2, self::SCALE);
    }

    public static function div(string $val1, string $val2): string
    {
        return bcdiv($val1, $val2, self::SCALE);
    }

    public static function getPercentPart(string $val, float $percent): string
    {
        return bcdiv(bcmul($val, (string)$percent, self::SCALE), '100', self::SCALE);
    }

    public static function addPercent(string $val, float $percent): string
    {
        return self::sum($val, self::getPercentPart($val, $percent));
    }

    public static function subPercent(string $val, float $percent): string
    {
        return self::sub($val, self::getPercentPart($val, $percent));
    }

    public static function compare(string $val1, $val2): int
    {
        return bccomp($val1, $val2, self::SCALE);
    }
}
