<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 *
 * @see https://dev.mysql.com/doc/refman/5.6/en/innodb-locking-reads.html
 */
namespace yiidreamteam\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class LockableActiveRecord
 * @package yiidreamteam\behaviors
 */
class LockableActiveRecord extends Behavior
{
    const LOCK_SHARE_MODE = 'LOCK IN SHARE MODE';
    const LOCK_FOR_UPDATE = 'FOR UPDATE';

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        if (!$owner instanceof ActiveRecord)
            throw new InvalidConfigException('Please attach this behavior to the instance of the ActiveRecord class');
        parent::attach($owner);
    }

    /**
     * Lock for Update
     */
    public function lockForUpdate()
    {
        $this->lockInternal(self::LOCK_FOR_UPDATE);
    }

    /**
     * Lock for Share Mode
     */
    public function lockShareMode()
    {
        $this->lockInternal(self::LOCK_SHARE_MODE);
    }

    /**
     * Checks for the existence of the DB transaction
     */
    private function checkTransaction()
    {
        if ($this->owner->getDb()->getTransaction() === null)
            throw new \BadMethodCallException('Running transaction is required');
    }

    /**
     * @param string $param
     * @throws \yii\db\Exception
     */
    private function lockInternal($param)
    {
        $this->checkTransaction();
        /** @var ActiveRecord $model */
        $model = $this->owner;
        $pk = ArrayHelper::getValue($model::primaryKey(), 0);
        $model->getDb()->createCommand("SELECT 1 FROM {$model->tableName()} WHERE {$pk} = :pk {$param}", [
            ':pk' => $model->getPrimaryKey(),
        ])->execute();
    }
}
