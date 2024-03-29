<?php


class Files
{
    private $inputFile = null;
    private $formatFile = null;
    private $outputFile = null;
    private $newline = 0;
    public $parameters;

    function __construct($argv)
    {
        $this->parameters = $argv;
    }

    public function parseParams()
    {
        foreach($this->parameters as $index=>$param)
        {
            if($param == '-h' || $param == '--help')
            {
                if(count($this->parameters) == 2)
                {
                    Help::printHelp();
                    exit(ERR_OK);
                }
                else
                    throw new Exception('ERROR WRONG PARAMS!',ERR_PARAM);
            }

            elseif(preg_match("/^--format=(.+)$/",$param,$match))
            {
                if($this->formatFile == null)
                {
                    $this->formatFile = fopen($match[1],'r');
                }
                else
                    throw new Exception('ERROR WRONG PARAMS!',ERR_PARAM);
            }

            elseif(preg_match("/^--input=(.+)$/",$param,$match))
            {
                if($this->inputFile == null)
                {
                    if(($this->inputFile = fopen($match[1],'r')) == False)
                        throw new Exception('ERROR WRONG INPUT FILE!',ERR_INPUT);
                }
                else
                    throw new Exception('ERROR WRONG PARAMS!',ERR_PARAM);
            }

            elseif(preg_match("/^--output=(.+)$/",$param,$match))
            {
                if($this->outputFile == null)
                {
                    if(($this->outputFile = fopen($match[1],'w')) == False)
                        throw new Exception('ERROR WRONG OUTPUT FILE!',ERR_OUTPUT);
                }
                else
                    throw new Exception('ERROR WRONG PARAMS!',ERR_PARAM);
            }

            elseif ($param == '--br')
                $this->newline = 1;

            elseif ($index == 0)
                continue;

            else
                throw new Exception('ERROR WRONG PARAMS!',ERR_PARAM);
        }

        if($this->outputFile == null)
            $this->outputFile = fopen("php://stdout", 'w');

        if($this->inputFile == null)
            $this->inputFile = fopen("php://stdin", 'r');

        return ERR_OK;
    }

    public function getFormatLine()
    {
        if(($line = fgets($this->formatFile)) == FALSE)
            return FALSE;
        else
            return $line;
    }

    public function getInput()
    {
        $output = '';
        while(($line = fgets($this->inputFile)) != FALSE)
            $output = $output . $line;

        return $output;
    }

    public function newLineparam()
    {
        return $this->newline;
    }

    public function closeFiles()
    {
        if($this->outputFile != NULL)
            fclose($this->outputFile);

        return ERR_OK;
    }

    public function printOutput($output)
    {
        fwrite($this->outputFile,$output);
    }
}

class Help
{
    public function printHelp()
    {
        print "IPP SYN PROJECT 2014/2015.\n";
        print "usage: php ./syn.php <--help> <--input={file}> <--output={file}> <--format={file} <--br>\n";
        print "--help              This help page.\n";
        print "--br                Add new line on end of each line. \n";
        print "--input={FILE}      Input file. \n";
        print "--output={FILE}     Output file. \n";
        print "--format={FILE}     Format file. \n";
    }
}

?>
