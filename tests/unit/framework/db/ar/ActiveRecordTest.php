<?php

namespace yiiunit\framework\db\ar;

use yii\db\dao\Query;
use yii\db\ar\ActiveQuery;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\OrderItem;
use yiiunit\data\ar\Order;

class ActiveRecordTest extends \yiiunit\MysqlTestCase
{
	public function setUp()
	{
		ActiveRecord::$db = $this->getConnection();
	}

//	public function testFind()
//	{
//		// find one
//		$result = Customer::find();
//		$this->assertTrue($result instanceof ActiveQuery);
//		$customer = $result->one();
//		$this->assertTrue($customer instanceof Customer);
//
//		// find all
//		$result = Customer::find();
//		$customers = $result->all();
//		$this->assertEquals(3, count($customers));
//		$this->assertTrue($customers[0] instanceof Customer);
//		$this->assertTrue($customers[1] instanceof Customer);
//		$this->assertTrue($customers[2] instanceof Customer);
//
//		// find by a single primary key
//		$customer = Customer::find(2);
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		// find by attributes
//		$customer = Customer::find()->where(array('name' => 'user2'))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals(2, $customer->id);
//
//		// find by Query array
//		$query = array(
//			'where' => 'id=:id',
//			'params' => array(':id' => 2),
//		);
//		$customer = Customer::find($query)->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		// find count
//		$this->assertEquals(3, Customer::count()->value());
//		$this->assertEquals(2, Customer::count(array(
//			'where' => 'id=1 OR id=2',
//		))->value());
//		$this->assertEquals(2, Customer::find()->select('COUNT(*)')->where('id=1 OR id=2')->value());
//	}
//
//	public function testFindBySql()
//	{
//		// find one
//		$customer = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC')->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user3', $customer->name);
//
//		// find all
//		$customers = Customer::findBySql('SELECT * FROM tbl_customer')->all();
//		$this->assertEquals(3, count($customers));
//
//		// find with parameter binding
//		$customer = Customer::findBySql('SELECT * FROM tbl_customer WHERE id=:id', array(':id' => 2))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//	}
//
//	public function testScope()
//	{
//		$customers = Customer::find(array(
//			'scopes' => array('active'),
//		))->all();
//		$this->assertEquals(2, count($customers));
//
//		$customers = Customer::find()->active()->all();
//		$this->assertEquals(2, count($customers));
//	}
//
//	public function testFindLazy()
//	{
//		/** @var $customer Customer */
//		$customer = Customer::find(2);
//		$orders = $customer->orders;
//		$this->assertEquals(2, count($orders));
//
//		$orders = $customer->orders()->where('id=3')->all();
//		$this->assertEquals(1, count($orders));
//		$this->assertEquals(3, $orders[0]->id);
//	}
//
//	public function testFindEager()
//	{
//		$customers = Customer::find()->with('orders')->all();
//		$this->assertEquals(3, count($customers));
//		$this->assertEquals(1, count($customers[0]->orders));
//		$this->assertEquals(2, count($customers[1]->orders));
//	}
//
//	public function testFindLazyVia()
//	{
//		/** @var $order Order */
//		$order = Order::find(1);
//		$this->assertEquals(1, $order->id);
//		$this->assertEquals(2, count($order->items));
//		$this->assertEquals(1, $order->items[0]->id);
//		$this->assertEquals(2, $order->items[1]->id);
//
//		$order = Order::find(1);
//		$order->id = 100;
//		$this->assertEquals(array(), $order->items);
//	}

	public function testFindEagerVia()
	{
		$orders = Order::find()->with('items')->orderBy('id')->all();
		$this->assertEquals(3, count($orders));
		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);
	}


//	public function testInsert()
//	{
//		$customer = new Customer;
//		$customer->email = 'user4@example.com';
//		$customer->name = 'user4';
//		$customer->address = 'address4';
//
//		$this->assertNull($customer->id);
//		$this->assertTrue($customer->isNewRecord);
//
//		$customer->save();
//
//		$this->assertEquals(4, $customer->id);
//		$this->assertFalse($customer->isNewRecord);
//	}
//
//	public function testUpdate()
//	{
//		// save
//		$customer = Customer::find(2);
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//		$this->assertFalse($customer->isNewRecord);
//		$customer->name = 'user2x';
//		$customer->save();
//		$this->assertEquals('user2x', $customer->name);
//		$this->assertFalse($customer->isNewRecord);
//		$customer2 = Customer::find(2);
//		$this->assertEquals('user2x', $customer2->name);
//
//		// updateCounters
//		$pk = array('order_id' => 2, 'item_id' => 4);
//		$orderItem = OrderItem::find()->where($pk)->one();
//		$this->assertEquals(1, $orderItem->quantity);
//		$ret = $orderItem->updateCounters(array('quantity' => -1));
//		$this->assertTrue($ret);
//		$this->assertEquals(0, $orderItem->quantity);
//		$orderItem = OrderItem::find()->where($pk)->one();
//		$this->assertEquals(0, $orderItem->quantity);
//
//		// updateAll
//		$customer = Customer::find(3);
//		$this->assertEquals('user3', $customer->name);
//		$ret = Customer::updateAll(array(
//			'name' => 'temp',
//		), array('id' => 3));
//		$this->assertEquals(1, $ret);
//		$customer = Customer::find(3);
//		$this->assertEquals('temp', $customer->name);
//
//		// updateCounters
//		$pk = array('order_id' => 1, 'item_id' => 2);
//		$orderItem = OrderItem::find()->where($pk)->one();
//		$this->assertEquals(2, $orderItem->quantity);
//		$ret = OrderItem::updateAllCounters(array(
//			'quantity' => 3,
//		), $pk);
//		$this->assertEquals(1, $ret);
//		$orderItem = OrderItem::find()->where($pk)->one();
//		$this->assertEquals(5, $orderItem->quantity);
//	}
//
//	public function testDelete()
//	{
//		// delete
//		$customer = Customer::find(2);
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//		$customer->delete();
//		$customer = Customer::find(2);
//		$this->assertNull($customer);
//
//		// deleteAll
//		$customers = Customer::find()->all();
//		$this->assertEquals(2, count($customers));
//		$ret = Customer::deleteAll();
//		$this->assertEquals(2, $ret);
//		$customers = Customer::find()->all();
//		$this->assertEquals(0, count($customers));
//	}
//
//	public function testFind()
//	{
//		// find one
//		$result = Customer::find();
//		$this->assertTrue($result instanceof ActiveQuery);
//		$customer = $result->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals(1, $result->count);
//
//		// find all
//		$result = Customer::find();
//		$customers = $result->all();
//		$this->assertTrue(is_array($customers));
//		$this->assertEquals(3, count($customers));
//		$this->assertTrue($customers[0] instanceof Customer);
//		$this->assertTrue($customers[1] instanceof Customer);
//		$this->assertTrue($customers[2] instanceof Customer);
//		$this->assertEquals(3, $result->count);
//		$this->assertEquals(3, count($result));
//
//		// check count first
//		$result = Customer::find();
//		$this->assertEquals(3, $result->count);
//		$customer = $result->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals(3, $result->count);
//
//		// iterator
//		$result = Customer::find();
//		$count = 0;
//		foreach ($result as $customer) {
//			$this->assertTrue($customer instanceof Customer);
//			$count++;
//		}
//		$this->assertEquals($count, $result->count);
//
//		// array access
//		$result = Customer::find();
//		$this->assertTrue($result[0] instanceof Customer);
//		$this->assertTrue($result[1] instanceof Customer);
//		$this->assertTrue($result[2] instanceof Customer);
//
//		// find by a single primary key
//		$customer = Customer::find(2);
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		// find by attributes
//		$customer = Customer::find()->where(array('name' => 'user2'))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals(2, $customer->id);
//
//		// find by Query
//		$query = array(
//			'where' => 'id=:id',
//			'params' => array(':id' => 2),
//		);
//		$customer = Customer::find($query)->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		// find count
//		$this->assertEquals(3, Customer::find()->count());
//		$this->assertEquals(3, Customer::count());
//		$this->assertEquals(2, Customer::count(array(
//			'where' => 'id=1 OR id=2',
//		)));
//	}
//
//	public function testFindBySql()
//	{
//		// find one
//		$customer = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC')->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user3', $customer->name);
//
//		// find all
//		$customers = Customer::findBySql('SELECT * FROM tbl_customer')->all();
//		$this->assertEquals(3, count($customers));
//
//		// find with parameter binding
//		$customer = Customer::findBySql('SELECT * FROM tbl_customer WHERE id=:id', array(':id' => 2))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		// count
//		$query = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC');
//		$query->one();
//		$this->assertEquals(3, $query->count);
//		$query = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC');
//		$this->assertEquals(3, $query->count);
//	}
//
//	public function testQueryMethods()
//	{
//		$customer = Customer::find()->where('id=:id', array(':id' => 2))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		$customer = Customer::find()->where(array('name' => 'user3'))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user3', $customer->name);
//
//		$customer = Customer::find()->select('id')->orderBy('id DESC')->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals(3, $customer->id);
//		$this->assertEquals(null, $customer->name);
//
//		// scopes
//		$customers = Customer::find()->active()->all();
//		$this->assertEquals(2, count($customers));
//		$customers = Customer::find(array(
//			'scopes' => array('active'),
//		))->all();
//		$this->assertEquals(2, count($customers));
//
//		// asArray
//		$customers = Customer::find()->orderBy('id')->asArray()->all();
//		$this->assertEquals('user2', $customers[1]['name']);
//
//		// index
//		$customers = Customer::find()->orderBy('id')->index('name')->all();
//		$this->assertEquals(2, $customers['user2']['id']);
//	}
//
//	public function testEagerLoading()
//	{
//		// has many
//		$customers = Customer::find()->with('orders')->orderBy('@.id')->all();
//		$this->assertEquals(3, count($customers));
//		$this->assertEquals(1, count($customers[0]->orders));
//		$this->assertEquals(2, count($customers[1]->orders));
//		$this->assertEquals(0, count($customers[2]->orders));
//
//		// nested
//		$customers = Customer::find()->with('orders.customer')->orderBy('@.id')->all();
//		$this->assertEquals(3, count($customers));
//		$this->assertEquals(1, $customers[0]->orders[0]->customer->id);
//		$this->assertEquals(2, $customers[1]->orders[0]->customer->id);
//		$this->assertEquals(2, $customers[1]->orders[1]->customer->id);
//
//		// has many via relation
//		$orders = Order::find()->with('items')->orderBy('@.id')->all();
//		$this->assertEquals(3, count($orders));
//		$this->assertEquals(1, $orders[0]->items[0]->id);
//		$this->assertEquals(2, $orders[0]->items[1]->id);
//		$this->assertEquals(3, $orders[1]->items[0]->id);
//		$this->assertEquals(4, $orders[1]->items[1]->id);
//		$this->assertEquals(5, $orders[1]->items[2]->id);
//
//		// has many via join table
//		$orders = Order::find()->with('books')->orderBy('@.id')->all();
//		$this->assertEquals(2, count($orders));
//		$this->assertEquals(1, $orders[0]->books[0]->id);
//		$this->assertEquals(2, $orders[0]->books[1]->id);
//		$this->assertEquals(2, $orders[1]->books[0]->id);
//
//		// has many and base limited
//		$orders = Order::find()->with('items')->orderBy('@.id')->limit(2)->all();
//		$this->assertEquals(2, count($orders));
//		$this->assertEquals(1, $orders[0]->items[0]->id);
//
//		/// customize "with" query
//		$orders = Order::find()->with(array('items' => function($q) {
//			$q->orderBy('@.id DESC');
//		}))->orderBy('@.id')->limit(2)->all();
//		$this->assertEquals(2, count($orders));
//		$this->assertEquals(2, $orders[0]->items[0]->id);
//
//		// findBySql with
//		$orders = Order::findBySql('SELECT * FROM tbl_order WHERE customer_id=2')->with('items')->all();
//		$this->assertEquals(2, count($orders));
//
//		// index and array
//		$customers = Customer::find()->with('orders.customer')->orderBy('@.id')->index('id')->asArray()->all();
//		$this->assertEquals(3, count($customers));
//		$this->assertTrue(isset($customers[1], $customers[2], $customers[3]));
//		$this->assertTrue(is_array($customers[1]));
//		$this->assertEquals(1, count($customers[1]['orders']));
//		$this->assertEquals(2, count($customers[2]['orders']));
//		$this->assertEquals(0, count($customers[3]['orders']));
//		$this->assertTrue(is_array($customers[1]['orders'][0]['customer']));
//
//		// count with
//		$this->assertEquals(3, Order::count());
//		$value = Order::count(array(
//			'select' => array('COUNT(DISTINCT @.id, @.customer_id)'),
//			'with' => 'books',
//		));
//		$this->assertEquals(2, $value);
//
//	}
//
//	public function testLazyLoading()
//	{
//		// has one
//		$order = Order::find(3);
//		$this->assertTrue($order->customer instanceof Customer);
//		$this->assertEquals(2, $order->customer->id);
//
//		// has many
//		$customer = Customer::find(2);
//		$orders = $customer->orders;
//		$this->assertEquals(2, count($orders));
//		$this->assertEquals(2, $orders[0]->id);
//		$this->assertEquals(3, $orders[1]->id);
//
//		// has many via join table
//		$orders = Order::find()->orderBy('@.id')->all();
//		$this->assertEquals(3, count($orders));
//		$this->assertEquals(2, count($orders[0]->books));
//		$this->assertEquals(1, $orders[0]->books[0]->id);
//		$this->assertEquals(2, $orders[0]->books[1]->id);
//		$this->assertEquals(array(), $orders[1]->books);
//		$this->assertEquals(1, count($orders[2]->books));
//		$this->assertEquals(2, $orders[2]->books[0]->id);
//
//		// customized relation query
//		$customer = Customer::find(2);
//		$orders = $customer->orders(array(
//			'where' => '@.id = 3',
//		));
//		$this->assertEquals(1, count($orders));
//		$this->assertEquals(3, $orders[0]->id);
//
//		// original results are kept after customized query
//		$orders = $customer->orders;
//		$this->assertEquals(2, count($orders));
//		$this->assertEquals(2, $orders[0]->id);
//		$this->assertEquals(3, $orders[1]->id);
//
//		// as array
//		$orders = $customer->orders(array(
//			'asArray' => true,
//		));
//		$this->assertEquals(2, count($orders));
//		$this->assertTrue(is_array($orders[0]));
//		$this->assertEquals(2, $orders[0]['id']);
//		$this->assertEquals(3, $orders[1]['id']);
//
//		// using anonymous function to customize query condition
//		$orders = $customer->orders(function($q) {
//			$q->orderBy('@.id DESC')->asArray();
//		});
//		$this->assertEquals(2, count($orders));
//		$this->assertTrue(is_array($orders[0]));
//		$this->assertEquals(3, $orders[0]['id']);
//		$this->assertEquals(2, $orders[1]['id']);
//	}
}