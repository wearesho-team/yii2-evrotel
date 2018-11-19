<?php

namespace Wearesho\Evrotel\Yii\Console;

use Wearesho\Evrotel;
use yii\di;
use yii\base;
use yii\queue;

/**
 * Class DialJob
 * @package Wearesho\Evrotel\Yii\Console
 */
class DialJob extends base\BaseObject implements queue\JobInterface
{
    /** @var string|array|Evrotel\AutoDial\RequestInterface */
    public $request;

    /** @var string|array|Evrotel\AutoDial\Worker */
    public $worker = [
        'class' => Evrotel\AutoDial\Worker::class,
    ];

    /**
     * @param queue\Queue $queue which pushed and is handling the job
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws base\InvalidConfigException
     */
    public function execute($queue)
    {
        /** @var Evrotel\AutoDial\Worker $worker */
        $worker = di\Instance::ensure($this->worker, Evrotel\AutoDial\Worker::class);
        /** @var Evrotel\AutoDial\RequestInterface $request */
        $request = di\Instance::ensure($this->request, Evrotel\AutoDial\RequestInterface::class);

        $response = $worker->push($request);
        \Yii::info("Response: " . (string)$response->getBody(), static::class);
    }
}
