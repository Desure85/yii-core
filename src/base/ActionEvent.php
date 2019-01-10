<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionEvent represents the event parameter used for an action event.
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 *
 * @method setResult($result): self
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @property \yii\base\Action $action
 */
class ActionEvent extends Event
{
    /**
     * @event raised before executing a controller action.
     * You may set [[Event::isValid]] to `false` to cancel the action execution.
     */
    public const BEFORE = 'action.before';
    /**
     * @event raised after executing a controller action.
     */
    public const AFTER = 'action.after';

    /**
     * Creates BEFORE event.
     * @param Action $action the action this event is fired on.
     * @return self created event
     */
    public static function before(Action $action): self
    {
        return new static(static::BEFORE, $action);
    }

    /**
     * Creates AFTER event with result.
     * @param Action $action the action this event is fired on.
     * @param mixed $result action result.
     * @return self created event
     */
    public static function after(Action $action, $result): self
    {
        return (new static(static::AFTER, $action))->setResult($result);
    }

    /**
     * @return Action the action associated with this event.
     */
    public function getAction(): Action
    {
        return $this->getTarget();
    }
}
