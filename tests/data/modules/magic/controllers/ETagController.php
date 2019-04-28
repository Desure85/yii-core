<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\data\modules\magic\controllers;

class ETagController extends \Yiisoft\Yii\Console\Controller
{
    public function actionListETags()
    {
        return '';
    }

    public function actionDelete()
    {
        return 'deleted';
    }
}
