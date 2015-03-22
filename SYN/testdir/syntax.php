<?php

Class Syntax
{

    private $db;
    private $input;
    private $newLine;
    private $tags=array();

    function __construct($db,$input,$newLine)
    {
        $this->db = $db;
        $this->input = $input;
        $this->newLine = $newLine;
    }

    function apply()
    {
        $match  = array();
        foreach($this->db as $element)
        {
            preg_match_all('/'.$element['Regex'].'/',$this->input,$match,PREG_OFFSET_CAPTURE);
            foreach($match[0] as $occurred)
            {

                if($occurred[0] == "")
                {
                    unset($occurred[1]);
                    continue;
                }

                $begin = $occurred[1];
                $end = strlen($occurred[0]) + $begin;
                foreach($element['Command'] as $tag)
                {
                    if($tag[0] == 'underline')
                    {
                        if(!array_key_exists($begin,$this->tags))
                            $this->tags[$begin] = '<u>';
                        else
                            $this->tags[$begin] = $this->tags[$begin] .'<u>';

                        if(!array_key_exists($end,$this->tags))
                            $this->tags[$end] = '</u>';
                        else
                            $this->tags[$end] = '</u>' . $this->tags[$end];
                    }

                    elseif($tag[0] == 'bold')
                    {
                        if(!array_key_exists($begin,$this->tags))
                            $this->tags[$begin] = '<b>';
                        else
                            $this->tags[$begin] = $this->tags[$begin] . '<b>';

                        if(!array_key_exists($end,$this->tags))
                            $this->tags[$end] = '</b>';
                        else
                            $this->tags[$end] = '</b>'. $this->tags[$end];
                    }

                    elseif($tag[0] == 'italic')
                    {
                        if(!array_key_exists($begin,$this->tags))
                            $this->tags[$begin] = '<i>';
                        else
                            $this->tags[$begin] = $this->tags[$begin] . '<i>';

                        if(!array_key_exists($end,$this->tags))
                            $this->tags[$end] = '</i>';
                        else
                            $this->tags[$end] = '</i>'. $this->tags[$end];
                    }
                    elseif($tag[0] == 'teletype')
                    {
                        if(!array_key_exists($begin,$this->tags))
                            $this->tags[$begin] = '<tt>';
                        else
                            $this->tags[$begin] = $this->tags[$begin] . '<tt>';

                        if(!array_key_exists($end,$this->tags))
                            $this->tags[$end] = '</tt>';
                        else
                            $this->tags[$end] = '</tt>' . $this->tags[$end];
                    }
                    elseif($tag[0] == 'size')
                    {
                        if(!array_key_exists($begin,$this->tags))
                            $this->tags[$begin] = '<font size='. $tag[1] . '>';
                        else
                            $this->tags[$begin] = $this->tags[$begin] . '<font size='. $tag[1] . '>';

                        if(!array_key_exists($end,$this->tags))
                            $this->tags[$end] = '</font>';
                        else
                            $this->tags[$end] = '</font>' . $this->tags[$end];
                    }
                    elseif($tag[0] == 'color')
                    {
                        if(!array_key_exists($begin,$this->tags))
                            $this->tags[$begin] = '<font color=#'. $tag[1] . '>';
                        else
                            $this->tags[$begin] = $this->tags[$begin] . '<font color=#'. $tag[1] . '>';

                        if(!array_key_exists($end,$this->tags))
                            $this->tags[$end] = '</font>';
                        else
                            $this->tags[$end] = '</font>' . $this->tags[$end];
                    }
                }
            }
        }
        ksort($this->tags);

        $this->tagsReversed = array_reverse($this->tags,true);
        foreach($this->tagsReversed as $index => $value)
        {
            $this->input =  substr_replace($this->input, $value, $index, 0);
        }

        if($this->newLine == true)
        {
            $this->addNewLine();
        }

    }

    private function addNewLine()
    {
        preg_match_all('/\n/',$this->input,$match,PREG_OFFSET_CAPTURE);
        $match = $match[0];

        $match = array_reverse($match);
        foreach($match as $element)
        {
            $position = $element[1];
            $this->input =  substr_replace($this->input, '<br />', $position, 0);
        }
    }

    public function getOutput()
    {
        return $this->input;
    }

}

?>
