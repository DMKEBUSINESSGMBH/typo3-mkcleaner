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

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractCommandCleaner.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractCommandCleaner implements SingletonInterface, CleanerInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @todo use DI in newer TYPO3 versions.
     */
    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(self::class);
    }

    protected function executeCommand(string $command, string $parameters): bool
    {
        $command = CommandUtility::getCommand($command).' '.$parameters;
        $output = null;
        $returnValue = 0;
        CommandUtility::exec($command, $output, $returnValue);
        if ($returnValue) {
            $this->logger->warning('exec', ['cmd' => $command, 'output' => $output, 'returnValue' => $returnValue]);

            return false;
        }

        $this->logger->info('exec', ['cmd' => $command, 'output' => $output, 'returnValue' => $returnValue]);

        return true;
    }
}
