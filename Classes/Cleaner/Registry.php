<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.com>
 * All rights reserved
 *
 * This file is part of the "mkcleaner" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mkcleaner\Cleaner;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Registry.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Registry
{
    /**
     * @var array<int, CleanerInterface>
     */
    protected static $registeredCleaners = [];

    /**
     * @return void
     */
    public static function registerCleaner(string $className, int $priority)
    {
        if (isset(self::$registeredCleaners[$priority])) {
            throw new \Exception('Priority '.$priority.' for cleaner '.$className.' already in use');
        }

        $cleaner = GeneralUtility::makeInstance($className);

        if (!$cleaner instanceof CleanerInterface) {
            throw new \Exception('Cleaner '.$className.' needs to implement '.CleanerInterface::class);
        }

        self::$registeredCleaners[$priority] = $cleaner;
        asort(self::$registeredCleaners);
    }

    /**
     * @return void
     */
    public static function unregisterCleaner(string $className)
    {
        foreach (self::$registeredCleaners as $priority => $cleaner) {
            if ($cleaner instanceof $className) {
                unset(self::$registeredCleaners[$priority]);
            }
        }
    }

    /**
     * @return CleanerInterface[]
     */
    public static function getRegisteredCleaners(): array
    {
        return self::$registeredCleaners;
    }
}
