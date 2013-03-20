<?php
namespace Net\Gearman\Tests;

use Net\Gearman\MappedJobFactory;

/**
 * @category   Testing
 * @package    Net_Gearman
 * @author     Ray Rehbein <mrrehbein@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Net_Gearman
 * @since      0.2.4
 */
class MappedJobFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Net_Gearman_MappedJobFactory->__construct()
     *
     * @expectedException Net\Gearman\Job\JobException
     */
    public function test__construct_empty()
    {
        $factory = new MappedJobFactory();
        $class = $factory->getJobClassName('test');
    }

    /**
     * Tests Net_Gearman_MappedJobFactory->__construct()
     *
     */
    public function test__construct_notEmpty()
    {
        $map = array('test' => 'className123', 'test2' => 'className456',);

        $factory = new MappedJobFactory($map);
        $class = $factory->getJobClassName('test');
        $this->assertEquals('className123', $class);
    }

    /**
     * Tests Net_Gearman_MappedJobFactory->mapJobClasses()
     */
    public function testMapJobClasses()
    {
        $map = array('test' => 'className123', 'test2' => 'className456',);

        $factory = new MappedJobFactory();
        $factory->mapJobClasses($map);

        $class = $factory->getJobClassName('test');
        $this->assertEquals('className123', $class);

        $class = $factory->getJobClassName('test2');
        $this->assertEquals('className456', $class);
    }

    /**
     * Tests Net_Gearman_MappedJobFactory->mapJobClass()
     */
    public function testMapJobClass()
    {
        $factory = new MappedJobFactory();

        $factory->mapJobClass('test', 'value1');
        $class = $factory->getJobClassName('test');
        $this->assertEquals('value1', $class);

        $factory->mapJobClass('test', 'value2');
        $class = $factory->getJobClassName('test');
        $this->assertEquals('value2', $class, 'old value was not overwritten');

        $factory->mapJobClass('test2', 'value123');
        $class = $factory->getJobClassName('test2');
        $this->assertEquals('value123', $class);
        $class = $factory->getJobClassName('test');
        $this->assertEquals('value2', $class, 'old value was not preserved');
    }

    /**
     * Tests Net_Gearman_MappedJobFactory->getJobClassName()
     * @expectedException Net\Gearman\Job\JobException
     */
    public function testGetJobClassName_empty()
    {
        $factory = new MappedJobFactory();
        $class = $factory->getJobClassName('test');
    }

    /**
     * Tests Net_Gearman_MappedJobFactory->getJobClassName()
     */
    public function testGetJobClassName_valid()
    {
        $map = array('test' => 'className');
        $factory = new MappedJobFactory($map);
        $class = $factory->getJobClassName('test');

        $this->assertEquals('className', $class);
    }

    /**
     * Tests Net_Gearman_MappedJobFactory->mapToWorker()
     */
    public function testMapToWorker_OneJob_WithParams()
    {
        if (!class_exists('PHPUnit_Framework_MockObject_Generator')) {
            $this->markTestSkipped("mapToWorker test uses PHPUnit_Framework_MockObject_Generator");
        }

        $params = array('test values', 'and such');

        $worker = $this->getMock('Net\Gearman\Worker', array(), array(), '', false);
        $worker->expects($this->once())->method('addAbility')->with('test', null, $params);

        $map = array('test' => 'className');
        $factory = new MappedJobFactory($map);

        $factory->mapToWorker($worker, $params);
    }

    /**
     * Tests Net_Gearman_MappedJobFactory->mapToWorker()
     */
    public function testMapToWorker_NoInitParams()
    {
        if (!class_exists('PHPUnit_Framework_MockObject_Generator')) {
            $this->markTestSkipped("mapToWorker test uses PHPUnit_Framework_MockObject_Generator");
        }

        $worker = $this->getMock('Net\Gearman\Worker', array(), array(), '', false);
        $worker->expects($this->once())->method('addAbility')->with('test', null, array());

        $map = array('test' => 'className');
        $factory = new MappedJobFactory($map);

        $factory->mapToWorker($worker);
    }

    /**
     * Tests Net_Gearman_MappedJobFactory->mapToWorker()
     */
    public function testMapToWorker_MultipleJobs_WithParams()
    {
        if (!class_exists('PHPUnit_Framework_MockObject_Generator')) {
            $this->markTestSkipped("mapToWorker test uses PHPUnit_Framework_MockObject_Generator");
        }

        $params = array('test values', 'and such');

        $worker = $this->getMock('Net\Gearman\Worker', array(), array(), '', false);
        $worker->expects($this->at(0))->method('addAbility')->with('test', null, $params);
        $worker->expects($this->at(1))->method('addAbility')->with('test2', null, $params);

        $map = array('test' => 'className', 'test2' => 'className',);
        $factory = new MappedJobFactory($map);

        $factory->mapToWorker($worker, $params);
    }

    /**
     * Tests Net_Gearman_MappedJobFactory->factory()
     */
    public function testFactory()
    {
        if (!class_exists('PHPUnit_Framework_MockObject_Generator')) {
            $this->markTestSkipped("mapToWorker test uses PHPUnit_Framework_MockObject_Generator");
        }

        $commonJob = $this->getMockForAbstractClass('Net\Gearman\Job\CommonJob', array(), '', false, false);

        $commonJobClass = get_class($commonJob);

        $map = array('test' => $commonJobClass, 'test2' => $commonJobClass . '_invalid',);
        $factory = new MappedJobFactory($map);

        $jobHandle = 'H:UnitTest:' . posix_getpid();

        $job = $factory->factory('test', null, $jobHandle);

        // Property not visable
        // $this->assertEquals($jobHandle, $job->getJobHandle());

        $this->assertInstanceOf('Net\Gearman\Job\CommonJob', $job);
        $this->assertInstanceOf($commonJobClass, $job);
    }
}
