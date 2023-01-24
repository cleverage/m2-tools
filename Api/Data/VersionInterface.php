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

namespace CleverAge\Tools\Api\Data;

/**
 * Interface VersionInterface
 *
 * @api
 */
interface VersionInterface
{
    /**
     * @return string|null
     * @api
     */
    public function getVersion();

    /**
     * @param string $version
     *
     * @return $this
     * @api
     */
    public function setVersion($version);

    /**
     * @return string|null
     * @api
     */
    public function getRevision();

    /**
     * @param string $revision
     *
     * @return $this
     * @api
     */
    public function setRevision($revision);

    /**
     * @return string|null
     * @api
     */
    public function getDate();

    /**
     * @param string $date
     *
     * @return $this
     * @api
     */
    public function setDate($date);
}
