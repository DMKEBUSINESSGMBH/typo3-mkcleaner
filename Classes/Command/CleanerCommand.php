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

namespace DMK\Mkcleaner\Command;

use DMK\Mkcleaner\Service\CleanerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

/**
 * Class CleanerCommand.
 *
 * @author  Hannes Bochmann
 * @author  Markus Crasser
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */

#[AsCommand(
    name: 'dmk:cleanupFolders',
    description: 'LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanerTask.description',
)]
final class CleanerCommand extends Command
{
    public function __construct(
        protected CleanerService $cleanerService,
        protected Helper $taskHelper,
        protected LanguageServiceFactory $languageServiceFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp($this->translate('label.CleanerTask.help'))
            ->addArgument(
                'foldersToClean',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                $this->translate('label.CleanerTask.foldersToClean.description')
            );
    }

    protected function translate(string $key): string
    {
        return $this->languageServiceFactory->create('default')->sL(
            'LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:'.$key
        );
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->taskHelper->getFolderObjectsFromCombinedIdentifiers($input->getArgument('foldersToClean')) as $folderToClean) {
            $this->cleanerService->cleanupFolder($folderToClean);
        }

        return Command::SUCCESS;
    }
}
