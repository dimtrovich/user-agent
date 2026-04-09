<?php

/**
 * This file is part of Dimtrovich UserAgent Detector.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// phpcs:disable

use Dimtrovich\SimpleConsole\Console;

include_once __DIR__ . '/Console.php';

class Build extends Console
{
    /**
     * Property help.
     */
    protected string $help = <<<'HELP'
        [Usage] php release.php <version> <next_version>

        [Options]
            h | help   Show help information
            v          Show more debug information.
            --dry-run  Dry run without git push or commit.
        HELP;

    /**
     * doExecute
     *
     * @return bool|mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function doExecute(): mixed
    {
        $currentVersion = trim(file_get_contents(__DIR__ . '/../VERSION'));
        $targetVersion  = $this->getArgument(0);

        if (! $targetVersion) {
            $targetVersion = static::versionPlus($currentVersion, 1);
        }

        $this->out('Release version: ' . $targetVersion);

        static::writeVersion($targetVersion);

        $this->exec(sprintf('git commit -am "Release version: %s"', $targetVersion));
        $this->exec(sprintf('git tag %s', $targetVersion));

        $this->exec('git push');
        $this->exec('git push --tags');

        return true;
    }

    /**
     * writeVersion
     *
     * @return bool|int
     */
    protected static function writeVersion(string $version)
    {
        return file_put_contents(static::versionFile(), $version . "\n");
    }

    /**
     * versionFile
     */
    protected static function versionFile(): string
    {
        return __DIR__ . '/../VERSION';
    }

    /**
     * versionPlus
     */
    protected static function versionPlus(string $version, int $offset, string $suffix = ''): string
    {
        [$version] = explode('-', $version, 2);

        $numbers = explode('.', $version);

        if (! isset($numbers[2])) {
            $numbers[2] = 0;
        }

        $numbers[2] += $offset;

        if ($numbers[2] === 0) {
            unset($numbers[2]);
        }

        $version = implode('.', $numbers);

        if ($suffix) {
            $version .= '-' . $suffix;
        }

        return $version;
    }

    /**
     * exec
     *
     * @return static
     */
    protected function exec(string $command)
    {
        $this->out('>> ' . $command);

        if (! $this->getOption('dry-run')) {
            system($command);
        }

        return $this;
    }
}

exit((new Build())->execute());
