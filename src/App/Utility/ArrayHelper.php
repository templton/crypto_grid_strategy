<?php declare(strict_types=1);

namespace App\Utility;

class ArrayHelper
{
    public static function sortKeys(array $data): array
    {
        $result = [];

        $keys = array_keys($data);

        sort($keys);

        foreach ($keys as $key) {
            $result[$key] = $data[$key];
        }

        return $result;
    }
}
