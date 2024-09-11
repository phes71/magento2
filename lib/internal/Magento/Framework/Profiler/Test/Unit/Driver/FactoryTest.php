<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Test class for \Magento\Framework\Profiler\Driver\Factory
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver;

use Magento\Framework\Profiler\Driver\Factory;
use Magento\Framework\Profiler\DriverInterface;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $_factory;

    /**
     * @var string
     */
    protected $_defaultDriverPrefix = 'Magento_Framework_Profiler_Driver_Test_';

    /**
     * @var string
     */
    protected $_defaultDriverType = 'default';

    protected function setUp(): void
    {
        $this->_factory = new Factory(
            $this->_defaultDriverPrefix,
            $this->_defaultDriverType
        );
    }

    public function testConstructor()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $this->assertAttributeEquals($this->_defaultDriverPrefix, '_defaultDriverPrefix', $this->_factory);
        $this->assertAttributeEquals($this->_defaultDriverType, '_defaultDriverType', $this->_factory);
    }

    public function testDefaultConstructor()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $factory = new Factory();
        $this->assertAttributeNotEmpty('_defaultDriverPrefix', $factory);
        $this->assertAttributeNotEmpty('_defaultDriverType', $factory);
    }

    /**
     * @dataProvider createDataProvider
     * @param array $config
     * @param string $expectedClass
     */
    public function testCreate($config, $expectedClass)
    {
        if (isset($config['type']) && is_callable($config['type'])) {
            $config['type'] = $config['type']($this);
        }
        if (is_callable($expectedClass)) {
            $expectedClass = $expectedClass($this);
        }
        $driver = $this->_factory->create($config);
        $this->assertInstanceOf($expectedClass, $driver);
        $this->assertInstanceOf(DriverInterface::class, $driver);
    }

    /**
     * @return array
     */
    public static function createDataProvider()
    {
        return [
            'Prefix and concrete type' => [
                ['type' => 'test'],
                static fn (self $testCase) => $testCase->getMockForDriverClass()['testDriverClass']
            ],
            'Prefix and default type' => [
                [],
                static fn (self $testCase) => $testCase->getMockForDriverClass()['defaultDriverClass']
            ],
            'Concrete class' => [
                ['type' => static fn (self $testCase) => $testCase->getMockForDriverClass()['testDriverClass']],
                static fn (self $testCase) => $testCase->getMockForDriverClass()['testDriverClass']
            ]
        ];
    }

    public function getMockForDriverClass()
    {
        $defaultDriverClassMock = $this->getMockForAbstractClass(
            DriverInterface::class,
            [],
            'Magento_Framework_Profiler_Driver_Test_Default',
            true,
            true,
            true,
            []
        );

        $defaultDriverClass = get_class($defaultDriverClassMock);

        $testDriverClassMock = $this->getMockForAbstractClass(
            DriverInterface::class,
            [],
            'Magento_Framework_Profiler_Driver_Test_Test',
            true,
            true,
            true,
            []
        );

        $testDriverClass = get_class($testDriverClassMock);

        return [
            'defaultDriverClass' => $defaultDriverClass,
            'testDriverClass' => $testDriverClass
        ];
    }

    public function testCreateUndefinedClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Cannot create profiler driver, class "Magento_Framework_Profiler_Driver_Test_Baz" doesn\'t exist.'
        );
        $this->_factory->create(['type' => 'baz']);
    }

    public function testCreateInvalidClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Driver class "stdClass" must implement \Magento\Framework\Profiler\DriverInterface.'
        );
        $this->_factory->create(['type' => 'stdClass']);
    }
}
