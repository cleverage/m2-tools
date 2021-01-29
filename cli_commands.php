<?php

/**
 * This file is part of the CleverAge/Tools package.
 *
 * Copyright (C) 2020-2021 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (PHP_SAPI == 'cli') {
    \Magento\Framework\Console\CommandLocator::register('CleverAge\Tools\Console\Command\CommandList');
}
