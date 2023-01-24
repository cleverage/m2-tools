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

namespace CleverAge\Tools\Console\Command;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Setup\Model\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see \Magento\Setup\Console\Command\UpgradeCommand
 */
class SetupConfigPhpGenCommand extends Command
{
    public const COMMAND_NAME = 'cleverage:tools:setup:configphpgen';

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader
     */
    private $moduleLoader;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Reader
     */
    private $deploymentConfigReader;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Writer
     */
    private $deploymentConfigWriter;

    /**
     * Inject dependencies
     *
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\Module\ModuleList\Loader $moduleLoader,
        \Magento\Framework\App\DeploymentConfig\Reader $deploymentConfigReader,
        \Magento\Framework\App\DeploymentConfig\Writer $deploymentConfigWriter,
        $name = null
    ) {
        parent::__construct($name);
        $this->moduleLoader = $moduleLoader;
        $this->deploymentConfigReader = $deploymentConfigReader;
        $this->deploymentConfigWriter = $deploymentConfigWriter;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('(Re-)generate config.php');

        parent::configure();
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Regenerating config.php...");
        $this->createModulesConfig([]);
        $output->writeln("Done.");
    }

    /**
     * Creates modules deployment configuration segment
     *
     * @see \Magento\Setup\Model\Installer::createModulesConfig()
     *
     * @param  \ArrayObject|array $request
     * @return array
     * @throws \LogicException
     */
    protected function createModulesConfig($request)
    {
        $all = array_keys($this->moduleLoader->load());
        $deploymentConfig = $this->deploymentConfigReader->load();
        $currentModules = isset($deploymentConfig[ConfigOptionsListConstants::KEY_MODULES])
            ? $deploymentConfig[ConfigOptionsListConstants::KEY_MODULES] : [] ;
        $enable = $this->readListOfModules($all, $request, Installer::ENABLE_MODULES);
        $disable = $this->readListOfModules($all, $request, Installer::DISABLE_MODULES);
        $result = [];
        foreach ($all as $module) {
            if ((isset($currentModules[$module]) && !$currentModules[$module])) {
                $result[$module] = 0;
            } else {
                $result[$module] = 1;
            }
            if (in_array($module, $disable)) {
                $result[$module] = 0;
            }
            if (in_array($module, $enable)) {
                $result[$module] = 1;
            }
        }
        $this->deploymentConfigWriter->saveConfig([ConfigFilePool::APP_CONFIG => ['modules' => $result]], true);
        return $result;
    }

    /**
     * Determines list of modules from request based on list of all modules
     *
     * @see \Magento\Setup\Model\Installer::readListOfModules()
     *
     * @param  string[] $all
     * @param  array    $request
     * @param  string   $key
     * @return string[]
     * @throws \LogicException
     */
    protected function readListOfModules($all, $request, $key)
    {
        $result = [];
        if (!empty($request[$key])) {
            if ($request[$key] == 'all') {
                $result = $all;
            } else {
                $result = explode(',', $request[$key]);
                foreach ($result as $module) {
                    if (!in_array($module, $all)) {
                        throw new \LogicException("Unknown module in the requested list: '{$module}'");
                    }
                }
            }
        }
        return $result;
    }
}
