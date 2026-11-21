<?php

namespace App\Helpers;

class ArrayHelper
{
    /**
     * Lọc và xoá phần tử trong mảng theo điều kiện callback.
     * Trả về mảng đã lọc, mảng gốc bị xoá phần đã match.
     */
    public static function pull(array &$array, callable $callback): array
    {
        $matched = [];

        foreach ($array as $key => $item) {
            if ($callback($item, $key)) {
                $matched[] = $item;
                unset($array[$key]);
            }
        }

        return $matched;
    }
}
