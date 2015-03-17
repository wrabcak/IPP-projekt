<?php

#SYN:xvrabe07


//turn off warnings
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
    // try to parse parameters
    $files->parseParams();
}
catch (Exception $e)
{
    error_log($e->getMessage());
    exit ($e->getCode());
}

// create new object parser
$parser = new Parser();

try
{
    // reading format lines while is not end of file
    while(($syntaxLine = $files->getFormatLine()) != FALSE)
    {
        // add format line to format database
        $parser->addFormatLine($syntaxLine);
    }
}
catch (Exception $e)
{
    error_log($e->getMessage());
    exit ($e->getCode());
}

// get formated rules
$db = $parser->getDB();

// get input text
$input = $files->getInput();

// chech if was param --br set
$newLine = $files->newLineParam();

// create new object syntax wih parameters
$syntax = new Syntax($db,$input,$newLine);

// apply rules
$syntax->apply();

// get output text wit html tags
$output = $syntax->getOutput();

// print output to the output file
$files->printOutput($output);

// close fd
$files->closeFiles();

// return OK
exit(ERR_OK);

?>
