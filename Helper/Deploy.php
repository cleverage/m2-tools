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

namespace CleverAge\Tools\Helper;

class Deploy
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileSystemDriver;

    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $fileSystemDriver
    ) {
        $this->fileSystemDriver = $fileSystemDriver;
    }

    /**
     * @return string|null
     */
    public function getVersion()
    {
        $files = [
            BP . '/VERSION',
            BP . '/../VERSION'
        ];
        foreach ($files as $file) {
            if ($this->fileSystemDriver->isFile($file)) {
                return trim($this->fileSystemDriver->fileGetContents($file));
            }
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getRevision()
    {
        $files = [
            BP . '/REVISION',
            BP . '/../REVISION'
        ];
        foreach ($files as $file) {
            if ($this->fileSystemDriver->isFile($file)) {
                return trim($this->fileSystemDriver->fileGetContents($file));
            }
        }
        return null;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate()
    {
        $files = [
            BP . '/VERSION',
            BP . '/../VERSION',
            BP . '/REVISION',
            BP . '/../REVISION'
        ];
        foreach ($files as $file) {
            if ($this->fileSystemDriver->isFile($file)) {
                // phpcs:disable
                return new \DateTime('@' . filemtime($file));
                // phpcs:enable
            }
        }
        return null;
    }
}
