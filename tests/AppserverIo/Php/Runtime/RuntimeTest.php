<?php

/**
 * AppserverIo\Php\Runtime\RuntimeTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    Psr
 * @subpackage MessageQueueProtocol
 * @author     Tim Wagner <tw@appserver.io>
 * @copyright  2014 TechDivision GmbH <info@appserver.io>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/appserver-io-psr/messagequeueprotocol
 * @link       http://www.appserver.io
 */

namespace AppserverIo\Php\Runtime;

/**
 * Runtime test implementations.
 *
 * @category   Appserver
 * @package    Psr
 * @subpackage MessageQueueProtocol
 * @author     Tim Wagner <tw@appserver.io>
 * @copyright  2014 TechDivision GmbH <info@appserver.io>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/appserver-io-psr/messagequeueprotocol
 * @link       http://www.appserver.io
 */
class RuntimeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests if pthreads is available.
     *
     * @return void
     */
    public function testPthreadsAvailable()
    {
        $this->assertTrue(extension_loaded('pthreads'));
    }

    /**
     * Tests if the threaded classes are available.
     *
     * @return void
     */
    public function testThreadClassAvailable()
    {
        $this->assertTrue(class_exists('\Pool'));
        $this->assertTrue(class_exists('\Cond'));
        $this->assertTrue(class_exists('\Mutex'));
        $this->assertTrue(class_exists('\Thread'));
        $this->assertTrue(class_exists('\Worker'));
        $this->assertTrue(class_exists('\Stackable'));
    }
}
