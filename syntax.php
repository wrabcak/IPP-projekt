<?php

class SyntaxHighlite
{
    private  $availableCommands = array('bold','italic','underline','teletype','size','color');

    private $arrayOfCommands = array();

    public function addFormatLine($syntaxLine)
    {

        if(trim($syntaxLine) == '')
            exit(ERR_OK);

        preg_match('/^([\S ]+)\t+([\S\t ]+)$/', $syntaxLine, $match);
        if(count($match) == 0)
            throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);

        array_push($this->arrayOfCommands,array('Regex'=>$test,'Commnand'=>$this->parseFormatCommand($match[2])));
        var_dump($this->arrayOfCommands);
    }

    private function parseFormatCommand($formatLine)
    {
        $commands = preg_split('/[\t ]*(,)[\t ]*/',$formatLine);
        $parsedCommand = array();

        foreach($commands as $command)
        {
            $splitCommand = split(':',$command);
            if(!in_array($splitCommand[0],$this->availableCommands))
                 throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);
            if($splitCommand[1] == 'size' || $splitCommand[1] == 'color')
            {
                if(!($splitCommand[1] <= 7 && $splitCommand[1] >= 1))
                    throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);

                if(!(ctype_xdigit($splitCommand[1])))
                    throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);
            }
            array_push($parsedCommand, $splitCommand);
        }
        return $parsedCommand;
    }
}
?>
