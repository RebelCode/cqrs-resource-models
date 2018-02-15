<?php

namespace RebelCode\Storage\Resource\FuncTest;

use Dhii\Data\Container\Exception\NotFoundException;
use PDO;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Pdo\PdoInsertResourceModel as TestSubject;
use RebelCode\Storage\Resource\TestStub\BaseDatabaseTestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class PdoInsertResourceModelTest extends BaseDatabaseTestCase
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getDatabaseSchema()
    {
        return [
            'users'           => [
                'id'        => ['type' => 'integer'],
                'user_name' => ['type' => 'text'],
                'user_age'  => ['type' => 'integer'],
            ],
            'linked_accounts' => [
                'id_1' => ['type' => 'integer'],
                'id_2' => ['type' => 'integer'],
            ],
            'comments'        => [
                'comment_id' => ['type' => 'integer'],
                'user_id'    => ['type' => 'integer'],
                'comment'    => ['type' => 'text'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getDataSet()
    {
        return $this->createArrayDataSet([]);
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string $className      Name of the class for the mock to extend.
     * @param array  $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The mock builder for an object that extends and implements the specified class and interfaces
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf(
            'abstract class %1$s extends %2$s implements %3$s {}',
            [
                $paddingClassName,
                $className,
                implode(', ', $interfaceNames),
            ]
        );
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a new mock container instance.
     *
     * @since [*next-version*]
     *
     * @param array $data The container data.
     *
     * @return MockObject
     */
    public function createContainer($data = [])
    {
        $mock = $this->getMockBuilder('Psr\Container\ContainerInterface')
                     ->setMethods(['get', 'has'])
                     ->getMockForAbstractClass();

        $mock->method('get')->willReturnCallback(
            function($k) use ($data, $mock) {
                if (isset($data[$k])) {
                    return $data[$k];
                }
                throw new NotFoundException(null, null, null, $mock, $k);
            }
        );

        $mock->method('has')->willReturnCallback(
            function($k) use ($data) {
                return isset($data[$k]);
            }
        );

        return $mock;
    }

    /**
     * Creates a new ArrayAccess mock instance.
     *
     * @since [*next-version*]
     *
     * @param array $data The array access data.
     *
     * @return MockObject
     */
    public function createArrayAccess($data = [])
    {
        $mock = $this->getMockBuilder('ArrayAccess')
                     ->setMethods(['offsetGet', 'offsetExists', 'offsetSet', 'offsetUnset'])
                     ->getMockForAbstractClass();

        $mock->method('offsetGet')->willReturnCallback(
            function($k) use ($data, $mock) {
                return (isset($data[$k]))
                    ? $data[$k]
                    : null;
            }
        );

        $mock->method('offsetExists')->willReturnCallback(
            function($k) use ($data) {
                return isset($data[$k]);
            }
        );

        return $mock;
    }

    /**
     * Creates a mock iterator instance.
     *
     * @since [*next-version*]
     *
     * @param array $data The data to iterate over.
     *
     * @return MockObject
     */
    public function createIterator($data = [])
    {
        $mock = $this->getMockBuilder('Iterator')
                     ->setMethods(['rewind', 'current', 'key', 'next', 'valid'])
                     ->getMockForAbstractClass();

        $mock->method('rewind')->willReturnCallback(
            function() use (&$data) {
                return reset($data);
            }
        );
        $mock->method('current')->willReturnCallback(
            function() use (&$data) {
                return current($data);
            }
        );
        $mock->method('key')->willReturnCallback(
            function() use (&$data) {
                return key($data);
            }
        );
        $mock->method('next')->willReturnCallback(
            function() use (&$data) {
                return next($data);
            }
        );
        $mock->method('valid')->willReturnCallback(
            function() use (&$data) {
                return key($data) !== null;
            }
        );

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $table = uniqid('table-');
        $fcMap = [
            uniqid('field-') => uniqid('column-'),
            uniqid('field-') => uniqid('column-'),
            uniqid('field-') => uniqid('column-'),
        ];
        $subject = new TestSubject($this->_getPdo(), $table, $fcMap);

        $this->assertInstanceOf(
            'Dhii\Storage\Resource\InsertCapableInterface',
            $subject,
            'Test subject does not implement expected interface'
        );
    }

    /**
     * Tests the insert method with array records.
     *
     * @since [*next-version*]
     */
    public function testInsertArrays()
    {
        $table = 'users';
        $fcMap = [
            'id'   => 'id',
            'name' => 'user_name',
            'age'  => 'user_age',
        ];
        $subject = new TestSubject($this->_getPdo(), $table, $fcMap);

        $records = [
            $record1 = [
                'id'   => 2,
                'name' => 'foo',
                'age'  => 28,
            ],
        ];

        $subject->insert($records);

        $columns = array_values($fcMap);
        $expected = [
            array_combine($columns, array_values($record1)),
        ];

        $actual = $this->_getPdo()->query('SELECT * FROM `users`;')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals(
            $expected,
            $actual,
            'Expected and actual table data after INSERT do not match.'
        );
    }

    /**
     * Tests the insert method with container records.
     *
     * @since [*next-version*]
     */
    public function testInsertContainer()
    {
        $table = 'users';
        $fcMap = [
            'id'   => 'id',
            'name' => 'user_name',
            'age'  => 'user_age',
        ];
        $subject = new TestSubject($this->_getPdo(), $table, $fcMap);

        $records = [
            $this->createContainer(
                [
                    'id'   => $id = 8,
                    'name' => $name = 'bar',
                    'age'  => $age = 11,
                ]
            ),
        ];

        $subject->insert($records);

        $columns = array_values($fcMap);
        $expected = [
            array_combine($columns, [$id, $name, $age]),
        ];

        $actual = $this->_getPdo()->query('SELECT * FROM `users`;')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals(
            $expected,
            $actual,
            'Expected and actual table data after INSERT do not match.'
        );
    }

    /**
     * Tests the insert method with {@see \stdClass} records.
     *
     * @since [*next-version*]
     */
    public function testInsertStdClass()
    {
        $table = 'users';
        $fcMap = [
            'id'   => 'id',
            'name' => 'user_name',
            'age'  => 'user_age',
        ];
        $subject = new TestSubject($this->_getPdo(), $table, $fcMap);

        $records = [
            (object) (
            [
                'id'   => $id = 8,
                'name' => $name = 'bar',
                'age'  => $age = 11,
            ]
            ),
        ];

        $subject->insert($records);

        $columns = array_values($fcMap);
        $expected = [
            array_combine($columns, [$id, $name, $age]),
        ];

        $actual = $this->_getPdo()->query('SELECT * FROM `users`;')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals(
            $expected,
            $actual,
            'Expected and actual table data after INSERT do not match.'
        );
    }

    /**
     * Tests the insert method with {@see \ArrayAccess} records.
     *
     * @since [*next-version*]
     */
    public function testInsertArrayAccess()
    {
        $table = 'users';
        $fcMap = [
            'id'   => 'id',
            'name' => 'user_name',
            'age'  => 'user_age',
        ];
        $subject = new TestSubject($this->_getPdo(), $table, $fcMap);

        $records = [
            $this->createArrayAccess(
                [
                    'id'   => $id = 8,
                    'name' => $name = 'bar',
                    'age'  => $age = 11,
                ]
            ),
        ];

        $subject->insert($records);

        $columns = array_values($fcMap);
        $expected = [
            array_combine($columns, [$id, $name, $age]),
        ];

        $actual = $this->_getPdo()->query('SELECT * FROM `users`;')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals(
            $expected,
            $actual,
            'Expected and actual table data after INSERT do not match.'
        );
    }

    /**
     * Tests the insert method with an iterator of records.
     *
     * @since [*next-version*]
     */
    public function testInsertIterator()
    {
        $table = 'users';
        $fcMap = [
            'id'   => 'id',
            'name' => 'user_name',
            'age'  => 'user_age',
        ];
        $subject = new TestSubject($this->_getPdo(), $table, $fcMap);

        $records = $this->createIterator(
            [
                $this->createContainer(
                    [
                        'id'   => $id = 8,
                        'name' => $name = 'bar',
                        'age'  => $age = 11,
                    ]
                ),
            ]
        );

        $subject->insert($records);

        $columns = array_values($fcMap);
        $expected = [
            array_combine($columns, [$id, $name, $age]),
        ];

        $actual = $this->_getPdo()->query('SELECT * FROM `users`;')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals(
            $expected,
            $actual,
            'Expected and actual table data after INSERT do not match.'
        );
    }
}
