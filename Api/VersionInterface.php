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

namespace CleverAge\Tools\Api;

interface VersionInterface
{
    /**
     * @return \CleverAge\Tools\Api\Data\VersionInterface
     */
    public function get();
}
