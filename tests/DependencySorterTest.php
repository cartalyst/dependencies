<?php

/*
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

namespace Cartalyst\Dependencies\Tests;

use UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use Cartalyst\Dependencies\DependencySorter;
use Cartalyst\Dependencies\DependentInterface;

class DependencySorterTest extends TestCase
{
    /** @test */
    public function dependencies_can_be_added_to_the_sorter_through_the_constructor()
    {
        $sorter = new DependencySorter([
            'foo/bar'    => [],
            'baz/qux'    => ['foo/bar'],
            'fred/corge' => ['baz/qux'],
        ]);

        $expected = [
            'foo/bar'    => [],
            'baz/qux'    => ['foo/bar'],
            'fred/corge' => ['baz/qux'],
        ];

        $this->assertSame($expected, $sorter->getItems());
    }

    /** @test */
    public function dependencies_can_be_added_to_the_sorter_through_the_setter()
    {
        $sorter = new DependencySorter();

        $sorter->add('foo/bar');
        $sorter->add('baz/qux', ['foo/bar']);
        $sorter->add('fred/corge', ['baz/qux']);

        $expected = [
            'foo/bar'    => [],
            'baz/qux'    => ['foo/bar'],
            'fred/corge' => ['baz/qux'],
        ];

        $this->assertSame($expected, $sorter->getItems());
    }

    /** @test */
    public function dependencies_can_be_sorted()
    {
        $sorter = new DependencySorter();

        $sorter->add('baz/qux', ['foo/bar']);
        $sorter->add('fred/corge', 'baz/qux'); // Test string dependencies
        $sorter->add('foo/bar');

        $sorted = $sorter->sort();

        $this->assertSame('foo/bar', $sorted[0]);
        $this->assertSame('baz/qux', $sorted[1]);
        $this->assertSame('fred/corge', $sorted[2]);
    }

    /** @test */
    public function it_can_add_depedent_instances()
    {
        $sorter = new DependencySorter();

        $dep1 = new class() implements DependentInterface {
            public function getSlug(): string
            {
                return 'baz/qux';
            }

            public function getDependencies(): array
            {
                return [
                    'foo/bar',
                ];
            }
        };

        $dep2 = new class() implements DependentInterface {
            public function getSlug(): string
            {
                return 'fred/corge';
            }

            public function getDependencies(): array
            {
                return [
                    'foo/bar',
                ];
            }
        };

        $dep3 = new class() implements DependentInterface {
            public function getSlug(): string
            {
                return 'foo/bar';
            }

            public function getDependencies(): array
            {
                return [];
            }
        };

        $sorter->add($dep1);
        $sorter->add($dep2);
        $sorter->add($dep3);

        $sorted     = $sorter->sort();
        $dependents = array_keys($sorter->getDependents());

        $this->assertCount(3, $sorted);

        $this->assertSame($dep1->getSlug(), $dependents[0]);
        $this->assertSame($dep2->getSlug(), $dependents[1]);
        $this->assertSame($dep3->getSlug(), $dependents[2]);

        $this->assertSame($dep3, $sorted[0]);
        $this->assertSame($dep1, $sorted[1]);
        $this->assertSame($dep2, $sorted[2]);
    }

    /** @test */
    public function an_exception_will_be_thrown_when_a_dependency_has_a_circular_dependency()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Item [foo/bar] and [bar/foo] have a circular dependency.');

        $sorter = new DependencySorter();

        $sorter->add('foo/bar', ['bar/foo']);
        $sorter->add('bar/foo', ['foo/bar']);

        $sorter->sort();
    }

    /** @test */
    public function an_exception_will_be_thrown_when_a_dependency_dependends_on_itself()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Item [foo/bar] is dependent on itself.');

        $sorter = new DependencySorter();

        $sorter->add('foo/bar', ['foo/bar']);

        $sorter->sort();
    }
}
