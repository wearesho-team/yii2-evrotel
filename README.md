# Yii2 Evrotel Integration

## Installation

```bash
composer require wearesho-team/yii2-evrotel
```

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
```

### Fetching today stats to database

To fetch all calls from stats you need to run method check:
```bash
php yii evrotel/check
```
then, `Evrotel\Yii\Call` records will be created

### Running tasks queue
To create queue jobs from tasks you may run 
```bash
php yii evrotel/run
```
it will create some count jobs from task (see config).

To permanent listen for new tasks you may use command
```bash
php yii evrotel/listen
```

## License
[MIT](./LICENSE)
