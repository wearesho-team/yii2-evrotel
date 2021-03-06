# Yii2 Evrotel Integration
[![Build Status](https://travis-ci.org/wearesho-team/yii2-evrotel.svg?branch=master)](https://travis-ci.org/wearesho-team/yii2-evrotel)
[![codecov](https://codecov.io/gh/wearesho-team/yii2-evrotel/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/yii2-evrotel)

## Installation

```bash
composer require wearesho-team/yii2-evrotel
```

## Requirements
- PostgreSQL 10
- MySQL 8
- PHP 7.2

## Usage

### Configuration
Use [ConfigInterface](./src/ConfigInterface.php) to configure app.
[EnvironmentConfig](./src/EnvironmentConfig.php) will be used by default (in Bootstrap).

Environment keys:
- **EVROTEL_CHANNELS** *(integer, default 5)* - channels will be used in queue
- **EVROTEL_JOB_INTERVAL** *(integer, default 1, minutes)* - timeout between pushing jobs to queue


Also, you can use [Config](./src/Config.php)

### Bootstrap

Use [Bootstrap](./src/Bootstrap.php) for simple application configuration.

```php
<?php
// config.php

use Wearesho\Evrotel;

return [
    'bootstrap' => [
        'evrotel' => [
            'class' => Evrotel\Yii\Bootstrap::class,
        ] ,    
    ],
];

```

### Creating tasks

To create task and send it to call queue you need to create [Task](./src/Task.php) record.
```php
<?php

use Wearesho\Evrotel;
use Wearesho\Yii\Filesystem\Filesystem;

/** @var Filesystem $fs */

$recipient = '380970000000';
$file = 'some-file-content';
$fs->putStream($filePath = 'voice/play.wav', $file);

$task = new Evrotel\Yii\Task([
    'recipient' => $recipient,
    'file' => $filePath, // have to be resolvable using Filesystem
]);
$task->save();

// If you want to schedule next task after receiving stats
$repeat = new Evrotel\Yii\Repeat([
    'task' => $task,
    'min_duration' => 1, // minimal duration to stop creating tasks, seconds
    'max_count' => 1, // maximal tasks count in chain
    'interval' => 5, // interval between tasks, minutes. do not use value less than 5
    'end_at' => '2018-01-01 12:00::00', // date and time when creating new tasks in chain will be blocked
]);
$repeat->save();
```

### Fetching today stats to database

#### Manual calls
To fetch all calls from stats you need to run method check:
```bash
php yii evrotel/check
```
then, `Evrotel\Yii\Call` records will be created

#### Auto Dial Stats
To fetch all calls history for auto dial you need to run method check-auto:
```bash
php yii evrotel/check-auto
```
then, `Evrotel\Yii\Call` records will be created,
automatically assotiated with `Evrotel\Yii\Task` (using `Evrotel\Yii\Task\Call` relation table)
and, if `Evrotel\Yii\Task\Repeat` settings exists new task will be created.

### Running tasks queue
To create queue jobs from tasks you may run 
```bash
php yii evrotel/run
```
it will create some count jobs from task (see config).

To permanent listen for new tasks you should use crontab.

### Cleaning tasks queue
To move all waiting tasks to closed you have to run:
```bash
php yii evrotel/clean
```

## License
[MIT](./LICENSE)
