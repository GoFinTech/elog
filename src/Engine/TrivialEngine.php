<?php

/*
 * (c) 2019 Go Financial Technologies, JSC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoFinTech\Logging\Engine;


use DateTime;
use GoFinTech\Logging\Constructs\LogEngineInterface;
use GoFinTech\Logging\elog;

class TrivialEngine implements LogEngineInterface
{
    public function emit(array $args, int $levelHint, int $callDepth): void
    {
        $count = count($args);
        for ($i = 0; $i < $count; $i++) {
            $arg = $args[$i];
            if (is_int($arg)) {
                $levelHint |= $arg;
                continue;
            }
            if (is_string($arg)) {
                $template = $arg;
                $params = $args[$i + 1] ?? null;
                if (!is_array($params))
                    $params = [];
                $this->output($levelHint, $template, $params, $callDepth + 1);
                return;
            }
            break;
        }
        $this->output(elog::L_WARN, '[Badly constructed elog call]', [], $callDepth + 1);
    }

    private function output(int $hint, string $template, array $params, int $callDepth): void
    {
        $msg = array('[', (new DateTime())->format(DATE_ISO8601), ']');
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $callDepth + 2);
        $me = $trace[$callDepth];
        $file = pathinfo($me['file'], PATHINFO_BASENAME);
        $line = $me['line'];
        $caller = $trace[$callDepth + 1] ?? null;
        if ($caller)
            $function = $caller['function'];
        else
            $function = '__main__';
        switch ($hint & elog::L_AUDIT) {
            case elog::L_INFO:
                $msg[] = ' INFO ';
                break;
            case elog::L_AUDIT:
                $msg[] = ' AUDIT ';
                break;
            case elog::L_WARN:
                $msg[] = ' WARN ';
                break;
            case elog::L_SMOKE:
                $msg[] = ' SMOKE ';
                break;
            case elog::L_CRASH:
                $msg[] = ' CRASH ';
                break;
            case elog::L_TRACE:
                $msg[] = ' TRACE ';
                break;
            case elog::L_DEBUG:
                $msg[] = ' DEBUG ';
                break;
            default:
                $msg[] = ' UNDEF ';
                break;
        }
        $msg[] = "$file:$line:$function ";
        $msg[] = preg_replace_callback('/{({|[-_.a-zA-Z0-9]+})/', function ($m) use ($params) {
            if ($m[1] == '{')
                return '{';
            $name = substr($m[1], 0, -1);
            return $params[$name] ?? '{' . $name . '?}';
        }, $template);
        $msg[] = "\n";
        echo implode($msg);
    }
}
