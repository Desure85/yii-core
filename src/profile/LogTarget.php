<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\profile;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use yii\helpers\Yii;

/**
 * LogTarget saves profiling messages as a log messages.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'profiler' => [
 *         'targets' => [
 *             [
 *                 '__class' => yii\profile\LogTarget::class,
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * @property LoggerInterface $logger logger to be used for message export.
 *
 * @author Paul Klimov <klimov-paul@gmail.com>
 * @since 3.0.0
 */
class LogTarget extends Target
{
    /**
     * @var string log level to be used for messages export.
     */
    public $logLevel = LogLevel::DEBUG;

    /**
     * @var LoggerInterface logger to be used for message export.
     */
    private $_logger;


    /**
     * @return LoggerInterface logger to be used for message saving.
     */
    public function getLogger()
    {
        if ($this->_logger === null) {
            $this->_logger = Yii::getContainer()->get('logger');
        }
        return $this->_logger;
    }

    /**
     * @param LoggerInterface|\Closure|array $logger logger instance or its DI compatible configuration.
     */
    public function setLogger($logger): void
    {
        if ($logger === null) {
            $this->_logger = null;
            return;
        }
        if ($logger instanceof \Closure) {
            $logger = call_user_func($logger);
        }
        $this->_logger = Yii::ensureObject($logger, LoggerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function export(array $messages): void
    {
        $logger = $this->getLogger();
        foreach ($messages as $message) {
            $message['time'] = $message['beginTime'];
            $logger->log($this->logLevel, $message['token'], $message);
        }
    }
}
