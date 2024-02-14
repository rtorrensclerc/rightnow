<?php
namespace Custom\Widgets\charts;

class BarChart extends \RightNow\Libraries\Widget\Base
{
    // Varibale que almacena los errores surgidos
    public $msgError = "";

    public function __construct($attrs)
    {
        parent::__construct($attrs);
    }

    public function getData()
    {
        return parent::getData();
    }
  
}
