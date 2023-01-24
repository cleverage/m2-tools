<?php

/**
 * This file is part of the CleverAge/Tools package.
 *
 * Copyright (C) 2020-2023 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace CleverAge\Tools\Console;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ErrorCheckOutputDecorator implements OutputInterface
{
    /**
     * @var OutputInterface
     */
    protected $decorated;

    /**
     * @var boolean
     */
    protected $exceptionOnError = false;

    /**
     * @var array
     */
    protected $errorMessages = [];

    public function __construct(OutputInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        $this->_checkMessageForErrors($messages);
        return $this->decorated->write($messages, $newline, $type);
    }

    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        $this->_checkMessageForErrors($messages);
        return $this->decorated->writeln($messages, $type);
    }

    public function setVerbosity($level)
    {
        return $this->decorated->setVerbosity($level);
    }

    public function getVerbosity()
    {
        return $this->decorated->getVerbosity();
    }

    public function setDecorated($decorated)
    {
        return $this->decorated->setDecorated($decorated);
    }

    public function isDecorated()
    {
        return $this->decorated->isDecorated();
    }

    public function isQuiet()
    {
        return $this->decorated->isQuiet();
    }

    public function isVerbose()
    {
        return $this->decorated->isVerbose();
    }

    public function isDebug()
    {
        return $this->decorated->isDebug();
    }

    public function isVeryVerbose()
    {
        return $this->decorated->isVeryVerbose();
    }

    public function setFormatter(OutputFormatterInterface $formatter)
    {
        return $this->decorated->setFormatter($formatter);
    }

    public function getFormatter()
    {
        return $this->decorated->getFormatter();
    }

    public function setExceptionOnError($exceptionOnError)
    {
        $this->exceptionOnError = $exceptionOnError;

        return $this;
    }

    /**
     * @param  string|array $messages
     * @return $this
     * @throws \Exception
     */
    // phpcs:ignore
    protected function _checkMessageForErrors($messages)
    {
        if (! is_array($messages)) {
            $messages = [$messages];
        }

        $errorMessages = [];
        foreach ($messages as $m) {
            if (strpos($m, '<error>') !== false) {
                $errorMessages[] = strip_tags($m);
            }
        }
        if ($errorMessages && $this->exceptionOnError) {
            throw new \Exception(implode("\n", $errorMessages));
        }
        $this->errorMessages = array_unique(array_merge($this->errorMessages, $errorMessages));

        return $this;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @return $this
     */
    public function resetErrorMessages()
    {
        $this->errorMessages = [];

        return $this;
    }
}
