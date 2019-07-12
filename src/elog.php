<?php

/*
 * (c) 2019 Go Financial Technologies, JSC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace GoFinTech\Logging;


use Throwable;

class elog
{
    public static function msg($t, ...$args): void
    {
        self::output($t, $args);
    }

    public static function error($t, ...$args): void
    {
        self::output("ERROR $t", $args);
    }

    public static function debug($t, ...$args): void
    {
        self::output("DEBUG $t", $args);
    }

    public static function output($msg, $args): void
    {
        $buf = [$msg];
        foreach ($args as $arg) {
            $buf[] = self::formatData($arg);
        }
        echo implode(' ', $buf) . "\n";
    }

    public static function formatData($arg, int $recurse = 1): string
    {
        if (is_null($arg)) {
            return "NULL";
        }
        else if (is_bool($arg)) {
            return ($arg) ? 'true' : 'false';
        }
        else if (is_object($arg)) {
            if ($arg instanceof Throwable)
                return self::formatException($arg);
            $className = get_class($arg);
            if (!$recurse)
                return "{{$className}}";
            $buf = [];
            foreach (get_object_vars($arg) as $key => $value) {
                $buf[] = "\$$key=" . self::formatData($value, $recurse - 1);
            }
            if (empty($buf))
                return "{{$className}:#}";
            else
                return "{{$className}: " . implode(', ', $buf) . "}";
        }
        else if (is_array($arg)) {
            $count = count($arg);
            if ($count == 0)
                return "[]";
            if (!$recurse)
                return "Array($count)";
            $buf = [];
            $index = 0;
            $ordered = true;
            foreach ($arg as $key => $value) {
                if ($index > 0) {
                    $buf[] = ', ';
                }
                $buf[] = "$key => ";
                $buf[] = self::formatData($value, $recurse - 1);
                if ($key !== $index) {
                    $ordered = false;
                }
                $index++;
            }
            if ($ordered)
                return '[' . implode(array_filter($buf,
                        function ($i) {
                            return $i % 3 != 0;
                        }, ARRAY_FILTER_USE_KEY)) . ']';
            else
                return '[' . implode($buf) . ']';
        }
        else if (is_resource($arg)) {
            return "<$arg>";
        }
        else {
            return "$arg";
        }
    }

    public static function formatException(Throwable $ex): string
    {
        $className = get_class($ex);
        return "{$ex->getFile()}:{$ex->getLine()} $className: {$ex->getMessage()}";
    }
}
