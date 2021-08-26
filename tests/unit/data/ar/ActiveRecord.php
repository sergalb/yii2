<?php
/**
 * ActiveRecord class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

use yii\db\Connection;

/**
 * ActiveRecord is ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
	public static $db;

	public static function getDbConnection()
	{
		return self::$db;
	}
}