<?php

#SYN:xvrabe07

error_reporting(0);

define("ERR_OK",0);
define("ERR_PARAM",1);
define("ERR_INPUT",2);
define("ERR_OUTPUT",3);
define("ERR_FORMAT_LINE",4);

require_once('file.php');
require_once('parser.php');
require_once('syntax.php');

$files = new Files($argv);

try
{
    $files->parseParams();
}
catch (Exception $e)
{
    error_log($e->getMessage());
    exit ($e->getCode());
}

$parser = new Parser();

try
{
    while(($syntaxLine = $files->getFormatLine()) != FALSE)
    {
        $parser->addFormatLine($syntaxLine);
    }
}
catch (Exception $e)
{
    error_log($e->getMessage());
    exit ($e->getCode());
}

$db = $parser->getDB();
$input = $files->getInput();
$newLine = $files->newLineParam();
$syntax = new Syntax($db,$input,$newLine);

$syntax->apply();

$output = $syntax->getOutput();

$files->printOutput($output);
$files->closeFiles();
exit(ERR_OK);

?>
