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

namespace CleverAge\Tools\Block;

use Magento\Framework\View\Element\Template;

class VersionBanner extends Template
{
    /**
     * @var \CleverAge\Tools\Helper\Block
     */
    protected $blockHelper;

    public function __construct(
        Template\Context $context,
        \CleverAge\Tools\Helper\Block $blockHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->blockHelper = $blockHelper;
    }

    public function shouldDisplay()
    {
        return $this->blockHelper->shouldDisplay('frontend');
    }

    public function getVersion()
    {
        return $this->blockHelper->getVersion();
    }

    public function getRevision()
    {
        return $this->blockHelper->getRevision();
    }

    public function getDate()
    {
        return $this->blockHelper->getDate();
    }
}
