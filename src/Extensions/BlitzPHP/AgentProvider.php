<?php

/**
 * This file is part of Dimtrovich UserAgent Detector.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dimtrovich\UserAgent\Extensions\BlitzPHP;

use BlitzPHP\Container\AbstractProvider;
use BlitzPHP\Contracts\Container\ContainerInterface;
use Dimtrovich\UserAgent\Agent;
use Psr\SimpleCache\CacheInterface;

class AgentProvider extends AbstractProvider
{
    /**
     * Bindings definitions
     */
    public static function definitions(): array
    {
        return [
            Agent::class => static fn (ContainerInterface $container) => new Agent(
                $container->get('request')->server(),
                $container->get('request')->header('User-Agent'),
                $container->get(CacheInterface::class),
            ),
            'userAgent' => static fn (ContainerInterface $container) => $container->get(Agent::class),
        ];
    }
}
