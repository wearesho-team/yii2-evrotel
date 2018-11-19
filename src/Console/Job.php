<?php

namespace Wearesho\Evrotel\Yii\Console;

use Wearesho\Evrotel;
use yii\base;
use yii\queue;
use yii\di;

/**
 * Class Job
 * @package Wearesho\Evrotel\Yii\Console
 */
class Job extends base\BaseObject implements queue\JobInterface
{
    /** @var string|array|Evrotel\AutoDial\RequestInterface */
    public $request;

    public $worker = [
        'class' => Evrotel\AutoDial\Worker::class,
    ];

    public $repository = [
        'class' => Evrotel\AutoDial\MediaRepository::class,
    ];

    /**
     * @param queue\Queue $queue
     * @throws \yii\base\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute($queue): void
    {
        /** @var Evrotel\AutoDial\RequestInterface $request */
        $request = di\Instance::ensure($this->request, Evrotel\AutoDial\RequestInterface::class);

        /** @var Evrotel\AutoDial\MediaRepository $repository */
        $repository = di\Instance::ensure($this->repository, Evrotel\AutoDial\MediaRepository::class);
        $fileName = $repository->push($request->getFileName());
        \YIi::info("Pushed $fileName", static::class);

        $queue
            ->delay(60)
            ->push(new DialJob([
                'request' => new Evrotel\AutoDial\Request($request->getPhone(), $fileName)
            ]));
    }
}
