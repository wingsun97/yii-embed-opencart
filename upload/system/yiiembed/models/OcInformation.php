<?php
/**
 * OcInformation
 *
 * --- BEGIN ModelDoc ---
 *
 * Table {{information}}
 * @property integer $information_id
 * @property integer $bottom
 * @property integer $sort_order
 * @property integer $status
 *
 * Relations
 * @property \OcLanguage[] $languages
 * @property \OcStore[] $stores
 *
 * @see \CActiveRecord
 * @method \OcInformation find($condition = '', array $params = array())
 * @method \OcInformation findByPk($pk, $condition = '', array $params = array())
 * @method \OcInformation findByAttributes(array $attributes, $condition = '', array $params = array())
 * @method \OcInformation findBySql($sql, array $params = array())
 * @method \OcInformation[] findAll($condition = '', array $params = array())
 * @method \OcInformation[] findAllByPk($pk, $condition = '', array $params = array())
 * @method \OcInformation[] findAllByAttributes(array $attributes, $condition = '', array $params = array())
 * @method \OcInformation[] findAllBySql($sql, array $params = array())
 * @method \OcInformation with()
 * @method \OcInformation together()
 * @method \OcInformation cache($duration, $dependency = null, $queryCount = 1)
 * @method \OcInformation resetScope($resetDefault = true)
 * @method \OcInformation populateRecord($attributes, $callAfterFind = true)
 * @method \OcInformation[] populateRecords($data, $callAfterFind = true, $index = null)
 *
 * --- END ModelDoc ---
 */

class OcInformation extends CActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return OcInformation the static model class
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
        return '{{information}}';
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'languages' => array(self::MANY_MANY, 'OcLanguage', '{{information_description}}(information_id, language_id)'),
            'stores' => array(self::MANY_MANY, 'OcStore', '{{information_to_store}}(information_id, store_id)'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'information_id' => Yii::t('app', 'Information'),
            'bottom' => Yii::t('app', 'Bottom'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'status' => Yii::t('app', 'Status'),
        );
    }

}

