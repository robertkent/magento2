<?php
/**
 * Test class for Magento_Profiler_Driver_Standard
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Profiler_Driver_StandardTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Profiler_Driver_Standard_Stat|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stat;

    /**
     * @var Magento_Profiler_Driver_Standard
     */
    protected $_driver;

    protected function setUp()
    {
        $this->_stat = $this->getMock('Magento_Profiler_Driver_Standard_Stat');
        $this->_driver = new Magento_Profiler_Driver_Standard(array(
            'stat' => $this->_stat
        ));
    }

    protected function tearDown()
    {
        Magento_Profiler::reset();
    }

    /**
     * Test __construct method with no arguments
     */
    public function testDefaultConstructor()
    {
        $driver = new Magento_Profiler_Driver_Standard();
        $this->assertAttributeInstanceOf('Magento_Profiler_Driver_Standard_Stat', '_stat', $driver);
    }

    /**
     * Test clear method
     */
    public function testClear()
    {
        $this->_stat->expects($this->once())->method('clear')->with('timer_id');
        $this->_driver->clear('timer_id');
    }

    /**
     * Test start method
     */
    public function testStart()
    {
        $this->_stat->expects($this->once())->method('start')->with(
            'timer_id',
            $this->greaterThanOrEqual(microtime(true)),
            $this->greaterThanOrEqual(0),
            $this->greaterThanOrEqual(0)
        );
        $this->_driver->start('timer_id');
    }

    /**
     * Test stop method
     */
    public function testStop()
    {
        $this->_stat->expects($this->once())->method('stop')->with(
            'timer_id',
            $this->greaterThanOrEqual(microtime(true)),
            $this->greaterThanOrEqual(0),
            $this->greaterThanOrEqual(0)
        );
        $this->_driver->stop('timer_id');
    }

    /**
     * Test _initOutputs method
     */
    public function testInitOutputs()
    {
        $outputFactory = $this->getMock('Magento_Profiler_Driver_Standard_Output_Factory');
        $config = array(
            'outputs' => array(
                'outputTypeOne' => array(
                    'baseDir' => '/custom/base/dir'
                ),
                'outputTypeTwo' => array(
                    'type' => 'specificOutputTypeTwo'
                ),
            ),
            'baseDir' => '/base/dir',
            'outputFactory' => $outputFactory
        );

        $outputOne = $this->getMock('Magento_Profiler_Driver_Standard_OutputInterface');
        $outputTwo = $this->getMock('Magento_Profiler_Driver_Standard_OutputInterface');

        $outputFactory->expects($this->at(0))
            ->method('create')
            ->with(array(
                'baseDir' => '/custom/base/dir',
                'type' => 'outputTypeOne'
            ))
            ->will($this->returnValue($outputOne));

        $outputFactory->expects($this->at(1))
            ->method('create')
            ->with(array(
                'type' => 'specificOutputTypeTwo',
                'baseDir' => '/base/dir'
            ))
            ->will($this->returnValue($outputTwo));

        $driver = new Magento_Profiler_Driver_Standard($config);
        $this->assertAttributeCount(2, '_outputs', $driver);
        $this->assertAttributeEquals(array($outputOne, $outputTwo), '_outputs', $driver);
    }

    /**
     * Test display method
     */
    public function testDisplayAndRegisterOutput()
    {
        $outputOne = $this->getMock('Magento_Profiler_Driver_Standard_OutputInterface');
        $outputOne->expects($this->once())->method('display')->with($this->_stat);
        $outputTwo = $this->getMock('Magento_Profiler_Driver_Standard_OutputInterface');
        $outputTwo->expects($this->once())->method('display')->with($this->_stat);

        $this->_driver->registerOutput($outputOne);
        $this->_driver->registerOutput($outputTwo);
        Magento_Profiler::enable();
        $this->_driver->display();
        Magento_Profiler::disable();
        $this->_driver->display();
    }

    /**
     * Test _getOutputFactory method creating new object by default
     */
    public function testDefaultOutputFactory()
    {
        $method = new ReflectionMethod($this->_driver, '_getOutputFactory');
        $method->setAccessible(true);
        $this->assertInstanceOf(
            'Magento_Profiler_Driver_Standard_Output_Factory',
            $method->invoke($this->_driver)
        );
    }
}
