<?php

/**
 * This file is part of Dimtrovich UserAgent Detector.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dimtrovich\UserAgent\Extensions\BlitzPHP\Facades;

use BlitzPHP\Facades\Facade;

/**
 * @mixin \Dimtrovich\UserAgent\Agent
 */
final class Agent extends Facade
{
    protected static function accessor(): string
    {
        return 'userAgent';
    }
}
