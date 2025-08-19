# EXT:sentry_bridge (addon for `networkteam/sentry-client`)

![TYPO3 Version](https://img.shields.io/packagist/dependency-v/andersundsehr/sentry-client/typo3%2Fcms-core?style=flat-square&logo=typo3&color=orange)
![PHP Version](https://img.shields.io/packagist/dependency-v/andersundsehr/sentry-client/php?style=flat-square&logo=php)

## Install

````bash
composer require andersundsehr/sentry-bridge
````
- Set your DSN in the config for `sentry_client`.
- Add Async Transport to your `systems/additional.php` file (see below)
- add Cronjob to run `typo3 andersundsehr:sentry-async:flush` every minute or as needed

## Features

- Async transport to sentry
- TYPO3 Log as Sentry Breadcrumbs
- Link to Sentry (even with Queue enabled) for ContentObjectProductionExceptionHandler
- Bugfix: Console command exceptions are captured again.

## Requirements

Sentry is initialized very early in the TYPO3 bootstrap process, so it is important to ensure that the configuration is set up correctly before any other extensions or TYPO3 core code is executed.

put this line in your `systems/additional.php` file:
```PHP
$GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler'] = \Networkteam\SentryClient\ProductionExceptionHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler'] = \Networkteam\SentryClient\DebugExceptionHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_client']['release'] = trim((string)exec('git rev-parse --verify HEAD'));
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sentry_client']['options']['transport'] ??= (new \AUS\SentryBridge\Factory\QueueTransportFactory())();
$GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
        \Networkteam\SentryClient\SentryLogWriter::class => [],
    ],
];
```

# with ♥️ from anders und sehr GmbH

> If something did not work 😮  
> or you appreciate this Extension 🥰 let us know.

> We are always looking for great people to join our team!
> https://www.andersundsehr.com/karriere/
