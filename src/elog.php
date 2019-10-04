<?php

/*
 * (c) 2019 Go Financial Technologies, JSC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace GoFinTech\Logging;


use GoFinTech\Logging\Constructs\LogEngineInterface;
use GoFinTech\Logging\Constructs\LogScope;
use GoFinTech\Logging\Engine\TrivialEngine;
use Throwable;

class elog
{
    /** @var string Message parameter that overrides message code */
    public const P_CODE = '__code';
    /** @var string  Message parameter that stores exception */
    public const P_EXCEPTION = '__ex';
    /** @var string Message parameter that extends context */
    public const P_CONTEXT = '__ctx';
    /** @var string Message parameter for surplus call parameters */
    public const P_PAYLOAD = '__payload';

    /** @var int Message level hints */
    public const L_INFO = 0;
    /** @var int Hint for audit trail records */
    public const L_AUDIT = 0b111;
    /** @var int Hint for self-diagnostic check failures */
    public const L_WARN  = 0b001;
    /** @var int Hint for business errors and expected exceptions */
    public const L_SMOKE = 0b010;
    /** @var int Hint for technical errors and unexpected exceptions */
    public const L_CRASH = 0b011;
    /** @var int Hint for program flow tracing */
    public const L_TRACE = 0b100;
    /** @var int Hint for additional debug data */
    public const L_DEBUG = 0b101;

    public const C_FILE = 'file';
    public const C_LINE = 'line';
    public const C_CLASS = 'class';
    public const C_METHOD = 'method';
    public const C_PACKAGE = 'package';
    public const C_APP = 'app';
    public const C_INSTANCE = 'instance';

    /** @var LogEngineInterface */
    private static $engine;

    public static function msg(...$s): void
    {
        (self::$engine ?? self::$engine = self::defaultEngine())->emit($s, self::L_INFO, 1);
    }

    public static function error(...$s): void
    {
        (self::$engine ?? self::$engine = self::defaultEngine())->emit($s, self::L_SMOKE, 1);
    }

    public static function debug(...$s): void
    {
        (self::$engine ?? self::$engine = self::defaultEngine())->emit($s, self::L_DEBUG, 1);
    }

    public static function init(LogEngineInterface $engine): void
    {
        // TODO
    }

    public static function scope(array $context = null): LogScope
    {
        // TODO
    }

    private static function defaultEngine(): LogEngineInterface
    {
        return new TrivialEngine();
    }

    private static function output($msg, $args): void
    {
        $buf = [$msg];
        foreach ($args as $arg) {
            $buf[] = self::formatData($arg);
        }
        echo implode(' ', $buf) . "\n";
    }

    private static function formatData($arg, int $recurse = 1): string
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
