<?php

namespace TechDivision\Example;

use TechDivision\SplClassLoader;
use TechDivision\Example\Entities\Sample;
use TechDivision\PersistenceContainerClient\Context\Connection\Factory;

// set the session timeout to unlimited
ini_set('session.gc_maxlifetime', 0);
ini_set('zend.enable_gc', 0);
ini_set('max_execution_time', 0);

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(dirname(__FILE__)));

$paths[] = BP . DS . 'webapps' . DS .'example' . DS . 'META-INF' . DS . 'classes';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'local';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'community';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'core';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'lib';

// set the new include path
set_include_path(implode(PS, $paths) . PS . get_include_path());

require_once 'TechDivision/SplClassLoader.php';

$classLoader = new SplClassLoader();
$classLoader->register();

session_start();

// initialize the connection, the session and the initial context
/*
 * Pass parameter 'Queue' to create a queue based client.
 * 
 * $connection = Factory::createContextConnection('Queue');
 * $connection = Factory::createContextConnection('SingleSocket');
 */
// $connection = Factory::createContextConnection();
$connection = Factory::createContextConnection();
$session = $connection->createContextSession();
$initialContext = $session->createInitialContext();

// lookup the remote processor implementation
$processor = $initialContext->lookup('TechDivision\Example\Services\SampleProcessor');

if (array_key_exists('action', $_REQUEST)) {
    $action = $_REQUEST['action'];
} else {
    $action = 'findAll';
}

$sampleId = '';
$name = '';

switch ($action) {
    case 'load':
        $entity = $processor->load($_REQUEST['sampleId']);
        $name = $entity->getName();
        $sampleId = $entity->getSampleId();
        $entities = $processor->findAll();
        break;
    case 'delete':
        $entities = $processor->delete($_REQUEST['sampleId']);
        break;
    case 'persist':
        $entity = new Sample();
        $entity->setSampleId((integer) $_POST['sampleId']);
        $entity->setName($_POST['name']);
        $processor->persist($entity);
        $entities = $processor->findAll();
        break;
    case 'createSchema':
        $processor->createSchema();
        $entities = $processor->findAll();
        break;
    case 'changeWorker':
        $processor->changeWorker($_REQUEST['workers']);
        $entities = $processor->findAll();
        break;
    default:
        $entities = $processor->findAll();
        error_log("Found " . sizeof($entities) . " entities");
        break;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Sample Test</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <div>
            <ul>
                <li><a href="index.php?action=findAll">Home</a></li>
                <li><a href="index-script.php?action=findAll">Script version</a></li>
            </ul>
        </div>
        <div>
            <form action="index.php" method="post">
                <input type="hidden" name="action" value="persist" />
                <fieldset>
                    <legend>Sample</legend>
                    <table><tr>
                            <td>Id:</td>
                            <td><input type="text" size="40" maxlength="40" name="sampleId" value="<?php echo $sampleId ?>"></td>
                        </tr><tr>
                            <td>Name:</td>
                            <td><input type="text" size="40" maxlength="40" name="name" value="<?php echo $name ?>"></td>
                        </tr><tr>
                            <td colspan="2"><input type="submit" value="Save"></td>
                        </tr>
                    </table>
                    <?php if (isset($entity)) { ?><table>
                        <thead>
                            <tr>
                                <td>Username</td>
                                <td>Locale</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entity->getUsers() as $user) { ?><tr>
                                <td><?php echo $user->getUsername() ?></td>
                                <td><?php echo $user->getUserLocale() ?></td>
                            </tr><?php } ?>
                        </tbody>
                    </table><?php } ?>
                </fieldset>
            </form>
        </div>
        <div>
            <table>
                <thead>
                    <tr>
                        <td>Id</td>
                        <td>Name</td>
                        <td>Actions</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entities as $sampleId => $entity) { ?><tr>
                        <td><a href="index.php?action=load&sampleId=<?php echo $entity->getSampleId() ?>"><?php echo $entity->getSampleId() ?></a></td>
                        <td><?php echo $entity->getName() ?></td>
                        <td><a href="index.php?action=delete&sampleId=<?php echo $entity->getSampleId() ?>">Delete</a></td>
                    </tr><?php } ?>
                </tbody>
            </table>
        </div>
    </body>
</html>