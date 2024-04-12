MK Cleaner
=======

![TYPO3 compatibility](https://img.shields.io/badge/TYPO3-8.7-orange?maxAge=3600&style=flat-square&logo=typo3)
[![Latest Stable Version](https://img.shields.io/packagist/v/dmk/mkcleaner.svg?maxAge=3600&style=flat-square&logo=composer)](https://packagist.org/packages/dmk/mkcleaner)
[![Total Downloads](https://img.shields.io/packagist/dt/dmk/mkcleaner.svg?maxAge=3600&style=flat-square)](https://packagist.org/packages/dmk/mkcleaner)
[![Build Status](https://img.shields.io/github/actions/workflow/status/DMKEBUSINESSGMBH/typo3-mkcleaner/.github/workflows/phpci.yml?maxAge=3600&style=flat-square&logo=github-actions)](https://github.com/DMKEBUSINESSGMBH/typo3-mkcleaner/actions?query=workflow%3APHP-CI)
[![License](https://img.shields.io/packagist/l/dmk/mkcleaner.svg?maxAge=3600&style=flat-square&logo=gnu)](https://packagist.org/packages/dmk/mkcleaner)

What does it do?
----------------

This extension cleans files by removing most of the metadata. This is a important step when it comes
to security so no sensitive information is leaked.
This is done with mat2 (https://0xacab.org/jvoisin/mat2), exiftool (https://exiftool.org/) and qpdf (https://github.com/qpdf/qpdf)
in the moment. You need to install/provide those commands on the server CLI.
If some command is not available system-wide the path
can be configured with `$GLOBALS['TYPO3_CONF_VARS']['SYS']['binSetup']` or 
`$GLOBALS['TYPO3_CONF_VARS']['SYS']['binPath']`.

Check the logs for errors after uploading files etc.

As soon as everything is setup almost every file that is added/replaced with the TYPO3 FAL API will be cleaned.
Furthermore there is a scheduler job that can be used for an initial cleanup of desired folders.


Adding a custom cleaner
----------------
A custom cleaner can be added in `ext_localconf.php` like this:
```php
\DMK\Mkcleaner\Cleaner\Registry::registerCleaner(\Vendor\Package\Cleaner\CustomCleaner::class, 100);
```    

Every cleaner needs to implement `DMK\Mkcleaner\Cleaner\CleanerInterface`. Please take a look at the 
existing cleaners.

Drawbacks
----------------
Please keep in mind that neither of the tools is perfect and might break files or leave metadata present. 
For example pdf files sometimes loose all links or svg files have changed content when mat2 would
be used.
Therefore pdf files are cleaned with exiftool and qpdf instead of mat2 and svg files are omitted
completely. Please take care of having svg files without metadata yourself.
