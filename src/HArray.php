<?php

namespace LireinCore\Helpers;

class HArray
{
    use Traits\TStatic;

    /**
     * Сортирует массив по другому массиву
     * @param array $sourceArray
     * @param array $orderArray
     * @return array
     */
    public static function sortArrayByArray($sourceArray, $orderArray)
    {
        $resultArray = [];
        array_walk($orderArray, function ($orderIndex) use (&$sourceArray, &$resultArray) {
            $resultArray[] = $sourceArray[$orderIndex];
        });

        return $resultArray;
    }
}