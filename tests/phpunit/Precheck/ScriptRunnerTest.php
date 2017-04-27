<?php

namespace tests\phpunit\Precheck;

use PHPUnit_Framework_TestCase as TestCase;
use vfsStream;
use TryLib_Precheck_ScriptRunner as ScriptRunner;
use vfsStreamWrapper;

require_once "TryLib/Autoload.php";
require_once 'vfsStream/vfsStream.php';

class ScriptRunnerTest extends TestCase {

    private $mock_cmd_runner;

    function setUp() {
        parent::setUp();
        $this->mock_cmd_runner = $this->getMock('TryLib_CommandRunner');

        vfsStream::setup('testDir');
    }

    function testScriptDoesNotExists() {

        $script_runner = new ScriptRunner(vfsStream::url('testDir/script'));
        
        $this->mock_cmd_runner->expects($this->never())
                              ->method('run');

        $this->mock_cmd_runner->expects($this->never())
                              ->method('terminate');
        
        $script_runner->check($this->mock_cmd_runner, 'repoPath', 'origin/master');              
    }
    
    function testScriptExistsAndSucceeds() {
        vfsStream::newFile('script')
            ->at(vfsStreamWrapper::getRoot());

        $script_runner = new ScriptRunner(vfsStream::url('testDir/script'));
        
        $this->mock_cmd_runner->expects($this->once())
                              ->method('run')
                              ->with('vfs://testDir/script', false, true)
                              ->will($this->returnValue(0));

        $this->mock_cmd_runner->expects($this->never())
                              ->method('terminate');
        
        $script_runner->check($this->mock_cmd_runner, 'repoPath', 'origin/master');              
    }
    
    function testScriptExistsAndFails() {
        vfsStream::newFile('script')
            ->at(vfsStreamWrapper::getRoot());

        $script_runner = new ScriptRunner(vfsStream::url('testDir/script'));
        
        $this->mock_cmd_runner->expects($this->once())
                              ->method('run')
                              ->with('vfs://testDir/script', false, true)
                              ->will($this->returnValue(255));

        $this->mock_cmd_runner->expects($this->once())
                              ->method('terminate')
                              ->with('Failed running pre-check script vfs://testDir/script');
        
        $script_runner->check($this->mock_cmd_runner, 'repoPath', 'origin/master');              
    }
}
