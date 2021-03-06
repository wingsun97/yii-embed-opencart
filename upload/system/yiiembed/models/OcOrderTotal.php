<?php
/**
 * OcOrderTotal
 *
 * --- BEGIN ModelDoc ---
 *
 * Table {{order_total}}
 * @property integer $order_total_id
 * @property integer $order_id
 * @property string $code
 * @property string $title
 * @property string $text
 * @property number $value
 * @property integer $sort_order
 *
 * Relations
 * @property \OcOrder $order
 *
 * @see \CActiveRecord
 * @method \OcOrderTotal find($condition = '', array $params = array())
 * @method \OcOrderTotal findByPk($pk, $condition = '', array $params = array())
 * @method \OcOrderTotal findByAttributes(array $attributes, $condition = '', array $params = array())
 * @method \OcOrderTotal findBySql($sql, array $params = array())
 * @method \OcOrderTotal[] findAll($condition = '', array $params = array())
 * @method \OcOrderTotal[] findAllByPk($pk, $condition = '', array $params = array())
 * @method \OcOrderTotal[] findAllByAttributes(array $attributes, $condition = '', array $params = array())
 * @method \OcOrderTotal[] findAllBySql($sql, array $params = array())
 * @method \OcOrderTotal with()
 * @method \OcOrderTotal together()
 * @method \OcOrderTotal cache($duration, $dependency = null, $queryCount = 1)
 * @method \OcOrderTotal resetScope($resetDefault = true)
 * @method \OcOrderTotal populateRecord($attributes, $callAfterFind = true)
 * @method \OcOrderTotal[] populateRecords($data, $callAfterFind = true, $index = null)
 *
 * --- END ModelDoc ---
 */

class OcOrderTotal extends CActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return OcOrderTotal the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{order_total}}';
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'order' => array(self::BELONGS_TO, 'OcOrder', 'order_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'order_total_id' => Yii::t('app', 'Order Total'),
            'order_id' => Yii::t('app', 'Order'),
            'code' => Yii::t('app', 'Code'),
            'title' => Yii::t('app', 'Title'),
            'text' => Yii::t('app', 'Text'),
            'value' => Yii::t('app', 'Value'),
            'sort_order' => Yii::t('app', 'Sort Order'),
        );
    }

}

