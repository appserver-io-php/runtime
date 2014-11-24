<?php
/**
 * AppserverIo\Php\Runtime\ThreadDepthTest
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    TechDivision
 * @subpackage Runtime
 * @author     René Rösner <r.roesner@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

namespace AppserverIo\Php\Runtime;

/**
 * Sometimes an error occured that threads were not able to instantiate themselves to any depth.
 *
 * @category   AppServer
 * @package    TechDivision
 * @subpackage Runtime
 * @author     René Rösner <r.roesner@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

require_once "ThreadDepthTestThread.php";
require_once "Mock/TestThreadDataContainer.php";

class ThreadDepthTest extends \PHPUnit_Framework_TestCase
{

    const THREAD_DEPTH = 64;

    const VERIFICATION_CONTAINER_KEY = 'verification';
    const COUNTER_CONTAINER_KEY = 'counter';
    const THREADS_CONTAINER_KEY = 'threads';

    protected $testThread;
    protected $testThreadContainer;

    public function setUp()
    {
        $initCount = 0;

        // Inits the Container setting the threaddepth, the counter and the threadarray
        $this->testThreadContainer = new TestThreadDataContainer();
        $this->testThreadContainer[self::COUNTER_CONTAINER_KEY] = self::THREAD_DEPTH;

        $this->testThreadContainer[self::VERIFICATION_CONTAINER_KEY] = $initCount;
        $this->testThreadContainer[self::THREADS_CONTAINER_KEY] = array();

        // Inits the first thread that initialises the following depths
        $this->testThread = new ThreadDepthTestThread($this->testThreadContainer);

        // Workaround for pthreads indexingproblem
        $threadContainer = $this->testThreadContainer[self::THREADS_CONTAINER_KEY];
        $threadCount = count($threadContainer);
        $threadContainer[$threadCount] = $this->testThread;

        $this->testThreadContainer[self::THREADS_CONTAINER_KEY] = $threadContainer ;
    }

    public function testThreadsAreAbleToInstantiateToAnyDepth()
    {
        $this->testThread->start();
        $this->testThread->join();

        $completedDepth = $this->testThreadContainer[self::VERIFICATION_CONTAINER_KEY];
        $this->assertEquals(self::THREAD_DEPTH, $completedDepth);
    }
}