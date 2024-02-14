<?php

namespace Custom\Widgets\integer;

class SummaryHH extends \RightNow\Libraries\Widget\Base
{
    public function __construct($attrs)
    {
        parent::__construct($attrs);
    }

    public function getData()
    {
      $this->CI->load->model('custom/ws/HH');
      $idHH = $this->data['attrs']['hh_id'];

      $result = $this->CI->HH->getInfoReparation($idHH);

      if ($result === false)
      {
        $this->data['errors'] = $this->CI->HH->getLastError();
      }
      else
      {
        $this->data['info_hh'] = $result;
      }

      return parent::getData();
    }
}
