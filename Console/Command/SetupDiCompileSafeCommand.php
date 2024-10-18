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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CleverAge\Tools\Console\ErrorCheckOutputDecorator;

/**
 * Class SetupDiCompileSafeCommand
 *
 * @see \Magento\Setup\Console\Command\DiCompileCommand
 */
class SetupDiCompileSafeCommand extends Command
{
    public const COMMAND_NAME = 'cleverage:tools:setup:di:compile_safe';

    public function __construct(
        $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(
                'Generates DI configuration and all non-existing interceptors and factories'
                . ' (returns non-zero code in case of failure)'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // phpcs:disable
        $outputDecorator = new ErrorCheckOutputDecorator($output);

        $command = $this->getApplication()->find('setup:di:compile');
        $return = $command->execute($input, $outputDecorator);

        // La commande doit échouer en cas d'erreur de compilation (force l'échec du déploiement)
        if ($errorMessages = $outputDecorator->getErrorMessages()) {
            throw new \Exception(implode("\n", $errorMessages));
        }
        // phpcs:enable

        return $return;
    }
}
