<?php

define('SSTART',0);
define('SESCAPE',1);
define('SCHAR',2);
define('SAND',3);
define('SADD',4);
define('SOR',5);
define('SBRACKET',6);
define('SBRACLOSE',7);
define('SMULTI',8);
define('SNEG',9);

/*
 * Class for parsing format lines
 */
class Parser
{
    private  $availableCommands = array('bold','italic','underline','teletype','size','color');

    private $arrayOfCommands = array();

    /*
     * Method for adding new format line to the database structure
     */
    public function addFormatLine($syntaxLine)
    {
        // try to match regex and command line
        preg_match('/^([\S ]+)\t+([\S\t ]+)$/', $syntaxLine, $match);
        if(count($match) == 0)
            throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);

        // try to convert regex to php
        $regex = $this->parseFormatRegex($match[1]);

        // check commands
        $command = $this->parseFormatCommand($match[2]);

        // if is regex ok, push it to parsed regex and commnads db
        if($regex != NULL)
        {
            array_push($this->arrayOfCommands,array('Regex'=>$regex,'Command'=>$command));
        }
        else
        {
            exit(ERR_FORMAT_LINE);
            throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);
        }
    }

    /*
     * Method to get database with format rules
     */
    public function getDB()
    {
        return $this->arrayOfCommands;
    }

    /*
     * Method for parse command part of format line.
     * Return array of commands.
     */
    private function parseFormatCommand($formatLine)
    {
        // split commands
        $commands = preg_split('/[\t ]*(,)[\t ]*/',$formatLine);
        $parsedCommand = array();

        foreach($commands as $command)
        {
            $splitCommand = split(':',$command);
            // if command is not correct return exception.
            if(!in_array($splitCommand[0],$this->availableCommands))
                 throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);

            // if command is type 'size' check if the argument is more then 1 and less then 7
            if($splitCommand[0] == 'size')
            {
                if(!($splitCommand[1] <= 7 && $splitCommand[1] >= 1))
                    throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);
            }
            //  if command is type 'color' check if the argument is in hexadecimal digit
            if($splitCommand[0] == 'color')
            {
                if(!(ctype_xdigit($splitCommand[1])))
                    throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);
            }

            // push command to array of commands
            array_push($parsedCommand, $splitCommand);
        }
        return $parsedCommand;
    }

    /*
     * Convert ifj regex to php regex using FSM.
     */
    private function parseFormatRegex($schoolRegex)
    {
        $finalRegex = ""; // final php regex
        $state = SSTART; // init FSM by start state
        $iteration = 0;

        // if 1 then fsm can end with this state, else exit with NULL
        $end_state = 0;

        // go through every char in ifj regex and convert it to php regex
        while($iteration < strlen($schoolRegex))
        {
            switch($state)
            {
                case SSTART:
                case SBRACKET:
                case SOR:
                case SAND:
                    if ($schoolRegex[$iteration] == '.' || $schoolRegex[$iteration] == '|' || $schoolRegex[$iteration] == '*' || $schoolRegex[$iteration] == '+' || $schoolRegex[$iteration] == ')')
                    {
                        return null;
                    }
                    elseif($schoolRegex[$iteration] == '!')
                    {
                        $finalRegex = $finalRegex . '[^';
                        $state = SNEG;
                        $end_state = 0;
                    }

                    elseif($schoolRegex[$iteration] == '%')
                    {
                        $finalRegex = $finalRegex . '[';
                        $state = SESCAPE;
                        $end_state = 0;
                    }

                    elseif($schoolRegex[$iteration] == '(')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACKET;
                        $end_state = 0;
                    }

                    elseif($schoolRegex[$iteration] == '?' || $schoolRegex[$iteration] == '{' || $schoolRegex[$iteration] == '}' || $schoolRegex[$iteration] == '/' || $schoolRegex[$iteration] == '^' || $schoolRegex[$iteration] == '\\' || $schoolRegex[$iteration] == '$' || $schoolRegex[$iteration] == '[' || $schoolRegex[$iteration] == ']')
                    {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                        $state = SCHAR;
                        $end_state = 1;
                    }
                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SCHAR;
                        $end_state = 1;
                    }
                    else
                        return NULL;
                    break;

                case SCHAR:
                case SBRACLOSE:
                    if ($schoolRegex[$iteration] == '.')
                    {
                        $state = SAND;
                        $end_state = 0;
                    }

                    elseif ($schoolRegex[$iteration] == '%')
                    {
                        $finalRegex = $finalRegex . '[';
                        $state = SESCAPE;
                        $end_state = 0;
                    }
                    elseif ($schoolRegex[$iteration] == '!')
                    {
                        $finalRegex = $finalRegex . '[^';
                        $state = SNEG;
                        $end_state = 0;
                    }

                    elseif ($schoolRegex[$iteration] == '(')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACKET;
                        $end_state = 0;
                    }
                    elseif ($schoolRegex[$iteration] == ')')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACLOSE;
                        $end_state = 1;
                    }
                    elseif ($schoolRegex[$iteration] == '+')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SMULTI;
                        $end_state = 1;
                    }
                    elseif ($schoolRegex[$iteration] == '*')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SADD;
                        $end_state = 1;
                    }
                    elseif ($schoolRegex[$iteration] == '|')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SADD;
                        $end_state = 1;
                    }
                    elseif($schoolRegex[$iteration] == '?' || $schoolRegex[$iteration] == '{' || $schoolRegex[$iteration] == '}' || $schoolRegex[$iteration] == '/' || $schoolRegex[$iteration] == '^' || $schoolRegex[$iteration] == '\\' || $schoolRegex[$iteration] == '$' || $schoolRegex[$iteration] == '[' || $schoolRegex[$iteration] == ']')
                    {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                        $state = SCHAR;
                        $end_state = 1;
                    }
                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SCHAR;
                        $end_state = 1;
                    }
                    else
                        return NULL;
                    break;

                case SNEG:

                    if ($schoolRegex[$iteration] == '%')
                    {
                        $state = SESCAPE;
                        $end_state = 0;
                    }

                    elseif ($schoolRegex[$iteration] == '.' || $schoolRegex[$iteration] == '*' || $schoolRegex[$iteration] == '+' || $schoolRegex[$iteration] == '!' || $schoolRegex[$iteration] == '|' || $schoolRegex[$iteration] == '(' || $schoolRegex[$iteration] == ')')
                    {
                        return NULL;
                    }

                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration] . ']';
                        $state = SCHAR;
                        $end_state = 1;
                        break;
                    }
                    else
                        return NULL;
                    break;

               case SESCAPE:
                   if($schoolRegex[$iteration] == 't')
                   {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == 'd')
                   {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == 'n')
                   {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == '+')
                   {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == '*')
                   {
                       $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == '(')
                   {
                       $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == ')')
                   {
                       $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == '.')
                   {
                       $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == '|')
                   {
                       $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == '%')
                   {
                       $finalRegex = $finalRegex .  $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == '!')
                   {
                       $finalRegex = $finalRegex .  $schoolRegex[$iteration];
                   }
                   elseif($schoolRegex[$iteration] == 's')
                   {
                       $finalRegex = $finalRegex . '\\t\\r\\n\\f\\v ';
                   }
                   elseif($schoolRegex[$iteration] == 'a')
                   {
                       $finalRegex = $finalRegex . '\\S\\s';
                   }
                   elseif($schoolRegex[$iteration] == 'l')
                   {
                       $finalRegex = $finalRegex . 'a-z';
                   }
                   elseif($schoolRegex[$iteration] == 'L')
                   {
                       $finalRegex = $finalRegex . 'A-Z';
                   }
                   elseif($schoolRegex[$iteration] == 'w')
                   {
                       $finalRegex = $finalRegex . 'a-zA-Z';
                   }
                   elseif($schoolRegex[$iteration] == 'W')
                   {
                       $finalRegex = $finalRegex . 'a-zA-Z0-9';
                   }
                   else
                       return NULL;
                   $finalRegex = $finalRegex . ']';
                   $state = SCHAR;
                   $end_state = 1;
                   break;

                case SADD:
                    if ($schoolRegex[$iteration] == '.')
                    {
                        $state = SAND;
                        $end_state = 0;
                    }

                    elseif ($schoolRegex[$iteration] == '%')
                    {
                        $finalRegex = $finalRegex . '[';
                        $state = SESCAPE;
                        $end_state = 0;
                    }
                    elseif ($schoolRegex[$iteration] == '!')
                    {
                        $finalRegex = $finalRegex . '[^';
                        $state = SNEG;
                        $end_state = 0;
                    }

                    elseif ($schoolRegex[$iteration] == '(')
                    {
                        $finalRegex = $finalRegex . $school[$iteration];
                        $state = SBRACKET;
                        $end_state = 0;
                    }
                    elseif ($schoolRegex[$iteration] == ')')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACLOSE;
                        $end_state = 1;
                    }
                    elseif ($schoolRegex[$iteration] == '+')
                    {
                        $finalRegex[strlen($finalRegex)-1] = '*';
                        $state = SMULTI;
                        $end_state = 1;
                    }
                    elseif ($schoolRegex[$iteration] == '*')
                    {
                        $finalRegex[strlen($finalRegex)-1] = '*';
                        $state = SMULTI;
                        $end_state = 1;
                    }
                    elseif ($schoolRegex[$iteration] == '|')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SADD;
                        $end_state = 1;
                    }
                    elseif($schoolRegex[$iteration] == '?' || $schoolRegex[$iteration] == '{' || $schoolRegex[$iteration] == '}' || $schoolRegex[$iteration] == '/' || $schoolRegex[$iteration] == '^' || $schoolRegex[$iteration] == '\\' || $schoolRegex[$iteration] == '$' || $schoolRegex[$iteration] == '[' || $schoolRegex[$iteration] == ']')
                    {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                        $state = SCHAR;
                        $end_state = 1;
                        break;
                    }
                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SCHAR;
                        $end_state = 1;
                        break;
                    }
                    else
                        return NULL;
                    break;

                case SMULTI:
                    if ($schoolRegex[$iteration] == '.')
                    {
                        $state = SAND;
                        $end_state = 0;
                    }

                    elseif ($schoolRegex[$iteration] == '%')
                    {
                        $finalRegex = $finalRegex . '[';
                        $state = SESCAPE;
                        $end_state = 0;
                    }
                    elseif ($schoolRegex[$iteration] == '!')
                    {
                        $finalRegex = $finalRegex . '[^';
                        $state = SNEG;
                        $end_state = 0;
                    }

                    elseif ($schoolRegex[$iteration] == '(')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACKET;
                        $end_state = 0;
                    }
                    elseif ($schoolRegex[$iteration] == ')')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACLOSE;
                        $end_state = 1;
                    }
                    elseif ($schoolRegex[$iteration] == '+')
                    {
                        $state = SMULTI;
                        $end_state = 1;
                    }
                    elseif ($schoolRegex[$iteration] == '*')
                    {
                        $finalRegex[strlen($finalRegex)-1] = '*';
                        $state = SMULTI;
                        $end_state = 1;
                    }
                    elseif ($schoolRegex[$iteration] == '|')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SADD;
                        $end_state = 1;
                    }
                    elseif($schoolRegex[$iteration] == '?' || $schoolRegex[$iteration] == '{' || $schoolRegex[$iteration] == '}' || $schoolRegex[$iteration] == '/' || $schoolRegex[$iteration] == '^' || $schoolRegex[$iteration] == '\\' || $schoolRegex[$iteration] == '$' || $schoolRegex[$iteration] == '[' || $schoolRegex[$iteration] == ']')
                    {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                        $state = SCHAR;
                        $end_state = 1;
                        break;
                    }
                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SCHAR;
                        $end_state = 1;
                        break;
                    }
                    else
                        return NULL;
                    break;
            }
            $iteration++;
        }
        // if fsm end in one of these states return NULL because of error.
        //if($state == SNEG || $state == SAND || $state == SBRACKET || $state == SESCAPE || $state == SOR )
        //    return NULL;

        // FSM ended with bad end state, return NULL
        if($end_state == 0)
            return NULL;

        // return final php regex
        return $finalRegex;
    }
}
?>
