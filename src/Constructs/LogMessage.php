<?php

/*
 * (c) 2019 Go Financial Technologies, JSC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoFinTech\Logging\Constructs;

/*
 * (c) 2019 Go Financial Technologies, JSC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use DateTimeInterface;

class LogMessage
{
    /** @var DateTimeInterface */
    public $timestamp;
    /** @var string Message template */
    public $message;
    /** @var ?array Message parameters */
    public $params;
    /** @var int Bitwise combination of hint flags */
    public $hints;
    /** @var string Message identification code */
    public $code;
    /** @var ?array Context parameters */
    public $context;
}
