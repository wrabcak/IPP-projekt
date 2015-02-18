<?php
/*
* @Projekt: IPP SYN
* @Autor: Lukas Vrabec <xvrabe07@stud.fit.vutbr.cz>
* @file: syn.php
*/

#error_reporting(0);

define("ERR_OK",0);
define("ERR_PARAM",1);
define("ERR_INPUT",2);
define("ERR_OUTPUT",3);
define("ERR_FORMAT_LINE",4);

require_once('file.php');
require_once('syntax.php');

$files = new Files($argv);

try
{
    $files->parseParams();
}
catch (Exception $e)
{
    echo $e->getMessage();
    exit ($e->getCode());
}

$syntaxHighliter = new SyntaxHighlite();

try
{
    while(($syntaxLine = $files->getFormatLine()) != FALSE)
    {
        $syntaxHighliter->addFormatLine($syntaxLine);
    }
}
catch (Exception $e)
{
    echo $e->getMessage();
    exit ($e->getCode());
}

exit(ERR_OK);

?>
