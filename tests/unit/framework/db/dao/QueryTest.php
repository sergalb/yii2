<?php

namespace yiiunit\framework\db\dao;

use yii\db\dao\Connection;
use yii\db\dao\Command;
use yii\db\dao\Query;
use yii\db\dao\DataReader;

class QueryTest extends \yiiunit\MysqlTestCase
{
	function testSelect()
	{
		// default
		$query = new Query;
		$query->select('*');
		$this->assertEquals('*', $query->select);
		$this->assertNull($query->distinct);
		$this->assertEquals(null, $query->selectOption);

		$query = new Query;
		$query->select('id, name', 'something')->distinct(true);
		$this->assertEquals('id, name', $query->select);
		$this->assertTrue($query->distinct);
		$this->assertEquals('something', $query->selectOption);
	}

	function testFrom()
	{
		$query = new Query;
		$query->from('tbl_user');
		$this->assertEquals('tbl_user', $query->from);
	}

	function testWhere()
	{
		$query = new Query;
		$query->where('id = :id', array(':id' => 1));
		$this->assertEquals('id = :id', $query->where);
		$this->assertEquals(array(':id' => 1), $query->params);

		$query->andWhere('name = :name', array(':name' => 'something'));
		$this->assertEquals(array('and', 'id = :id', 'name = :name'), $query->where);
		$this->assertEquals(array(':id' => 1, ':name' => 'something'), $query->params);

		$query->orWhere('age = :age', array(':age' => '30'));
		$this->assertEquals(array('or', array('and', 'id = :id', 'name = :name'), 'age = :age'), $query->where);
		$this->assertEquals(array(':id' => 1, ':name' => 'something', ':age' => '30'), $query->params);
	}

	function testJoin()
	{

	}

	function testGroup()
	{
		$query = new Query;
		$query->groupBy('team');
		$this->assertEquals('team', $query->groupBy);

		$query->addGroup('company');
		$this->assertEquals(array('team', 'company'), $query->groupBy);

		$query->addGroup('age');
		$this->assertEquals(array('team', 'company', 'age'), $query->groupBy);
	}

	function testHaving()
	{
		$query = new Query;
		$query->having('id = :id', array(':id' => 1));
		$this->assertEquals('id = :id', $query->having);
		$this->assertEquals(array(':id' => 1), $query->params);

		$query->andHaving('name = :name', array(':name' => 'something'));
		$this->assertEquals(array('and', 'id = :id', 'name = :name'), $query->having);
		$this->assertEquals(array(':id' => 1, ':name' => 'something'), $query->params);

		$query->orHaving('age = :age', array(':age' => '30'));
		$this->assertEquals(array('or', array('and', 'id = :id', 'name = :name'), 'age = :age'), $query->having);
		$this->assertEquals(array(':id' => 1, ':name' => 'something', ':age' => '30'), $query->params);
	}

	function testOrder()
	{
		$query = new Query;
		$query->orderBy('team');
		$this->assertEquals('team', $query->orderBy);

		$query->addOrderBy('company');
		$this->assertEquals(array('team', 'company'), $query->orderBy);

		$query->addOrderBy('age');
		$this->assertEquals(array('team', 'company', 'age'), $query->orderBy);
	}

	function testLimitOffset()
	{
		$query = new Query;
		$query->limit(10)->offset(5);
		$this->assertEquals(10, $query->limit);
		$this->assertEquals(5, $query->offset);
	}

	function testUnion()
	{

	}

	function testInsert()
	{

	}

	function testUpdate()
	{

	}

	function testDelete()
	{

	}

	function testCreateTable()
	{

	}

	function testRenameTable()
	{

	}

	function testDropTable()
	{

	}

	function testTruncateTable()
	{

	}

	function testAddColumn()
	{

	}

	function testDropColumn()
	{

	}

	function testRenameColumn()
	{

	}

	function testAlterColumn()
	{

	}

	function testAddForeignKey()
	{

	}

	function testDropForeignKey()
	{

	}

	function testCreateIndex()
	{

	}

	function testDropIndex()
	{

	}

	function testParams()
	{

	}

	function testGetSql()
	{

	}

	function testCreateCommand()
	{

	}

	function testReset()
	{

	}

	function testToArray()
	{

	}

	function testMergeWith()
	{

	}
}