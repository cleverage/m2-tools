<?php

/**
 * This file is part of the CleverAge/Tools package.
 *
 * Copyright (C) 2020-2023 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\Tools\Console\Command;

use Magento\Framework\ObjectManagerInterface;

class CommandList implements \Magento\Framework\Console\CommandListInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Gets list of command classes
     *
     * @return string[]
     */
    protected function getCommandsClasses()
    {
        return [
            \CleverAge\Tools\Console\Command\CronJobRunCommand::class,
            \CleverAge\Tools\Console\Command\SetupConfigPhpGenCommand::class,
            \CleverAge\Tools\Console\Command\SetupDiCompileSafeCommand::class,
            \CleverAge\Tools\Console\Command\SqlRunCommand::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        $commands = [];
        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->objectManager->get($class);
            } else {
                // phpcs:disable
                throw new \Exception('Class ' . $class . ' does not exist');
                // phpcs:enable
            }
        }
        return $commands;
    }
}
