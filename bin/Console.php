<?php

/**
 * This file is part of Dimtrovich UserAgent Detector.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dimtrovich\SimpleConsole;

use Closure;
use LogicException;
use RuntimeException;
use Throwable;

/**
 * The Console class.
 *
 * @credit  Copyright (C) 2017 Simon Asika.
 */
class Console
{
    /**
     * Property executable.
     */
    protected string $executable;

    /**
     * Property args.
     */
    protected array $args = [];

    /**
     * Property options.
     */
    protected array $options = [];

    /**
     * Property help.
     */
    protected string $help = '';

    /**
     * Property helpIptions.
     */
    protected array $helpOptions = ['h', 'help'];

    /**
     * Property booleanMapping.
     */
    protected array $booleanMapping = [
        0 => ['n', 'no', 'false', 0, '0', true],
        1 => ['y', 'yes', 'true', 1, '1', false, null],
    ];

    /**
     * CliInput constructor.
     */
    public function __construct(?array $argv = null)
    {
        $this->parseArgv($argv ?: $_SERVER['argv']);

        $this->init();
    }

    /**
     * init
     */
    protected function init(): void
    {
        // Override if necessary
    }

    /**
     * execute
     */
    public function execute(?Closure $callback = null): int
    {
        try {
            if ($this->getOption($this->helpOptions)) {
                $this->out($this->getHelp());

                return 0;
            }

            if ($callback) {
                $callback = $callback->bindTo($this);
                $result   = $callback($this);
            } else {
                $result = $this->doExecute();
            }
        } catch (Throwable $e) {
            $result = $this->handleException($e);
        }

        if ($result === true) {
            $result = 0;
        } elseif ($result === false) {
            $result = 255;
        } else {
            $result = (bool) $result;
        }

        return (int) $result;
    }

    /**
     * doExecute
     */
    protected function doExecute(): mixed
    {
        // Please override this method.
        return 0;
    }

    /**
     * delegate
     */
    protected function delegate(string $method): mixed
    {
        $args = func_get_args();
        array_shift($args);

        if (! is_callable([$this, $method])) {
            throw new LogicException(sprintf('Method: %s not found', $method));
        }

        return call_user_func_array([$this, $method], $args);
    }

    /**
     * getHelp
     */
    protected function getHelp(): string
    {
        return trim($this->help);
    }

    /**
     * handleException
     */
    protected function handleException(Throwable $e): int
    {
        $v = $this->getOption('v');

        if ($e instanceof CommandArgsException) {
            $this->err('[Warning] ' . $e->getMessage())
                ->err()
                ->err($this->getHelp());
        } else {
            $this->err('[Error] ' . $e->getMessage());
        }

        if ($v) {
            $this->err('[Backtrace]:')
                ->err($e->getTraceAsString());
        }

        $code = $e->getCode();

        return $code === 0 ? 255 : $code;
    }

    /**
     * getArgument
     */
    public function getArgument(int $offset, mixed $default = null): mixed
    {
        return $this->args[$offset] ?? $default;
    }

    /**
     * setArgument
     *
     * @return static
     */
    public function setArgument(int $offset, mixed $value)
    {
        $this->args[$offset] = $value;

        return $this;
    }

    /**
     * getOption
     *
     * @param array|string $name
     *
     * @return mixed|null
     */
    public function getOption($name, mixed $default = null)
    {
        $name = (array) $name;

        foreach ($name as $n) {
            if (isset($this->options[$n])) {
                return $this->options[$n];
            }
        }

        return $default;
    }

    /**
     * setOption
     *
     * @param array|string $name
     *
     * @return static
     */
    public function setOption($name, mixed $value)
    {
        $name = (array) $name;

        foreach ($name as $n) {
            $this->options[$n] = $value;
        }

        return $this;
    }

    /**
     * out
     *
     * @return static
     */
    public function out(?string $text = null, bool $nl = true)
    {
        fwrite(STDOUT, $text . ($nl ? "\n" : ''));

        return $this;
    }

    /**
     * err
     *
     * @return static
     */
    public function err(?string $text = null, bool $nl = true)
    {
        fwrite(STDERR, $text . ($nl ? "\n" : ''));

        return $this;
    }

    /**
     * in
     */
    public function in(string $ask = '', mixed $default = null, bool $bool = false): string
    {
        $this->out($ask, false);

        $in = rtrim(fread(STDIN, 8192), "\n\r");

        if ($bool) {
            $in = $in === '' ? $default : $in;

            return (bool) $this->mapBoolean($in);
        }

        return $in === '' ? (string) $default : $in;
    }

    /**
     * mapBoolean
     */
    public function mapBoolean(string $in): ?bool
    {
        $in = strtolower($in);

        if (in_array($in, $this->booleanMapping[0], true)) {
            return false;
        }

        if (in_array($in, $this->booleanMapping[1], true)) {
            return true;
        }

        return null;
    }

    /**
     * exec
     *
     * @return static
     */
    protected function exec(string $command)
    {
        $this->out('>> ' . $command);

        system($command);

        return $this;
    }

    /**
     * parseArgv
     */
    protected function parseArgv(array $argv): void
    {
        $this->executable = array_shift($argv);
        $key              = null;

        $out = [];

        for ($i = 0, $j = count($argv); $i < $j; $i++) {
            $arg = $argv[$i];

            // --foo --bar=baz
            if (str_starts_with($arg, '--')) {
                $eqPos = strpos($arg, '=');

                // --foo
                if ($eqPos === false) {
                    $key = substr($arg, 2);

                    // --foo value
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $value = $argv[$i + 1];
                        $i++;
                    } else {
                        $value = $out[$key] ?? true;
                    }

                    $out[$key] = $value;
                } else {
                    // --bar=baz
                    $key       = substr($arg, 2, $eqPos - 2);
                    $value     = substr($arg, $eqPos + 1);
                    $out[$key] = $value;
                }
            } elseif (str_starts_with($arg, '-')) {
                // -k=value -abc

                // -k=value
                if (isset($arg[2]) && $arg[2] === '=') {
                    $key       = $arg[1];
                    $value     = substr($arg, 3);
                    $out[$key] = $value;
                } else {
                    // -abc
                    $chars = str_split(substr($arg, 1));

                    foreach ($chars as $char) {
                        $key       = $char;
                        $out[$key] = isset($out[$key]) ? $out[$key] + 1 : 1;
                    }

                    // -a a-value
                    if (($i + 1 < $j) && ($argv[$i + 1][0] !== '-') && (count($chars) === 1)) {
                        $out[$key] = $argv[$i + 1];
                        $i++;
                    }
                }
            } else {
                // Plain-arg
                $this->args[] = $arg;
            }
        }

        $this->options = $out;
    }
}

class CommandArgsException extends RuntimeException
{
}
