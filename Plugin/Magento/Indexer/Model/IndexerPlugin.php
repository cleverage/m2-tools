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

namespace CleverAge\Tools\Plugin\Magento\Indexer\Model;

use Magento\Framework\Indexer\StateInterface;

class IndexerPlugin
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function aroundReindexAll(\Magento\Indexer\Model\Indexer $subject, callable $proceed)
    {
        if ($subject->getState()->getStatus() == StateInterface::STATUS_WORKING) {
            if (PHP_SAPI == 'cli') {
                @fwrite(
                    STDERR,
                    "WARNING: Indexer {$subject->getId()} is flagged as 'working', will likely be skipped.\n"
                );
            }
            $this->logger->warning("Attempting to reindex {$subject->getId()} while working, will likely be skipped.");
        }

        return $proceed();
    }
}
