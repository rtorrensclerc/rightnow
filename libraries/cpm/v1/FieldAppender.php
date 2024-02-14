<?php
/* 
 * Given a CPHP object, appends a string to the specified field and saves the
 * object.
 */
namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

class FieldAppender
{
    protected $object;
    
    function __construct($object)
    {
        $this->object = $object;
    }
    
    function Append($field, $string)
    {
        $string = " - " . $string;
        if (is_string($this->object->{$field})
            && !$this->endsWith($this->object->{$field}, $string))
        {
            /* If field is a string, and hasn't already been appended */
            $this->object->{$field} .= $string;
            $this->object->save();
        }
    }
    
    protected static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }
}