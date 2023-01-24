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

namespace CleverAge\Tools\Model\Data;

/**
 * Model Version
 *
 * @api
 */
class Version extends \Magento\Framework\Api\AbstractSimpleObject implements \CleverAge\Tools\Api\Data\VersionInterface
{
    public const VERSION = 'version';

    /**
     * @return string|null
     */
    public function getVersion()
    {
        return $this->_get(self::VERSION);
    }

    /**
     * @api
     * @param  string $version
     * @return $this
     */
    public function setVersion($version)
    {
        return $this->setData(self::VERSION, $version);
    }

    public const REVISION = 'revision';

    /**
     * @return string|null
     */
    public function getRevision()
    {
        return $this->_get(self::REVISION);
    }

    /**
     * @api
     * @param  string $revision
     * @return $this
     */
    public function setRevision($revision)
    {
        return $this->setData(self::REVISION, $revision);
    }

    public const DATE = 'date';

    /**
     * @return string|null
     */
    public function getDate()
    {
        return $this->_get(self::DATE);
    }

    /**
     * @api
     * @param  string $date
     * @return $this
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }
}
