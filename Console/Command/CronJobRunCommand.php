<?php

/**
 * This file is part of the CleverAge/Tools package.
 *
 * Copyright (C) 2020-2021 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace CleverAge\Tools\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronJobRunCommand extends Command
{
    public const INPUT_ARG_NAME = 'name';
    public const INPUT_ARG_GROUP = 'group';

    public const COMMAND_NAME = 'cleverage:tools:cronjob:run';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface */
    private $configLoader;

    /** @var \Magento\Cron\Model\Config */
    private $cronConfig;

    /** @var State */
    private $state;

    /** @var \Magento\Framework\App\AreaList */
    private $areaList;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader,
        \Magento\Cron\Model\Config $cronConfig,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\AreaList $areaList,
        $name = null
    ) {
        parent::__construct($name);
        $this->objectManager = $objectManager;
        $this->configLoader = $configLoader;
        $this->cronConfig = $cronConfig;
        $this->state = $state;
        $this->areaList = $areaList;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Execute SQL string on Magento database');

        $this->addArgument(
            self::INPUT_ARG_NAME,
            InputArgument::REQUIRED,
            'Name of the cron job.'
        );
        $this->addArgument(
            self::INPUT_ARG_GROUP,
            InputArgument::OPTIONAL,
            'Group of the cron job.'
        );
    }

    /**
     * @see \Magento\Framework\App\Cron::launch
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobName = $input->getArgument(self::INPUT_ARG_NAME);
        $jobGroup = $input->getArgument(self::INPUT_ARG_GROUP);

        $this->state->setAreaCode(Area::AREA_CRONTAB);
        $this->objectManager->configure($this->configLoader->load(Area::AREA_CRONTAB));
        $this->areaList->getArea(Area::AREA_CRONTAB)->load(Area::PART_TRANSLATE);

        $found = false;
        $jobsByGroup = $this->cronConfig->getJobs();
        if ($jobGroup) {
            $jobsByGroup = [$jobGroup => $jobsByGroup[$jobGroup] ?? []];
        }

        foreach ($jobsByGroup as $group => $jobs) {
            foreach ($jobs as $name => $jobConfig) {
                if ($name === $jobName) {
                    $found = true;

                    $jobInstance = $this->objectManager->create($jobConfig['instance']);
                    $jobMethod = $jobConfig['method'] ?? 'execute';

                    $startTime = microtime(true);
                    $output->writeln(sprintf(
                        '[%s] Found job %s::%s (%s::%s). Executing...',
                        date('Y-m-d H:i:s'),
                        $group,
                        $jobName,
                        $jobConfig['instance'],
                        $jobMethod
                    ));

                    $jobInstance->{$jobMethod}();

                    $output->writeln(sprintf(
                        '[%s] Complete in %s',
                        date('Y-m-d H:i:s'),
                        round(microtime(true) - $startTime, 2)
                    ));
                }
            }
        }
        if (!$found) {
            throw new \RuntimeException(sprintf(
                "Could not find job %s.",
                $jobGroup ? "$jobGroup::$jobName" : $jobName
            ));
        }

        return self::SUCCESS;
    }
}
