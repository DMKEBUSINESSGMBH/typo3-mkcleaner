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

use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * Class ExiftoolAndQpdfCleaner.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ExiftoolAndQpdfCleaner extends AbstractCommandCleaner
{
    public function cleanupFile(FileInterface $file): bool
    {
        $filePath = $file->getForLocalProcessing(false);
        $filePathIntermediate = $filePath.'_intermediate';
        $this->executeCommand('exiftool', '-all:all= '.$filePath.' -o '.$filePathIntermediate);
        $this->executeCommand('qpdf', '--linearize '.$filePathIntermediate.' '.$filePath);
        unlink($filePathIntermediate);

        return true;
    }

    public function canHandleFile(FileInterface $file): bool
    {
        return 'application/pdf' == $file->getMimeType();
    }
}
