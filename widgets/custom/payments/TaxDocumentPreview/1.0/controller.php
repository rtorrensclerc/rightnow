<?php
namespace Custom\Widgets\payments;

class TaxDocumentPreview extends \RightNow\Libraries\Widget\Base
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
