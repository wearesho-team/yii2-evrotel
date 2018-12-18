<?php

namespace Wearesho\Evrotel\Yii\Console;

use Wearesho\Evrotel;
use yii\base;
use yii\queue;
use yii\di;
use yii\caching;

/**
 * Class Job
 * @package Wearesho\Evrotel\Yii\Console
 */
class Job extends base\BaseObject implements queue\JobInterface
{
    /** @var string|array|Evrotel\AutoDial\RequestInterface */
    public $request;

    public $repository = [
        'class' => Evrotel\AutoDial\MediaRepository::class,
    ];

    /** @var string|array|caching\Cache */
    public $cache = 'cache';

    /**
     * @param queue\Queue $queue
     * @throws \yii\base\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute($queue): void
    {
        /** @var Evrotel\AutoDial\RequestInterface $request */
        $request = di\Instance::ensure($this->request, Evrotel\AutoDial\RequestInterface::class);
        $rawFileName = $request->getFileName();

        if (!is_null($this->cache)) {
            /** @var caching\Cache $cache */
            $cache = di\Instance::ensure($this->cache, caching\Cache::class);
            $cacheKey = $this->getCacheKey($rawFileName);

            if ($cache->exists($cacheKey)) {
                $this->push($queue, $request);
                return;
            }
        }

        /** @var Evrotel\AutoDial\MediaRepository $repository */
        $repository = di\Instance::ensure($this->repository, Evrotel\AutoDial\MediaRepository::class);
        $fileName = $repository->push($rawFileName);
        \Yii::info("Pushed $fileName", static::class);

        if (!is_null($cache) && !is_null($cacheKey)) {
            $cache->set($cacheKey, true, 60 * 60 * 12);
        }

        $this->push(
            $queue,
            new Evrotel\AutoDial\Request($request->getPhone(), $fileName)
        );
    }

    protected function push(queue\Queue $queue, Evrotel\AutoDial\RequestInterface $request)
    {
        $queue
            ->delay(120)
            ->push(new DialJob([
                'request' => $request,
            ]));
    }

    protected function getCacheKey(string $fileName): array
    {
        return [
            'type' => static::class,
            'fileName' => $fileName,
        ];
    }
}
