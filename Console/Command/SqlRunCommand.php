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

use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SqlRunCommand extends Command
{
    public const INPUT_ARG_QUERY = 'query';
    public const INPUT_OPTION_CONNECTION = 'connection';
    public const INPUT_OPTION_FORMAT = 'format';

    public const OPTION_FORMAT_TABLE = 'table';
    public const OPTION_FORMAT_JSON = 'json';
    public const OPTION_FORMAT_JSON_PRETTY = 'pretty-json';
    public const OPTION_FORMAT_CSV = 'csv';
    public const OPTION_FORMAT_SCRIPT = 'script';

    public const COMMAND_NAME = 'cleverage:tools:sql:run';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        $name = null
    ) {
        $this->resourceConnection = $resource;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Execute SQL string on Magento database');

        $this->addArgument(
            self::INPUT_ARG_QUERY,
            InputArgument::REQUIRED,
            'SQL Query.'
        )->addOption(
            self::INPUT_OPTION_FORMAT,
            'f',
            InputOption::VALUE_REQUIRED,
            'Output format (default is human-readable table).',
            self::OPTION_FORMAT_TABLE
        )->addOption(
            self::INPUT_OPTION_CONNECTION,
            'c',
            InputOption::VALUE_REQUIRED,
            'Name of the DB connection to use.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $input->getArgument(self::INPUT_ARG_QUERY);
        $connection = $this->getConnection($input->getOption(self::INPUT_OPTION_CONNECTION));

        $stmt = $connection->query($query);

        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $errOutput->writeln(sprintf('Query executed successfully, %d row(s) affected/returned.', $stmt->rowCount()));

        if ($stmt->rowCount()) {
            $result = [];
            $headers = null;
            for ($i = 0; $i < 3 && ($row = $stmt->fetch(\PDO::FETCH_ASSOC)); $i++) {
                if ($headers === null) {
                    $headers = array_keys($row);
                }
                $result[] = $row;
            }
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }

            switch ($input->getOption(self::INPUT_OPTION_FORMAT)) {
                case self::OPTION_FORMAT_JSON:
                    $output->writeln(json_encode($result));
                    break;

                case self::OPTION_FORMAT_JSON_PRETTY:
                    $output->writeln(json_encode($result, JSON_PRETTY_PRINT));
                    break;

                case self::OPTION_FORMAT_CSV:
                    $csv = new Csv(new File());
                    $csv->appendData('php://stdout', [$headers]);
                    $csv->appendData('php://stdout', $result);
                    break;

                case self::OPTION_FORMAT_SCRIPT:
                    foreach ($result as $record) {
                        array_walk(
                            $record,
                            function (&$v, $k) {
                                $v = "$k=$v";
                            }
                        );
                        $output->writeln(implode('|', $record));
                    }
                    break;

                case self::OPTION_FORMAT_TABLE:
                default:
                    (new Table($output))->setHeaders($headers)
                    ->setRows($result)
                    ->render();
            }
        }
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection($name = null)
    {
        return $this->resourceConnection->getConnection($name ?: ModuleDataSetupInterface::DEFAULT_SETUP_CONNECTION);
    }
}
