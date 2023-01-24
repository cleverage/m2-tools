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

namespace CleverAge\Tools\Helper;

class ExceptionCompatibility
{
    /**
     * Convertit les exceptions de type \Error introduites en PHP7 en \RuntimeException
     * compatibles avec les librairies plus anciennes (sinon on génère souvent une exception lors du
     * traitement... de l'exception !)
     *
     * @param \Throwable $e
     *
     * @return \RuntimeException|\Throwable
     */
    public static function errorToException(\Throwable $e)
    {
        if ($e instanceof \Error) {
            $errorType = get_class($e);
            return new \RuntimeException(
                "[$errorType] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}",
                $e->getCode(),
                $e->getPrevious()
            );
        }
        return $e;
    }
}
