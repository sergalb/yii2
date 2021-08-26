<?php
/**
 * UniqueValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * CUniqueValidator validates that the attribute value is unique in the corresponding database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UniqueValidator extends Validator
{
	/**
	 * @var boolean whether the comparison is case sensitive. Defaults to true.
	 * Note, by setting it to false, you are assuming the attribute type is string.
	 */
	public $caseSensitive = true;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;
	/**
	 * @var string the yii\db\ar\ActiveRecord class name or alias of the class
	 * that should be used to look for the attribute value being validated.
	 * Defaults to null, meaning using the class of the object currently
	 * being validated.
	 * @see attributeName
	 */
	public $className;
	/**
	 * @var string the ActiveRecord class attribute name that should be
	 * used to look for the attribute value being validated. Defaults to null,
	 * meaning using the name of the attribute being validated.
	 * @see className
	 */
	public $attributeName;
	/**
	 * @var \yii\db\ar\ActiveQuery additional query criteria. This will be
	 * combined with the condition that checks if the attribute value exists
	 * in the corresponding table column.
	 */
	public $query = null;
	/**
	 * @var string the user-defined error message. The placeholders "{attribute}" and "{value}"
	 * are recognized, which will be replaced with the actual attribute name and value, respectively.
	 */
	public $message;
	/**
	 * @var boolean whether this validation rule should be skipped if when there is already a validation
	 * error for the current attribute. Defaults to true.
	 */
	public $skipOnError = true;


	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yiiunit\data\ar\ActiveRecord $object the object being validated
	 * @param string $attribute the attribute being validated
	 *
	 * @throws \yii\base\Exception if table doesn't have column specified
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if ($this->allowEmpty && $this->isEmpty($value)) {
			return;
		}

		$className = ($this->className === null) ? get_class($object) : \Yii::import($this->className);
		$attributeName = ($this->attributeName === null) ? $attribute : $this->attributeName;

		$table = $object::getMetaData()->table;
		if (($column = $table->getColumn($attributeName)) === null) {
			throw new \yii\base\Exception('Table "' . $table->name . '" does not have a column named "' . $attributeName . '"');
		}

		$finder = $object::find();
		$finder->where($this->caseSensitive ? "{$column->quotedName}=:value" : "LOWER({$column->quotedName})=LOWER(:value)");
		$finder->params(array(':value' => $value));

		if ($this->query instanceof \yii\db\dao\BaseQuery) {
			$finder->mergeWith($this->query);
		}

		if ($object->getIsNewRecord()) {
			// if current $object isn't in the database yet then it's OK just
			// to call exists()
			$exists = $finder->exists();
		} else {
			// if current $object is in the database already we can't use exists()
			$finder->limit(2);
			$objects = $finder->all();

			$n = count($objects);
			if ($n === 1) {
				if ($column->isPrimaryKey) {
					// primary key is modified and not unique
					$exists = $object->getOldPrimaryKey() != $object->getPrimaryKey();
				} else {
					// non-primary key, need to exclude the current record based on PK
					$exists = array_shift($objects)->getPrimaryKey() != $object->getOldPrimaryKey();
				}
			} else {
				$exists = $n > 1;
			}
		}

		if ($exists) {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii', '{attribute} "{value}" has already been taken.');
			$this->addError($object, $attribute, $message, array('{value}' => $value));
		}
	}
}