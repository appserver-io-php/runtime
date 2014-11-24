<?php
/**
 * AppserverIo\Php\Runtime\TestThread
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
 * This class describes a thread made to test the possible depth
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

require_once "Mock/TestThreadDataContainer.php";

class ThreadDepthTestThread extends \Thread
{

    const VERIFICATION_CONTAINER_KEY = 'verification';
    const COUNTER_CONTAINER_KEY = 'counter';
    const THREADS_CONTAINER_KEY = 'threads';
    const ZERO = 0;

    public $container;

    public function __construct(TestThreadDataContainer $container)
    {
        $this->container = $container;
        error_log(var_export("Thread initialised in depth " . $this->container["counter"] . PHP_EOL, true));
    }

    public function run()
    {
        if ($this->container[self::COUNTER_CONTAINER_KEY] > self::ZERO) {
            $this->container[self::COUNTER_CONTAINER_KEY] = $this->container[self::COUNTER_CONTAINER_KEY] - 1;
            $this->container[self::VERIFICATION_CONTAINER_KEY] = $this->container[self::VERIFICATION_CONTAINER_KEY] + 1;

            $child = new ThreadDepthTestThread($this->container);
            array_push($this->container[self::THREADS_CONTAINER_KEY], $child);

            $child->start();
            $child->join();
        }
    }
}
