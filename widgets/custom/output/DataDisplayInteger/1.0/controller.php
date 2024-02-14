<?php
namespace Custom\Widgets\output;

class DataDisplayInteger extends \RightNow\Libraries\Widget\Base 
{
  function __construct($attrs) 
  {
    parent::__construct($attrs);
    
    $this->CI->load->model("custom/IncidentGeneral");
    $this->CI->load->helper("utils_helper");
  }

  function getData() 
  {
    $i_id = (int) getUrlParm("i_id");
    if($i_id > 0)
    {
      $Incident = $this->CI->IncidentGeneral->get($i_id);
      if($Incident === FALSE)
      {
        echo "No se pudo obtener el incidente.";
      }
      else
      {
        $status = FALSE;
        $target = "$" .$this->data["attrs"]["name"];

        if(strpos($target, "Status") !== FALSE)
        {
          $target .= ".ID";
          $status = TRUE;
        }

        
        $str_target = str_replace(".", "->" ,$target);
        $value = NULL;
        eval("\$value = $str_target;");


        if(is_object($value))
          $value = $value->LookupName;

        if($status === TRUE)
          $parsed = (getStatusIncidentid($value) !== FALSE) ? getStatusIncidentid($value) : FALSE;
        else
          $parsed = $value;

        if($parsed === FALSE && $status === TRUE)
        {
          $target2 = "$" .$this->data["attrs"]["name"];
          $str_target2 = str_replace(".", "->" ,$target2);
          eval("\$value = $str_target2;");
          $parsed = $value->LookupName;
        }

        $this->data["js"]["value"] = $parsed;
      }
    }
    return parent::getData();
  }
}