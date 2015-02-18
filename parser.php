<?php

class Parser
{
    private  $availableCommands = array('bold','italic','underline','teletype','size','color');

    public $arrayOfCommands = array();

    const SSTART = 0;
    const SESCAPE = 1;
    const SCHAR = 2;
    const SDOT = 3;
    const SITER = 4;
    const SOR = 5;
    const SBRACKET = 6;
    const SBRACLOSE = 7;
    CONST SPITER = 8;
    CONST SNOT = 9;

    public function addFormatLine($syntaxLine)
    {

        if(trim($syntaxLine) == '')
            exit(ERR_OK);

        preg_match('/^([\S ]+)\t+([\S\t ]+)$/', $syntaxLine, $match);
        if(count($match) == 0)
            throw new Exception('ERROR WRONG FORMAT LINE!',ERR_FORMAT_LINE);

        array_push($this->arrayOfCommands,array('Regex'=>$this->parseFormatRegex($match[1]),'Commnand'=>$this->parseFormatCommand($match[2])));

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
                    elseif ($school[$iteration] == '+')
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
                       $finalRegex = $finalRegex . '\\t\\r\\n\\f\\v';
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
    } //funkcia
}//trieda
?>
