<?php
/**
 * Part of the Dependencies package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Dependencies
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Mockery as m;
use Cartalyst\Dependencies\DependencySorter;

class DependencySorterTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testDependenciesCanBeAddedToSorter()
	{
		$sorter = new DependencySorter;
		$sorter->add('foo/bar');
		$sorter->add('baz/qux', array('foo/bar'));
		$sorter->add('fred/corge', array('baz/qux'));

		$expected = array(
			'foo/bar'    => array(),
			'baz/qux'    => array('foo/bar'),
			'fred/corge' => array('baz/qux'),
		);

		$this->assertEquals($sorter->items, $expected);
	}

	public function testDependenciesCanBeSorted()
	{
		$sorter = new DependencySorter;
		$sorter->add('baz/qux', array('foo/bar'));
		$sorter->add('fred/corge', array('baz/qux'));
		$sorter->add('foo/bar');

		$expected = array('foo/bar', 'baz/qux', 'fred/corge');

		// Because the order of our array matters, we'll implode it
		// and compare the two string match
		$this->assertEquals(implode('.', $expected), implode('.', $sorter->sort()));
	}

	/**
	 * @expectedException UnexpectedValueException
	 */
	public function testCircularDependenciesThrowAnException()
	{
		$sorter = new DependencySorter;
		$sorter->add('foo/bar', array('bar/foo'));
		$sorter->add('bar/foo', array('foo/bar'));
		$sorter->sort();
	}

	/**
	 * @expectedException UnexpectedValueException
	 */
	public function testSelfDependencyThrowsAnException()
	{
		$sorter = new DependencySorter;
		$sorter->add('foo/bar', array('foo/bar'));
		$sorter->sort();
	}

}
