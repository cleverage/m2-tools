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

namespace CleverAge\Tools\Plugin\Magento\Backend\Block\Page;

use CleverAge\Tools\Helper\Deploy;

class FooterPlugin
{
    /**
     * @var Deploy
     */
    protected $deployHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Deploy $deployHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->deployHelper = $deployHelper;
    }

    public function afterToHtml(\Magento\Backend\Block\Page\Footer $subject, $result)
    {
        if ($this->scopeConfig->getValue('cleverage_tools/version_banner/enable_adminhtml')) {
            $date = $this->deployHelper->getDate();
            $info = [
                __('Version: <strong>%1</strong>', $this->deployHelper->getVersion()),
                __('Revision: <strong>%1</strong>', $this->deployHelper->getRevision()),
                __(
                    'Date: <strong>%1</strong>',
                    (is_object($date) ? $date->format('Y-m-d H:i:s') : $date)
                )
            ];
            $string = implode(' | ', $info);
            $result .= "<p class=\"cleverage-tools-version\">$string</p>";
        }

        return $result;
    }
}
