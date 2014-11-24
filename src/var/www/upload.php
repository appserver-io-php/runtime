<?php

/**
 *   'fileToUpload' => array (
 *       'name' => '374752_1163700272143l.jpg', 
 *       'type' => 'image/jpeg', 
 *       'tmp_name' => '/private/var/tmp/phpEe39MM', 
 *       'error' => 0, 
 *       'size' => 8584
 *   )
 *  
 */

var_export($_REQUEST);
var_export($_FILES);
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Upload Test</title>
    </head>
    <body>
        <form action="upload.php" enctype="multipart/form-data" method="post">
            <fieldset>
                <legend>File upload exammple: </legend>
                <label for="fileToUpload">Select a file: </label>
                <input id="fileToUpload" type="file" name="fileToUpload"/>
                <input type="submit" name="Upload"/>
            </fieldset>
        </form>
    </body>
</html>