<?php

namespace yiiunit\framework\db\dao;

use yii\db\dao\Connection;

class ConnectionTest extends \yiiunit\MysqlTestCase
{
	function testConstruct()
	{
		$connection = $this->getConnection(false);
		$params = $this->getParam('mysql');

		$this->assertEquals($params['dsn'], $connection->dsn);
		$this->assertEquals($params['username'], $connection->username);
		$this->assertEquals($params['password'], $connection->password);
	}

	function testOpenClose()
	{
		$connection = $this->getConnection(false);

		$this->assertFalse($connection->active);
		$this->assertEquals(null, $connection->pdo);

		$connection->open();
		$this->assertTrue($connection->active);
		$this->assertTrue($connection->pdo instanceof \PDO);

		$connection->close();
		$this->assertFalse($connection->active);
		$this->assertEquals(null, $connection->pdo);

		$connection = new Connection;
		$connection->dsn = 'unknown::memory:';
		$this->setExpectedException('yii\db\Exception');
		$connection->open();
	}

	function testGetDriverName()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals('mysql', $connection->driverName);
		$this->assertFalse($connection->active);
	}

	function testQuoteValue()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals(123, $connection->quoteValue(123));
		$this->assertEquals("'string'", $connection->quoteValue('string'));
		$this->assertEquals("'It\'s interesting'", $connection->quoteValue("It's interesting"));
	}

	function testQuoteTableName()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals('`table`', $connection->quoteTableName('table'));
		$this->assertEquals('`table`', $connection->quoteTableName('`table`'));
		$this->assertEquals('`schema`.`table`', $connection->quoteTableName('schema.table'));
		$this->assertEquals('`schema.table`', $connection->quoteTableName('schema.table', true));
	}

	function testQuoteColumnName()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals('`column`', $connection->quoteColumnName('column'));
		$this->assertEquals('`column`', $connection->quoteColumnName('`column`'));
		$this->assertEquals('`table`.`column`', $connection->quoteColumnName('table.column'));
		$this->assertEquals('`table.column`', $connection->quoteColumnName('table.column', true));
	}

	function testGetPdoType()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals(\PDO::PARAM_BOOL, $connection->getPdoType('boolean'));
		$this->assertEquals(\PDO::PARAM_INT, $connection->getPdoType('integer'));
		$this->assertEquals(\PDO::PARAM_STR, $connection->getPdoType('string'));
		$this->assertEquals(\PDO::PARAM_NULL, $connection->getPdoType('NULL'));
	}

	function testAttribute()
	{

	}
}
