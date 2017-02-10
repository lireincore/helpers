<?php

namespace LireinCore\Helpers;

class HDir
{
    use Traits\TStatic;

    /**
     * Создает каталоги рекурсивно и пытается назначить им указанные права, хозяина и группу
     * @param string $pathname
     * @param int $mode
     * @param mixed $user
     * @param mixed $group
     * @return bool
     */
    public static function rmk($pathname, $mode = 0775, $user = null, $group = null)
    {
        $dirs = array_filter(explode(DIRECTORY_SEPARATOR, $pathname));
        $path = '';

        foreach ($dirs as $dir) {
            $path .= DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($path)) {
                if (!@mkdir($path, $mode)) {
                    return false;
                }
                else {
                    @chmod($path, $mode);
                    if (!is_null($user)) @chown($path, $user);
                    if (!is_null($group)) @chgrp($path, $group);
                }
            }
        }

        return true;
    }

    /**
     * Удаляет каталог и все его содержимое
     * @param string $pathname
     * @return bool
     */
    public static function rrm($pathname)
    {
        if (($dir = @opendir($pathname)) === false) return false;

        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                $full = $pathname . DIRECTORY_SEPARATOR . $file;
                if (is_dir($full)) {
                    static::rrmdir($full);
                } else {
                    unlink($full);
                }
            }
        }

        closedir($dir);

        return @rmdir($pathname);
    }
}