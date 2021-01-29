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

namespace CleverAge\Tools\Controller\Version;

use Magento\Framework\App\Action\Action;

class Index extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \CleverAge\Tools\Helper\Deploy
     */
    protected $deployHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \CleverAge\Tools\Helper\Deploy $deployHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->deployHelper = $deployHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $result->setData(
            [
            'version' => $this->deployHelper->getVersion(),
            'revision' => $this->deployHelper->getRevision(),
            'date' => $this->deployHelper->getDate(),
            ]
        );

        return $result;
    }
}
