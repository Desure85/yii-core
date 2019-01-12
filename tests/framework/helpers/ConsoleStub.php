<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\helpers;

use yii\helpers\Console;

/**
 * Console helper stub for STDIN/STDOUT/STDERR replacement
 *
 * @author Pavel Dovlatov <mysterydragon@yandex.ru>
 */
class ConsoleStub extends Console
{
    /**
     * @var resource input stream
     */
    public static $inputStream = \STDIN;

    /**
     * @var resource output stream
     */
    public static $outputStream = \STDOUT;

    /**
     * @var resource error stream
     */
    public static $errorStream = \STDERR;


    /**
     * {@inheritdoc}
     */
    public static function stdin(bool $raw = false): string
    {
        return $raw ? fgets(self::$inputStream) : rtrim(fgets(self::$inputStream), PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public static function stdout(string $string)
    {
        return fwrite(self::$outputStream, $string);
    }

    /**
     * {@inheritdoc}
     */
    public static function stderr(string $string)
    {
        return fwrite(self::$errorStream, $string);
    }
}
