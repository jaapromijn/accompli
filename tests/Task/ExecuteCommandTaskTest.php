<?php

namespace Accompli\Test\Task;

use Accompli\AccompliEvents;
use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\PrepareReleaseEvent;
use Accompli\EventDispatcher\Event\ReleaseEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskCommandExecutionException;
use Accompli\Task\ExecuteCommandTask;
use PHPUnit_Framework_TestCase;

/**
 * ExecuteCommandTaskTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ExecuteCommandTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if ExecuteCommandTask::getSubscribedEvents returns an array with at least a
     * AccompliEvents::INSTALL_RELEASE, AccompliEvents::DEPLOY_RELEASE and AccompliEvents::ROLLBACK_RELEASE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', ExecuteCommandTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::INSTALL_RELEASE, ExecuteCommandTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::DEPLOY_RELEASE, ExecuteCommandTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::ROLLBACK_RELEASE, ExecuteCommandTask::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new ExecuteCommandTask sets the instance properties.
     */
    public function testConstruct()
    {
        $task = new ExecuteCommandTask(array(AccompliEvents::INSTALL_RELEASE), 'echo', array('test'));

        $this->assertAttributeSame(array(AccompliEvents::INSTALL_RELEASE), 'events', $task);
        $this->assertAttributeSame('echo', 'command', $task);
        $this->assertAttributeSame(array('test'), 'arguments', $task);
    }

    /**
     * Tests if ExecuteCommandTask::onEvent successfully executes the command.
     */
    public function testOnEventWithPrepareReleaseEvent()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(3))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('changeWorkingDirectory');
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with($this->equalTo('echo'), $this->equalTo(array('test')))
                ->willReturn(new ProcessExecutionResult(0, '', ''));

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $event = new PrepareReleaseEvent($workspaceMock, '0.1.0');

        $task = new ExecuteCommandTask(array(AccompliEvents::PREPARE_RELEASE), 'echo', array('test'));
        $task->onEvent($event, AccompliEvents::PREPARE_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if ExecuteCommandTask::onEvent successfully executes the command.
     */
    public function testOnEventWithReleaseEvent()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(3))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('changeWorkingDirectory');
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with($this->equalTo('echo'), $this->equalTo(array('test')))
                ->willReturn(new ProcessExecutionResult(0, '', ''));

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $event = new ReleaseEvent($releaseMock);

        $task = new ExecuteCommandTask(array(AccompliEvents::INSTALL_RELEASE), 'echo', array('test'));
        $task->onEvent($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if ExecuteCommandTask::onEvent logs the failure after executing the command.
     */
    public function testOnEventFailure()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(1))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('changeWorkingDirectory');
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with($this->equalTo('echo'), $this->equalTo(array('test')))
                ->willReturn(new ProcessExecutionResult(1, '', ''));

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $event = new ReleaseEvent($releaseMock);

        $this->setExpectedException(TaskCommandExecutionException::class, 'Failed executing command "echo".');

        $task = new ExecuteCommandTask(array(AccompliEvents::INSTALL_RELEASE), 'echo', array('test'));
        $task->onEvent($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }
}
