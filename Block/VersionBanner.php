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

namespace CleverAge\Tools\Block;

use Magento\Framework\View\Element\Template;
use CleverAge\Tools\Helper\Deploy;

class VersionBanner extends Template
{
    /**
     * @var Deploy
     */
    protected $deployHelper;

    public function __construct(
        Template\Context $context,
        Deploy $deployHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->deployHelper = $deployHelper;
    }

    public function shouldDisplay()
    {
        return (bool) $this->_scopeConfig->getValue('cleverage_tools/version_banner/enable_frontend');
    }

    public function getVersion()
    {
        if ($version = $this->deployHelper->getVersion()) {
            return $version;
        }
        return __('(unknown)');
    }

    public function getRevision()
    {
        if ($revision = $this->deployHelper->getRevision()) {
            return $revision;
        }
        return __('(unknown)');
    }

    public function getDate()
    {
        if ($date = $this->deployHelper->getDate()) {
            return $date->format('Y-m-d H:i:s');
        }
        return __('(unknown)');
    }
}
