<?php

// activate internal error handling, necessary to catch errors with libxml_get_errors()
libxml_use_internal_errors(true);

$dom = new DOMDocument();

// let's have a nice output
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;

// load the XML string defined above
$dom->load('chapter.xml');

// substitute xincludes
$dom->xinclude();

echo $dom->saveXML();

$included = new DOMDocument();
$included->loadXml($dom->saveXML());

// validate the document
if ($included->schemaValidate('chapter.xsd') === false) {
    foreach (libxml_get_errors() as $error) {
        $message = "Found a schema validation error on line %s with code %s and message %s when validating configuration file %s";
        var_export($error, true);
        throw new \Exception(sprintf($message, $error->line, $error->code, $error->message, $error->file));
    }
}