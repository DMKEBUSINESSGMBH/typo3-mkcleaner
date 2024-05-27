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

if (!defined('TYPO3')) {
    exit('Access denied.');
}
call_user_func(
    function () {
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $signalSlotDispatcher->connect(
            \TYPO3\CMS\Core\Resource\ResourceStorage::class,
            \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileAdd,
            \DMK\Mkcleaner\SignalSlot\ResourceStorage::class,
            'cleanupFile'
        );
        $signalSlotDispatcher->connect(
            \TYPO3\CMS\Core\Resource\ResourceStorage::class,
            \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileReplace,
            \DMK\Mkcleaner\SignalSlot\ResourceStorage::class,
            'cleanupFile'
        );
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\DMK\Mkcleaner\Task\CleanerTask::class] = [
            'extension' => 'mkcleaner',
            'title' => 'LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanerTask.title',
            'description' => 'LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanerTask.description',
            'additionalFields' => \DMK\Mkcleaner\Task\CleanerTaskFieldProvider::class,
        ];

        \DMK\Mkcleaner\Cleaner\Registry::registerCleaner(\DMK\Mkcleaner\Cleaner\Mat2Cleaner::class, 50);
        \DMK\Mkcleaner\Cleaner\Registry::registerCleaner(\DMK\Mkcleaner\Cleaner\ExiftoolAndQpdfCleaner::class, 75);
    }
);
