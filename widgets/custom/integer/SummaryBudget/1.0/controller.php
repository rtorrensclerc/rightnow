<?php

namespace Custom\Widgets\integer;

class SummaryBudget extends \RightNow\Libraries\Widget\Base
{

    public function __construct($attrs)
    {
        parent::__construct($attrs);
    }

    public function getData()
    {
      /**
      * TODO: Don Ronny, por favor ponga su poder aquí.
      */

      $this->CI->load->model('custom/ws/Budget');
      $result = $this->CI->Budget->getConsumoMes(41);

      if ($result === false)
      {
        $this->data['errors'] = $this->CI->Budget->getLastError();
      }
      else
      {
        $mesActual = date("m");
        $this->data['result']['month']            = $this->toChileanMonth($mesActual);
        $this->data['result']['limited_exceded']  = false;

        if (is_object($result->budget)) {

          $percented_consumed                             = ($result->budget->MaximunAmount)?(($result->total * 100) / $result->budget->MaximunAmount):0;
          $this->data['result']['consumed_percent_print'] = number_format((($percented_consumed)?$percented_consumed:0), 2, ',', '.').'%';
          $this->data['result']['consumed_percent']       = ($percented_consumed)?($percented_consumed<0)?$percented_consumed*-1:($percented_consumed > 100)?100:$percented_consumed:0;
          $this->data['result']['consumed_amount']        = number_format($result->total, 2, ',', '.');
          $this->data['result']['budget_month']           = number_format($result->budget->MaximunAmount, 2, ',', '.');
          $this->data['result']['negative']               = ($percented_consumed < 0)?true:false;

          $this->data['result']['total_refund']           = number_format($result->totalRefund, 2, ',', '.');
          $this->data['result']['total_reparation']       = number_format($result->totalReparation, 2, ',', '.');
          $this->data['result']['limited_exceded']        = ($result->total >= $result->budget->MaximunAmount) ? true : false;
        }
        else
        {
          $this->data['result']['month']                 .= '<br>"Sin Presupuesto"';
          $this->data['result']['consumed_percent_print'] = '0%';
          $this->data['result']['consumed_percent']       = 0;
          $this->data['result']['consumed_amount']        = number_format(0, 2, ',', '.');
          $this->data['result']['budget_month']           = number_format(0, 2, ',', '.');
          $this->data['result']['negative']               = false;

          $this->data['result']['total_refund']           = number_format(0, 2, ',', '.');
          $this->data['result']['total_reparation']       = number_format(0, 2, ',', '.');
        }
      }

      return parent::getData();
    }

    private function toChileanMonth($monthInNumber)
    {
      $meses = array('01'=>'Enero',
                     '02'=>'Febrero',
                     '03'=>'Marzo',
                     '04'=>'Abril',
                     '05'=>'Mayo',
                     '06'=>'Junio',
                     '07'=>'Julio',
                     '08'=>'Agosto',
                     '09'=>'Septiembre',
                     '10'=>'Octubre',
                     '11'=>'Noviembre',
                     '12'=>'Diciembre');

      if (array_key_exists($monthInNumber,$meses))
        return $meses[$monthInNumber];
      else
        return 0;
    }
}
