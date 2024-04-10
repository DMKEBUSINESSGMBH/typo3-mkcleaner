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

namespace DMK\Mkcleaner\Service;

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Mat2Service.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Mat2Service implements SingletonInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    public function cleanupFolder(string $path): bool
    {
        if (!is_dir($path)) {
            $this->logger->error('failed: '.$path.' ist not a directory');

            return false;
        }
        if (!is_readable($path)) {
            $this->logger->error('failed: '.$path.' ist not readable');

            return false;
        }
        $resource = opendir($path);
        if (!$resource) {
            return false;
        }
        while (($file = readdir($resource)) !== false) {
            if ('.' == $file or '..' == $file) {
                continue;
            }
            $file = realpath($path.DIRECTORY_SEPARATOR.$file);

            if (is_dir($file)) {
                $this->cleanupFolder($file);
                continue;
            }

            $this->cleanupFile($file);
        }
        closedir($resource);

        return true;
    }

    public function cleanupFile(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        $command = CommandUtility::getCommand('mat2').' --inplace --lightweight '.$path;
        $output = $returnValue = '';
        CommandUtility::exec($command, $output, $returnValue);
        $this->logger->info('exec', ['cmd' => $command, 'output' => $output, 'returnValue' => $returnValue]);

        return true;
    }
}
