<?php

/*
 * Class for parsing arguments, printing lines of format or input file.
 */
class Files
{
    private $inputFile = null; // fd for inputfile
    private $formatFile = null; // fd for formatfile
    private $outputFile = null; // fd for outputfile
    private $newline = 0; // is 1 when found '--br' in parameters, else 0
    public $parameters; // parameters for parsing

    function __construct($argv)
    {
        // when object created store arguments in variable $parameters.
        $this->parameters = $argv;
    }

    /*
     * Method for parsing parameters stored in variable $parameters.
     * This method also open format and input file for reading line and ouput file for
     * writing. When error occurs returns exception.
     */
    public function parseParams()
    {
        foreach($this->parameters as $index=>$param)
        {
            // When in parameters is '-h' or '--help' print help.
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

            // need for first iteration
            elseif ($index == 0)
                continue;

            else
                throw new Exception('ERROR WRONG PARAMS!',ERR_PARAM);
        }

        // when outputfile is not set use stdout
        if($this->outputFile == null)
            $this->outputFile = fopen("php://stdout", 'w');

        // when inputfile is not set use stdin
        if($this->inputFile == null)
            $this->inputFile = fopen("php://stdin", 'r');

        return ERR_OK;
    }

    /* Method for get one line from format file
     * Return line if exits, esle return FALSE.
     */
    public function getFormatLine()
    {
        if(($line = fgets($this->formatFile)) == FALSE)
            return FALSE;
        else
            return $line;
    }

    /*
     * Get whole input file.
     */
    public function getInput()
    {
        $output = '';
        while(($line = fgets($this->inputFile)) != FALSE)
            $output = $output . $line;

        return $output;
    }

    /*
     * Get value from variable newline.
     */
    public function newLineparam()
    {
        return $this->newline;
    }

    /*
     * Close output file after writing.
     */
    public function closeFiles()
    {
        if($this->outputFile != NULL)
            fclose($this->outputFile);

        return ERR_OK;
    }

    /*
     * Write output to the outputfile.
     */
    public function printOutput($output)
    {
        fwrite($this->outputFile,$output);
    }
}

/*
 * Class with one method for printing help.
 */
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
