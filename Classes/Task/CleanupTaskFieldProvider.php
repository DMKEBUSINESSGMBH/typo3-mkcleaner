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

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class CleanupTask.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CleanupTaskFieldProvider implements AdditionalFieldProviderInterface
{
    /**
     * @param CleanupTask $task
     *
     * @return array
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $parentObject)
    {
        $fieldName = 'sourcepaths';
        if ('edit' == $parentObject->CMD) {
            $taskInfo[$fieldName] = $task->getSourcepaths();
        }
        $fieldHtml = '<textarea class="form-control" rows="5" cols="50" name="tx_scheduler['.$fieldName.']" id="'
            .$fieldName.'" >'.htmlspecialchars($taskInfo[$fieldName]).'</textarea>';

        return [
            $fieldName => [
                'code' => $fieldHtml,
                'label' => 'LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanupTask.sourcepaths',
                'cshKey' => '',
                'cshLabel' => '',
            ],
        ];
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $parentObject)
    {
        $validInput = true;
        $sourcepaths = GeneralUtility::trimExplode(LF, $submittedData['sourcepaths'], true);
        foreach ($sourcepaths as $sourcepath) {
            if (!is_dir($sourcepath)) {
                $validInput = false;
                break;
            }
            if (!is_readable($sourcepath)) {
                $validInput = false;
                break;
            }
        }
        if (!$validInput || empty($submittedData['sourcepaths'])) {
            $parentObject->addMessage(
                $GLOBALS['LANG']->sL('LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:message.CleanupTask.sourcepaths.invalid'),
                FlashMessage::ERROR
            );
            $validInput = false;
        }

        return $validInput;
    }

    /**
     * @param CleanupTask $task
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->setSourcepaths($submittedData['sourcepaths']);
    }
}
