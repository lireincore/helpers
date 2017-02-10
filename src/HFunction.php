<?php

namespace LireinCore\Helpers;

class HFunction
{
    use Traits\TStatic;

    /**
     * Вызывает функцию (метод) передавая в качестве аргументов ассоциативный массив
     *
     * @param string|array $function
     * @param array $params [arg => value]
     * @return mixed|null
     * @throws \RuntimeException
     */
    public static function call_user_func_array_assoc($function, $params)
    {
        if (is_array($function)) {
            $title = get_class($function[0]) . '::' . $function[1];

            if (!method_exists($function[0], $function[1])) {
                throw new \RuntimeException('Call to unexisting class method: '. $title);
            }

            $reflect = new \ReflectionMethod($function[0], $function[1]);
        } else {
            $title = $function;

            if (!function_exists($function)) {
                throw new \RuntimeException('Call to unexisting function: '. $title);
            }

            $reflect = new \ReflectionFunction($function);
        }

        $real_params = [];

        foreach ($reflect->getParameters() as $i => $param) {
            $pname = $param->getName();
            /*if ($param->isPassedByReference()) {
                /// @todo shall we raise some warning?
            }*/
            if (array_key_exists($pname, $params)) {
                $real_params[] = $params[$pname];
            } elseif ($param->isDefaultValueAvailable()) {
                $real_params[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException('Call to ' . $title . ' missing parameter nr. '. ($i+1) . ': ' . $pname);
            }
        }

        return call_user_func_array($function, $real_params);
    }

    /**
     * Создает объект передавая в конструктор ассоциативный массив
     *
     * @param string $class
     * @param array $params [arg => value]
     * @return object
     * @throws \RuntimeException
     */
    public static function create_class_array_assoc($class, $params = [])
    {
        if (!class_exists($class)) {
            throw new \RuntimeException('Call to unexisting class: '. $class);
        }

        $real_params = [];

        if (method_exists($class, '__construct')) {
            $refMethod = new \ReflectionMethod($class, '__construct');

            foreach ($refMethod->getParameters() as $i => $param) {
                $pname = $param->getName();
                /*if ($param->isPassedByReference()) {
                    /// @todo shall we raise some warning?
                }*/
                if (array_key_exists($pname, $params)) {
                    $real_params[] = $params[$pname];
                } elseif ($param->isDefaultValueAvailable()) {
                    $real_params[] = $param->getDefaultValue();
                } else {
                    $title = $class . '::__construct';
                    throw new \RuntimeException('Call to ' . $title . ' missing parameter nr. ' . ($i + 1) . ': ' . $pname);
                }
            }
        }

        $refClass = new \ReflectionClass($class);

        return $refClass->newInstanceArgs($real_params);
    }
}