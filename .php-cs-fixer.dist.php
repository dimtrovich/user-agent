<?php

declare(strict_types=1);

/**
 * This file is part of Dimtrovich UserAgent Detector.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\CodingStandard\Blitz;
use Nexus\CsConfig\Factory;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->files()
    ->in([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/bin'])
    ->append([__FILE__]);

$overrides = [];

$options = [
    'cacheFile' => 'build/.php-cs-fixer.cache',
    'finder'    => $finder,
];

return Factory::create(new Blitz(), $overrides, $options)->forLibrary(
    'Dimtrovich UserAgent Detector',
    'Dimitri Sitchet Tomkeu',
    'devcode.dst@gmail.com',
    2025,
);
