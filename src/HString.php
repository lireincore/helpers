<?php

namespace LireinCore\Helpers;

class HString
{
    use Traits\TStatic;

    /**
     * Делает заглавной первую букву
     * @param string $str
     * @return string
     */
    public static function mb_ucfirst($str)
    {
        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }

    /**
     * Подсчитывает кол-во заглавных букв в тексте
     * @param $str string
     * @return int
     */
    public static function capitalsCount($str)
    {
        return mb_strlen(preg_replace('![^A-Z|А-Я|Ё]+!u', '', $str));
    }

    /**
     * Вычисляет процентное соотношение содержащихся в тексте заглавных букв
     * @param $str string
     * @return float
     */
    public static function capitalsRatio($str)
    {
        if (!empty($str)) {
            return static::capitalsCount($str) * 100 / mb_strlen($str);
        }
        else return 0;
    }

    /**
     * Транслитерация
     * @param $string
     * @return string
     */
    public static function rus2trans($string)
    {
        $converter = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K',
            'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
        ];

        return strtr($string, $converter);
    }

    /**
     * Генерирует случайную строку заданной длины
     * Пример вызова: random_strint_generate( 10, $strtype=array('rus', 'capsrus', 'capseng', 'num'), $ext=array('&', '%') )
     * @param $number - количество символов, которые нужно сгенерировать
     * @param array $strtype - тип генерируемой строки
     * @param array $ext - массив дополнительных символов. Если не нужно включать целиком массив, или указать символы, не вошедшие в массив
     * @return string
     */
    public static function randomString($number, $strtype = [], $ext = [])
    {
        $eng = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'v', 'x', 'y', 'z'];
        $capseng = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z'];
        $rus = ['а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'];
        $capsrus = ['А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Ц', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'];
        $num = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $char = ['_', '-', '.', '+', '/', '@', ',', ':', ';', '!', '?', '(', ')'];

        $arr = [];

        foreach ($strtype as $val) {
            $arr = array_merge($arr, $$val);
        }

        $arr = array_merge($arr, $ext);
        $str = '';

        for ($i = 0; $i < $number; $i++) {
            $index = rand(0, (count($arr) - 1));
            $str .= $arr[$index];
        }

        return $str;
    }
}