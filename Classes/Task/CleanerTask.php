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

namespace DMK\Mkcleaner\Task;

use DMK\Mkcleaner\Service\CleanerService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class CleanerTask.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 *
 * @todo migrate to a symfony command with newer TYPO3 version
 * @todo use DI in newer TYPO3 versions for taskHelper and cleanerService.
 */
class CleanerTask extends AbstractTask
{
    /**
     * @var string
     */
    protected $foldersToClean = '';

    /**
     * @return void
     */
    public function setFoldersToClean(string $foldersToClean)
    {
        $this->foldersToClean = $foldersToClean;
    }

    public function getFoldersToClean(): string
    {
        return $this->foldersToClean;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        $taskHelper = GeneralUtility::makeInstance(Helper::class);
        $cleanerService = GeneralUtility::makeInstance(CleanerService::class);
        foreach ($taskHelper->getFolderObjectsFromCombinedIdentifiers($this->getFoldersToClean()) as $folderToClean) {
            $cleanerService->cleanupFolder($folderToClean);
        }

        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getAdditionalInformation(string $info = ''): string
    {
        return $info.CRLF.$GLOBALS['LANG']->sL('LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanerTask.foldersToClean').
            ': '.CRLF.$this->getFoldersToClean();
    }
}