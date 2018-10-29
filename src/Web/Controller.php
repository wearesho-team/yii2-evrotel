<?php

namespace Wearesho\Evrotel\Yii\Web;

use Carbon\Carbon;
use Wearesho\Yii\Filesystem\Filesystem;
use Wearesho\Evrotel;
use Horat1us\Yii\Exceptions\ModelException;
use yii\web;
use yii\base;
use yii\di;

/**
 * Class Controller
 * @package Wearesho\Evrotel\Yii
 */
class Controller extends web\Controller
{
    public $fs = [
        'class' => Filesystem::class,
    ];

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->fs = di\Instance::ensure($this->fs, Filesystem::class);
    }

    /**
     * @return int
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     * @throws web\HttpException
     */
    public function actionIndex(): int
    {
        \Yii::$app->response->format = web\Response::FORMAT_RAW;

        $config = new Evrotel\EnvironmentConfig();
        $receiver = new Evrotel\Receiver($config);

        try {
            $request = $receiver->getRequest();

            if ($request instanceof Evrotel\Receiver\Request\Start) {
                $record = new Evrotel\Yii\Call([
                    'direction' => $request->getDirection(),
                    'from' => $request->getFrom(),
                    'to' => $request->getTo(),
                    'finished' => true,
                    'at' => Carbon::instance($request->getDate())->toDateTimeString(),
                ]);
                ModelException::saveOrThrow($record);

                /**
                 * You have to return ID in response body
                 * to receive it in call end request
                 */
                return $record->id;
            } elseif ($request instanceof Evrotel\Receiver\Request\End) {
                $record = Evrotel\Yii\Call::findOne($request->getId());
                if (!$record instanceof Evrotel\Yii\Call) {
                    throw new web\NotFoundHttpException(
                        "Record {$request->getId()} does not exist",
                        0
                    );
                }
                $record->duration = $request->getDuration()->s;
                $record->disposition = $request->getDisposition();
                $record->file = $request->getRecordFileUrl();

                ModelException::saveOrThrow($record);

                return $record->id;
            }
        } catch (Evrotel\Exceptions\AccessDenied $denied) {
            throw new web\ForbiddenHttpException(
                "Invalid authorization header",
                0,
                $denied
            );
        } catch (Evrotel\Exceptions\BadRequest $badRequest) {
            throw new web\BadRequestHttpException(
                $badRequest->getMessage(),
                0,
                $badRequest
            );
        }
    }
}
