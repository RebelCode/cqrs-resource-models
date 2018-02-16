<?php

namespace RebelCode\Storage\Resource\FuncTest\Pdo;

use Dhii\Util\String\StringableInterface as Stringable;
use PDO;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Pdo\PdoDeleteResourceModel as TestSubject;
use RebelCode\Storage\Resource\TestStub\BaseDatabaseTestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class PdoDeleteResourceModelTest extends BaseDatabaseTestCase
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
        return $this->createArrayDataSet(
            [
                'users'           => [
                    [
                        'id'        => 5,
                        'user_name' => 'foo',
                        'user_age'  => 24,
                    ],
                    [
                        'id'        => 11,
                        'user_name' => 'bar',
                        'user_age'  => 30,
                    ],
                    [
                        'id'        => 12,
                        'user_name' => 'lorem',
                        'user_age'  => 19,
                    ],
                ],
                'linked_accounts' => [
                    [
                        'id_1' => 11,
                        'id_2' => 12,
                    ],
                    [
                        'id_1' => 5,
                        'id_2' => 11,
                    ],
                ],
                'comments'        => [
                    [
                        'comment_id' => 84,
                        'user_id'    => 11,
                        'comment'    => 'Hello world!',
                    ],
                    [
                        'comment_id' => 99,
                        'user_id'    => 5,
                        'comment'    => 'Lorem ipsum',
                    ],
                    [
                        'comment_id' => 111,
                        'user_id'    => 12,
                        'comment'    => 'Foobar',
                    ],
                ],
            ]
        );
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
     * Creates a new template mock instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject The created mock template instance.
     */
    protected function createTemplate()
    {
        return $this->getMockBuilder('Dhii\Output\TemplateInterface')
                    ->setMethods(['render'])
                    ->getMockForAbstractClass();
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type    The expression type.
     * @param array  $terms   The expression terms.
     * @param bool   $negated Optional negation flag.
     *
     * @return MockObject The created expression instance.
     */
    public function createLogicalExpression($type, $terms, $negated = false)
    {
        $mock = $this->getMockBuilder('Dhii\Expression\LogicalExpressionInterface')
                     ->setMethods(['getTerms', 'getType', 'isNegated'])
                     ->getMockForAbstractClass();

        $mock->method('getType')->willReturn($type);
        $mock->method('getTerms')->willReturn($terms);
        $mock->method('isNegated')->willReturn($negated);

        return $mock;
    }

    /**
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $entity The entity name.
     * @param string|Stringable $field  The field name.
     * @param string|Stringable $type   The term type.
     *
     * @return MockObject The created entity field term instance.
     */
    public function createEntityFieldTerm($entity, $field, $type = '')
    {
        $builder = $this->mockClassAndInterfaces(
            'stdClass',
            [
                'Dhii\Expression\TermInterface',
                'Dhii\Storage\Resource\Sql\EntityFieldInterface',
            ]
        );
        $builder->setMethods(['getEntity', 'getField', 'getType']);

        $mock = $builder->getMockForAbstractClass();

        $mock->method('getEntity')->willReturn($entity);
        $mock->method('getField')->willReturn($field);
        $mock->method('getType')->willReturn($type);

        return $mock;
    }

    /**
     * Creates a literal term mock instance.
     *
     * @since [*next-version*]
     *
     * @param mixed $value The value.
     *
     * @return MockObject The created literal term mock instance.
     */
    public function createLiteralTerm($value)
    {
        $mock = $this->getMockBuilder('Dhii\Expression\LiteralTermInterface')
                     ->setMethods(['getValue'])
                     ->getMockForAbstractClass();

        $mock->method('getValue')->willReturn($value);

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $pdo = $this->_getPdo();
        $template = $this->createTemplate();
        $table = uniqid('table-');
        $fcMap = [
            'id'   => 'id',
            'name' => 'user_name',
            'age'  => 'user_age',
        ];
        $subject = new TestSubject($pdo, $template, $table, $fcMap);

        $this->assertInstanceOf(
            'Dhii\Storage\Resource\DeleteCapableInterface',
            $subject,
            'Test subject does not implement expected interface.'
        );
    }

    /**
     * Tests the DELETE functionality with a condition to assert whether the records in the database that satisfy it are
     * actually deleted.
     *
     * @since [*next-version*]
     */
    public function testDeleteWithCondition()
    {
        $pdo = $this->_getPdo();
        $table = 'users';
        $fcMap = [
            'id'   => 'id',
            'name' => 'user_name',
            'age'  => 'user_age',
        ];
        $template = $this->createTemplate();
        $subject = new TestSubject($this->_getPdo(), $template, $table, $fcMap);

        $condition = $this->createLogicalExpression(
            'greater_equal_to',
            [
                $this->createEntityFieldTerm('users', 'age'),
                $this->createLiteralTerm(20),
            ]
        );
        $pdoHash = hash('crc32b', 20);
        $template->expects($this->atLeastOnce())
                 ->method('render')
                 ->with($this->contains($condition))
                 ->willReturn('`users`.`user_age` > :' . $pdoHash);

        $subject->delete($condition);

        $results = $pdo->query('SELECT * FROM `users` WHERE `user_age` >= 20')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEmpty($results, 'Not all records where deleted.');
    }

    /**
     * Tests the DELETE functionality without a condition to assert whether all the records in the target table
     * are actually deleted.
     *
     * @since [*next-version*]
     */
    public function testDeleteNoCondition()
    {
        $pdo = $this->_getPdo();
        $table = 'comments';
        $fcMap = [
            'id'   => 'comment_id',
            'name' => 'user_id',
            'text' => 'comment',
        ];
        $template = $this->createTemplate();
        $subject = new TestSubject($this->_getPdo(), $template, $table, $fcMap);

        $condition = null;

        $subject->delete($condition);

        $results = $pdo->query('SELECT * FROM `comments`')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEmpty($results, 'Not all records where deleted.');
    }
}
