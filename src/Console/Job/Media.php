<?php

namespace Wearesho\Evrotel\Yii\Console\Job;

use Wearesho\Evrotel;
use yii\base;
use yii\queue;
use yii\di;
use yii\caching;

/**
 * Class Job
 * @package Wearesho\Evrotel\Yii\Console
 */
class Media extends Evrotel\Yii\Console\Job
{
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
        $request = $this->getRequest();
        $rawFileName = $request->getFileName();

        if ($this->isPushed($rawFileName)) {
            $this->dial($queue);
            return;
        }

        $cacheKey = $this->getCacheKey($rawFileName);

        /** @var Evrotel\AutoDial\MediaRepository $repository */
        $repository = di\Instance::ensure($this->repository, Evrotel\AutoDial\MediaRepository::class);
        $fileName = $repository->push($rawFileName);
        \Yii::info("Pushed $fileName", static::class);

        if (!is_null($this->cache) && !is_null($cacheKey)) {
            /** @var caching\Cache $cache */
            $cache = di\Instance::ensure($this->cache, caching\Cache::class);
            $cache->set($cacheKey, true, 60 * 60 * 12);
        }

        $this->dial($queue);
    }

    /**
     * Is this job already executed (passed file pushed)
     * Will always return false if no cache provided
     *
     * @return bool
     *
     * @throws base\InvalidConfigException
     */
    public function isPushed(string $fileName = null): bool
    {
        if (is_null($this->cache)) {
            return false;
        }

        /** @var caching\Cache $cache */
        $cache = di\Instance::ensure($this->cache, caching\Cache::class);
        $cacheKey = $this->getCacheKey($fileName ?? $this->getRequest()->getFileName());

        return $cache->exists($cacheKey);
    }

    protected function dial(queue\Queue $queue)
    {
        $job = new Dial([
            'taskId' => $this->taskId,
        ]);
        $queue->push($job);
    }

    protected function getCacheKey(string $fileName): array
    {
        return [
            'type' => static::class,
            'fileName' => $fileName,
        ];
    }
}
