<?php
/**
 * This file contains a testcase showing a problem with predefined membervariables and the needed testThread.
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    AppserverIo\Php\Runtime
 * @subpackage PthreadProblemPrevention
 * @author     René Rösner <r.roesner@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

namespace AppserverIo\Php\Runtime;

/**
 * While programming with pthreads an error occured with a membervariable, that was empty although predefined
 * in the descriptionheader of the php class. The observation had been made, that only membervariables set in the
 * constructor are valued in the threadcontext. Currently we are using php in version 5.5.8 .
 *
 * @category   AppServer
 * @package    AppserverIo\Php\Runtime
 * @subpackage PthreadProblemPrevention
 * @author     René Rösner <r.roesner@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

const CONTAINERKEY = "PredefinedMemberVariableTest";
const EXPECTED = 1;

class PredefinedMemberVariableTest extends \PHPUnit_Framework_TestCase
{
    protected $testThread;
    protected $testContainer;

    public function setUp()
    {
        $this->testContainer = new TestContainer();
        $this->testThread = new TestThread($this->testContainer);
    }

    public function testPredefinedMemberVariablesContainValueByContainer()
    {
        $this->runTestThread();
        $this->assertEquals(EXPECTED, $this->testContainer[CONTAINERKEY]);
    }

    public function testPredefinedMemberVariableContainsValueByThread()
    {
        $this->runTestThread();
        $this->assertEquals(EXPECTED, $this->testThread->predefinedMember);
    }

    public function testPredefinedMemberVariableAssignedInConstructToSameLocalVariable()
    {
        $this->runTestThread();
        $this->assertEquals(EXPECTED, $this->testThread->localPredefinedMemberWithSameNameAsMember);
    }

    public function runTestThread()
    {
        $this->testThread->start();
        $this->testThread->join();
    }
}

class TestThread extends \Thread
{
    public $predefinedMember = EXPECTED;
    public $localPredefinedMemberWithSameNameAsMember = EXPECTED;
    protected $container;

    public function __construct($container)
    {
        //$this->predefinedMember = EXPECTED;
        //$this->localPredefinedMemberWithSameNameAsMember = EXPECTED;

        $this->container = $container;
        $localPredefinedMemberWithSameNameAsMember = $this->localPredefinedMemberWithSameNameAsMember;
    }

    public function run()
    {
        $this->container[CONTAINERKEY] = $this->predefinedMember;
    }
}

class TestContainer extends \Stackable
{
    public function run()
    {
    }
}