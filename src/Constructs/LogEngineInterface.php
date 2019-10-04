<?php

/*
 * (c) 2019 Go Financial Technologies, JSC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GoFinTech\Logging\Constructs;


interface LogEngineInterface
{
    public function emit(array $args, int $levelHint, int $callDepth): void;
}
