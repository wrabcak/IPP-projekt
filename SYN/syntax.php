<?php

/*
 * Syntax class for add html tags to output text.
 */
Class Syntax
{

    private $db; // database with formats
    private $input; // input text
    private $newLine; // if newline is 1 add html tag <br> on every new line of input text
    private $tags=array(); // array of tags

    function __construct($db,$input,$newLine)
    {
        $this->db = $db;
        $this->input = $input;
        $this->newLine = $newLine;
    }

    /*
     * Method for apply tags to input text.
     * Return input text also with html tags.
     */
    public function apply()
    {
        $match  = array();
        foreach($this->db as $element)
        {
            // find all strings that match with regex in format database
            preg_match_all('/'.$element['Regex'].'/',$this->input,$match,PREG_OFFSET_CAPTURE);
            foreach($match[0] as $occurred)
            {

                if($occurred[0] == "")
                {
                    unset($occurred[1]);
                    continue;
                }

                $begin = $occurred[1]; // first position of match
                $end = strlen($occurred[0]) + $begin; // last position of match
                foreach($element['Command'] as $tag) 
                {
                    if($tag[0] == 'underline')
                    {
                        if(!array_key_exists($begin,$this->tags)) // if exists position in tags array add new tag to selected position, dont create new position with this tag
                            $this->tags[$begin] = '<u>';
                        else
                            $this->tags[$begin] = $this->tags[$begin] .'<u>';

                        if(!array_key_exists($end,$this->tags)) // same as above with end tags
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
        ksort($this->tags); // sort array by the key from the smallest to the laegests number

        $this->tagsReversed = array_reverse($this->tags,true); // reverse tags array

        // adding tags to the input text from end
        foreach($this->tagsReversed as $index => $value)
        {
            // add tag to the selected position to input text
            $this->input =  substr_replace($this->input, $value, $index, 0);
        }

        // if was selected also --br argument add also '<br>' tag
        if($this->newLine == true)
        {
            $this->addNewLine();
        }

    }

    /*
     * This method add '<br>' tag to input text.
     */
    private function addNewLine()
    {
        // find newlines
        preg_match_all('/\n/',$this->input,$match,PREG_OFFSET_CAPTURE);
        $match = $match[0];

        // reverse array with positions
        $match = array_reverse($match);
        // add br tags to input text
        foreach($match as $element)
        {
            $position = $element[1];
            $this->input =  substr_replace($this->input, '<br />', $position, 0);
        }
    }

    /*
     *  This method return output text with html tags.
     */
    public function getOutput()
    {
        return $this->input;
    }

}

?>
