<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\profile;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use yii\helpers\Yii;
use yii\profile\LogTarget;
use yii\tests\TestCase;

class LogTargetTest extends TestCase
{
    /**
     * @covers \yii\profile\LogTarget::setLogger()
     * @covers \yii\profile\LogTarget::getLogger()
     */
    public function testSetupLogger()
    {
        $logger = new NullLogger();
        $target = new LogTarget($logger);

        $this->assertSame($logger, $target->getLogger());
    }

    /**
     * @depends testSetupLogger
     *
     * @covers \yii\profile\LogTarget::export()
     */
    public function testExport()
    {
        /* @var $logger LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->setMethods([
                'log'
            ])
            ->getMockForAbstractClass();

        $target = new LogTarget($logger);
        $target->logLevel = 'test-level';

        $logger->expects($this->once())
            ->method('log')
            ->with($this->equalTo($target->logLevel), $this->equalTo('test-token'));

        $target->export([
            [
                'category' => 'test',
                'token' => 'test-token',
                'beginTime' => 123,
                'endTime' => 321,
            ],
        ]);
    }
}
