<?php

define('SSTART',0);
define('SESCAPE',1);
define('SCHAR',2);
define('SDOT',3);
define('SITER',4);
define('SOR',5);
define('SBRACKET',6);
define('SBRACLOSE',7);
define('SPITER',8);
define('SNOT',9);

class Parser
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

        $regex = $this->parseFormatRegex($match[1]);

        if($regex != NULL)
        {
            array_push($this->arrayOfCommands,array('Regex'=>$regex,'Command'=>$this->parseFormatCommand($match[2])));
        }
        else
        {
            exit(ERR_FORMAT_LINE);
            throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);
        }
    }

    public function getDB()
    {
        return $this->arrayOfCommands;
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
            if($splitCommand[0] == 'size')
            {
                if(!($splitCommand[1] <= 7 && $splitCommand[1] >= 1))
                    throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);
            }
            if($splitCommand[0] == 'color')
            {
                if(!(ctype_xdigit($splitCommand[1])))
                    throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);
            }
            array_push($parsedCommand, $splitCommand);
        }
        return $parsedCommand;
    }

    private function parseFormatRegex($schoolRegex)
    {
        $finalRegex = "";
        $state = SSTART;
        $iteration = 0;
        while($iteration < strlen($schoolRegex))
        {
            switch($state)
            {
                case SSTART:
                case SBRACKET:
                case SOR:
                case SDOT:
                    if ($schoolRegex[$iteration] == '.' || $schoolRegex[$iteration] == '|' || $schoolRegex[$iteration] == '*' || $schoolRegex[$iteration] == '+' || $schoolRegex[$iteration] == ')')
                    {
                        return null;
                    }
                    elseif($schoolRegex[$iteration] == '!')
                    {
                        $finalRegex = $finalRegex . '[^';
                        $state = SNOT;
                    }

                    elseif($schoolRegex[$iteration] == '%')
                    {
                        $finalRegex = $finalRegex . '[';
                        $state = SESCAPE;
                    }

                    elseif($schoolRegex[$iteration] == '(')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACKET;
                    }
                    elseif($schoolRegex[$iteration] == '?' || $schoolRegex[$iteration] == '{' || $schoolRegex[$iteration] == '}' || $schoolRegex[$iteration] == '/' || $schoolRegex[$iteration] == '^' || $schoolRegex[$iteration] == '\\' || $schoolRegex[$iteration] == '$' || $schoolRegex[$iteration] == '[' || $schoolRegex[$iteration] == ']')
                    {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                        $state = SCHAR;
                    }
                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SCHAR;
                    }
                    else
                        return NULL;
                    break;

                case SCHAR:
                case SBRACLOSE:
                    if ($schoolRegex[$iteration] == '.')
                    {
                        $state = SDOT;
                    }

                    elseif ($schoolRegex[$iteration] == '%')
                    {
                        $finalRegex = $finalRegex . '[';
                        $state = SESCAPE;
                    }
                    elseif ($schoolRegex[$iteration] == '!')
                    {
                        $finalRegex = $finalRegex . '[^';
                        $state = SNOT;
                    }

                    elseif ($schoolRegex[$iteration] == '(')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACKET;
                    }
                    elseif ($schoolRegex[$iteration] == ')')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACLOSE;
                    }
                    elseif ($schoolRegex[$iteration] == '+')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SPITER;
                    }
                    elseif ($schoolRegex[$iteration] == '*')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SITER;
                    }
                    elseif ($schoolRegex[$iteration] == '|')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SITER;
                    }
                    elseif($schoolRegex[$iteration] == '?' || $schoolRegex[$iteration] == '{' || $schoolRegex[$iteration] == '}' || $schoolRegex[$iteration] == '/' || $schoolRegex[$iteration] == '^' || $schoolRegex[$iteration] == '\\' || $schoolRegex[$iteration] == '$' || $schoolRegex[$iteration] == '[' || $schoolRegex[$iteration] == ']')
                    {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                        $state = SCHAR;
                    }
                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SCHAR;
                    }
                    else
                        return NULL;
                    break;

                case SNOT:
                    if ($schoolRegex[$iteration] == '.' || $schoolRegex[$iteration] == '|' || $schoolRegex[$iteration] == '*' || $schoolRegex[$iteration] == '+' || $schoolRegex[$iteration] == ')')
                    {
                        return null;
                    }

                    elseif ($schoolRegex[$iteration] == '%')
                    {
                        $state = SESCAPE;
                    }

                    elseif ($schoolRegex[$iteration] == '.' || $schoolRegex[$iteration] == '*' || $schoolRegex[$iteration] == '+' || $schoolRegex[$iteration] == '!' || $schoolRegex[$iteration] == '|' || $schoolRegex[$iteration] == '(' || $schoolRegex[$iteration] == ')')
                    {
                        return NULL;
                    }

                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration] . ']';
                        $state = SCHAR;
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
                   $state = SCHAR;
                   $finalRegex = $finalRegex . ']';
                   break;

                case SITER:
                    if ($schoolRegex[$iteration] == '.')
                    {
                        $state = SDOT;
                    }

                    elseif ($schoolRegex[$iteration] == '%')
                    {
                        $finalRegex = $finalRegex . '[';
                        $state = SESCAPE;
                    }
                    elseif ($schoolRegex[$iteration] == '!')
                    {
                        $finalRegex = $finalRegex . '[^';
                        $state = SNOT;
                    }

                    elseif ($schoolRegex[$iteration] == '(')
                    {
                        $finalRegex = $finalRegex . $school[$iteration];
                        $state = SBRACKET;
                    }
                    elseif ($schoolRegex[$iteration] == ')')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACLOSE;
                    }
                    elseif ($schoolRegex[$iteration] == '+')
                    {
                        $finalRegex[strlen($finalRegex)-1] = '*';
                        $state = SPITER;
                    }
                    elseif ($schoolRegex[$iteration] == '*')
                    {
                        $finalRegex[strlen($finalRegex)-1] = '*';
                        $state = SPITER;
                    }
                    elseif ($schoolRegex[$iteration] == '|')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SITER;
                    }
                    elseif($schoolRegex[$iteration] == '?' || $schoolRegex[$iteration] == '{' || $schoolRegex[$iteration] == '}' || $schoolRegex[$iteration] == '/' || $schoolRegex[$iteration] == '^' || $schoolRegex[$iteration] == '\\' || $schoolRegex[$iteration] == '$' || $schoolRegex[$iteration] == '[' || $schoolRegex[$iteration] == ']')
                    {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                        $state = SCHAR;
                        break;
                    }
                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SCHAR;
                        break;
                    }
                    else
                        return NULL;
                    break;

                case SPITER:
                    if ($schoolRegex[$iteration] == '.')
                    {
                        $state = SDOT;
                    }

                    elseif ($schoolRegex[$iteration] == '%')
                    {
                        $finalRegex = $finalRegex . '[';
                        $state = SESCAPE;
                    }
                    elseif ($schoolRegex[$iteration] == '!')
                    {
                        $finalRegex = $finalRegex . '[^';
                        $state = SNOT;
                    }

                    elseif ($schoolRegex[$iteration] == '(')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACKET;
                    }
                    elseif ($schoolRegex[$iteration] == ')')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SBRACLOSE;
                    }
                    elseif ($schoolRegex[$iteration] == '+')
                    {
                        $state = SPITER;
                    }
                    elseif ($schoolRegex[$iteration] == '*')
                    {
                        $finalRegex[strlen($finalRegex)-1] = '*';
                        $state = SPITER;
                    }
                    elseif ($schoolRegex[$iteration] == '|')
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SITER;
                    }
                    elseif($schoolRegex[$iteration] == '?' || $schoolRegex[$iteration] == '{' || $schoolRegex[$iteration] == '}' || $schoolRegex[$iteration] == '/' || $schoolRegex[$iteration] == '^' || $schoolRegex[$iteration] == '\\' || $schoolRegex[$iteration] == '$' || $schoolRegex[$iteration] == '[' || $schoolRegex[$iteration] == ']')
                    {
                        $finalRegex = $finalRegex . '\\' . $schoolRegex[$iteration];
                        $state = SCHAR;
                        break;
                    }
                    elseif(ord($schoolRegex[$iteration])>=32)
                    {
                        $finalRegex = $finalRegex . $schoolRegex[$iteration];
                        $state = SCHAR;
                        break;
                    }
                    else
                        return NULL;
                    break;
            }
            $iteration++;
        }//cyklus
        if($state == SNOT || $state == SDOT || $state == SBRACKET || $state == SESCAPE || $state == SOR )
            return NULL;

        return $finalRegex;
    }
}
?>
