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
use yii\db\Expression;

/**
 * Class LockableActiveRecord
 * @package yiidreamteam\behaviors
 *
 * @property ActiveRecord $owner
 */
class LockableActiveRecord extends Behavior
{
    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        if (!$owner instanceof ActiveRecord) {
            throw new InvalidConfigException('Please attach this behavior to the instance of the ' . ActiveRecord::className());
        }
        parent::attach($owner);
    }

    /**
     * @param string $mode
     * @throws \yii\db\Exception
     * @link https://dev.mysql.com/doc/refman/5.7/en/innodb-locking-reads.html
     * @link https://www.postgresql.org/docs/9.4/static/explicit-locking.html
     */
    public function lock($mode = 'FOR UPDATE')
    {
        $this->ensureTransaction();
        /** @var ActiveRecord $model */
        $model = $this->owner;

        $pk = $model->getPrimaryKey(true);
        $sql = $model::find()->select(new Expression('1'))->andWhere($pk)->createCommand()->getRawSql() . ' ' . $mode;

        $model->getDb()->createCommand($sql)->execute();
    }

    /**
     * Checks for the existence of the DB transaction
     */
    private function ensureTransaction()
    {
        if ($this->owner->getDb()->getTransaction() === null) {
            throw new \BadMethodCallException('Active transaction is required');
        }
    }

    /**
     * Lock for update.
     *
     * @deprecated Use {@see lock()} directly
     */
    public function lockForUpdate()
    {
        $this->lock();
    }

    /**
     * Lock in share mode.
     * Warning: MySQL only.
     *
     * @deprecated Use {@see lock()} directly
     */
    public function lockShareMode()
    {
        $this->lock('LOCK IN SHARE MODE');
    }
}
