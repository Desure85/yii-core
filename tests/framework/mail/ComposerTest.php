<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\mail;

use yii\helpers\Yii;
use yii\mail\Composer;
use Yiisoft\View\View;
use yii\mail\Template;
use yii\tests\TestCase;

/**
 * @group mail
 */
class ComposerTest extends TestCase
{
    public function testSetupView()
    {
        $this->mockApplication();
        $composer = new Composer();
        $view = $this->app->createObject(View::class);
        $composer->setView($view);
        $this->assertEquals($view, $composer->getView(), 'Unable to setup view!');

        $viewConfig = [
            'params' => [
                'param1' => 'value1',
                'param2' => 'value2',
            ]
        ];
        $composer->setView($viewConfig);
        $view = $composer->getView();
        $this->assertTrue(is_object($view), 'Unable to setup view via config!');
        $this->assertEquals($viewConfig['params'], $view->params, 'Unable to configure view via config array!');
    }

    /**
     * @depends testSetupView
     */
    public function testGetDefaultView()
    {
        $composer = new Composer();
        $view = $composer->getView();
        $this->assertTrue(is_object($view), 'Unable to get default view!');
    }

    /**
     * @depends testGetDefaultView
     */
    public function testCreateTemplate()
    {
        $composer = new Composer();
        $composer->viewPath = '/test/view/path';

        $template = $this->invokeMethod($composer, 'createTemplate', ['test-view']);
        $this->assertInstanceOf(Template::class, $template);

        /* @var $template Template */
        $this->assertSame($composer->getView(), $template->view);
        $this->assertEquals('test-view', $template->viewName);
        $this->assertEquals($composer->htmlLayout, $template->htmlLayout);
        $this->assertEquals($composer->textLayout, $template->textLayout);
    }
}
