MK Cleaner
=======

![TYPO3 compatibility](https://img.shields.io/badge/TYPO3-8.7-orange?maxAge=3600&style=flat-square&logo=typo3)
[![Latest Stable Version](https://img.shields.io/packagist/v/dmk/mkcleaner.svg?maxAge=3600&style=flat-square&logo=composer)](https://packagist.org/packages/dmk/mkcleaner)
[![Total Downloads](https://img.shields.io/packagist/dt/dmk/mkcleaner.svg?maxAge=3600&style=flat-square)](https://packagist.org/packages/dmk/mkcleaner)
[![Build Status](https://img.shields.io/github/actions/workflow/status/DMKEBUSINESSGMBH/typo3-mkcleaner/.github/workflows/phpci.yml?maxAge=3600&style=flat-square&logo=github-actions)](https://github.com/DMKEBUSINESSGMBH/typo3-mkcleaner/actions?query=workflow%3APHP-CI)
[![License](https://img.shields.io/packagist/l/dmk/mkcleaner.svg?maxAge=3600&style=flat-square&logo=gnu)](https://packagist.org/packages/dmk/mkcleaner)

What does it do?
----------------

This extension cleans files by removing any metadata. This is a important step when it comes
to security so no sensitive information is leaked.
This is done with the mat2 library
(https://0xacab.org/jvoisin/mat2). You need to install/provide it on the server CLI.
If the mat2 command is not available system-wide the path
can be configured with `$GLOBALS['TYPO3_CONF_VARS']['SYS']['binSetup']` or 
`$GLOBALS['TYPO3_CONF_VARS']['SYS']['binPath']`.

Check the logs for errors.

As soon as everything is setup every file that is added/replaced with the TYPO3 FAL API will be cleaned.
Furthermore there is a scheduler job that can be used for an initial cleanup.
