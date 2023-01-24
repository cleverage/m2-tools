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

namespace CleverAge\Tools\Model\Api;

class Version implements \CleverAge\Tools\Api\VersionInterface
{
    /**
     * @var \CleverAge\Tools\Model\Data\VersionFactory
     */
    protected $versionFactory;

    /**
     * @var \CleverAge\Tools\Helper\Deploy
     */
    protected $deployHelper;

    public function __construct(
        \CleverAge\Tools\Model\Data\VersionFactory $versionFactory,
        \CleverAge\Tools\Helper\Deploy $deployHelper
    ) {
        $this->versionFactory = $versionFactory;
        $this->deployHelper = $deployHelper;
    }

    /**
     * @return \CleverAge\Tools\Api\Data\VersionInterface
     */
    public function get()
    {
        $date = $this->deployHelper->getDate();

        return $this->versionFactory->create()
            ->setVersion($this->deployHelper->getVersion() ?? '<unknown>')
            ->setRevision($this->deployHelper->getRevision() ?? '<unknown>')
            ->setDate($date ? $date->format('c') : '<unknown>');
    }
}
