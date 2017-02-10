<?php

namespace LireinCore\Helpers;

class HDebug
{
    use Traits\TStatic;

    /**
     * The function writes the $var to the log file.
     * @param mixed $var to be dumped
     * @param boolean $clear очистить файл перед записью
     * @param array $options
     */
    public static function log($var = '', $clear = false, $options = null)
    {
        static $sessions = [];
        
        $default_options = [
            'path' => __DIR__,
            'filename' => 'info.log', //имя файла
            'bt_count' => 5, //кол-во путей для backtrace
            'bt_start' => 0, //начальный путь для backtrace
            'nl' => "\n\r",
            'label' => '', //метка
        ];
        $options = (array)$options + $default_options;

        $nl = $options['nl'];

        if ($options['label']) {
            $label = date('Y/m/d H:i:s', time()) . ': [' . $options['label'] . ']';
        } else {
            $label = date('Y/m/d H:i:s', time()) . ': ';
        }

        if (is_string($var)) {
            $log = $label . $nl . "String '{$var}'";
        } elseif (is_int($var)) {
            $log = $label . $nl . "Integer {$var}";
        } elseif (is_float($var)) {
            $log = $label . $nl . "Float {$var}";
        } elseif (is_object($var) || is_array($var)) {
            $log = print_r($var, true);
            $log = $label . $nl . $log;
        } elseif ($var === true) {
            $log = $label . $nl . 'Boolean true';
        } elseif ($var === false) {
            $log = $label . $nl . 'Boolean false';
        } elseif (is_null($var)) {
            $log = $label . $nl . 'null';
        } elseif (is_resource($var)) {
            $log = $label . $nl . "Resource";
        }
        else {
            $log = $label . $nl . $var;
        }

        if ($options['bt_count']) {
            $lines = [];
            $bt = debug_backtrace();
            $lenght = count($bt);
            $start = min($lenght, $options['bt_start']);
            $end = min($lenght, $options['bt_start'] + $options['bt_count']);
            for ($i = $start; $i < $end; $i++) {
                $item = $bt[$i];
                if (!empty($item)) {
                    $func_str = !empty($item['function']) ? $item['function'] : 'UNDEFINED_FUNCTION';
                    $file_str = !empty($item['file']) ? $item['file'] : 'UNDEFINED_FILE';
                    $line_str = !empty($item['line']) ? $item['line'] : 'UNDEFINED_LINE';
                    $lines[] = "{$func_str}() in {$file_str} at line {$line_str}";
                } else break;
            }
            $log .= $nl . join($nl, $lines);
        }

        $log_file = $options['path'] . $options['filename'];

        $sessions[$options['filename']] = isset($sessions[$options['filename']]) ? true : false;
        if ($clear || (!$sessions[$options['filename']] && isset($options['maxsize']) && filesize($log_file) > $options['maxsize']))
            $ff = fopen($log_file, 'w');
        else
            $ff = fopen($log_file, 'a');

        $log .= $nl . '*********************************************************************************************' . $nl;
        fputs($ff, $log);
        fclose($ff);
    }
}