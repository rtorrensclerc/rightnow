<?php

namespace Custom\Controllers;
require_once(get_cfg_var('doc_root').'/include/ConnectPHP/Connect_init.phph');
use RightNow\Connect\v1_2 as RNCPHP;

class AjaxReports extends \RightNow\Controllers\Base
{
    //This is the constructor for the custom controller. Do not modify anything within
    //this function.
    public $error = '';
    private $array_operator_filter = array ('=' => 1, '!=' => 2, '<' => 3, '<=' => 4, '>' => 5, '>=' => 6, 'between' => 7, 'like' => 7,  'list' => 10);
    
    function __construct()
    {
        parent::__construct();


    }
    function resultPage()
    {
        if ($this->input->post('page') > 1)
            $page = $this->input->post('page') - 1;
        else
            $page = 0;
        $perPage = $this->input->post('per_page');
        $reportID = $this->input->post('report_id');
        $filters = $this->input->post('filters');
        $urlPerCol = $this->input->post('url_per_col');
        $colIdUrl = $this->input->post('col_id_url');

        if (!empty($perPage))
            $this->model('custom/widget/GridReport')->setPerPage($perPage);
        if (!empty($page))
            $this->model('custom/widget/GridReport')->setPage($page);
        if (!empty($filters))
          $this->buildFilters($filters);
        if (!empty($reportID))
            $result = $this->model('custom/widget/GridReport')->getReportData($reportID);
        if (!empty($result)){
            $temp_data                       = $this->replaceKeysForNumbers($result['data']);
            if (!empty($urlPerCol) && !empty($colIdUrl))
              $jsonResult['result']['headers']              = $this->reBuildArrayKeysWithUrl($result['keys'],$urlPerCol,$colIdUrl);
            else
              $jsonResult['result']['headers']              = $this->reBuildArrayKeys($result['keys']);
            $jsonResult['result']['data']    = $this->specialFormat($temp_data, $jsonResult['result']['headers']);
            echo json_encode($jsonResult);
        }else {
            $jsonResult['error']             = $this->model('custom/widget/GridReport')->getError();
            echo json_encode($jsonResult);
        }
    }
    /**
    * Formatea la Data a información legible para la vista
    *
    * @return array data
    * @param array data
    */
    private function specialFormat($a_data, $a_keys)
    {
      $a_final_data = array();
      for ($i = 0; $i < count($a_data); $i++)
      {
        for ($j = 0; $j < count($a_data[$i]); $j++)
        {
          if ($a_keys[$j]['data_type'] == 4  and !empty($a_data[$i][$j])) //FECHA
          {
            $date           = str_replace("'", "", $a_data[$i][$j]);
            $a_data[$i][$j] = $this->toChileanDate($date);
          }

        }
        $a_final_data[] = $a_data[$i];
      }
      return $a_final_data;
    }

    /**
    * agrega filtros a la instancia del modelo IntegerGrid
    *
    * @return void
    * @param string en formato json con los filtros Ej: {[{"name": "filtro", "operator": "=", "type": "int", "value": "rg"}]}
    */
    private function buildFilters($jsonFilters)
    {
      $a_json = json_decode($jsonFilters, true);

      if (is_array($a_json))
      {
        foreach ($a_json as $filter)
        {
          if ($filter['type'] == 'DATE')
          {
            $filter['value'] = $this->dateToRNdate($filter['value']);
          }

          if ($filter['operator'] == 'list')
            $a_values = explode( ',', $filter['value']);
          else
            $a_values = array($filter['value']);

          $this->model('custom/widget/GridReport')->addFilter($filter['name'],
                                           $this->array_operator_filter[$filter['operator']],
                                           $a_values
                                          );
        }
      }
    }

    /**
    * convierte una fecha a parametro conocido por AnalyticsReports de Righnow
    *
    * @return string fecha convertida a formarto entendible por RighNow
    * @param string fecha en formato Y-m-d H:i:s
    */
    private function dateToRNdate($date)
    {
      $fecha = strtotime($date);
      $fecha = date("Ymd\TH:i:s\Z", $fecha );
      return $fecha;
    }

    /**
    * cambia los arrays de keys en indexados en vez de asociativos
    *
    * @return array data con keys en numbers
    * @param array data extraida del reporte
    */
    private function replaceKeysForNumbers($array_data)
    {
      $a_result = array();
      if(is_array($array_data) and (count($array_data) > 0))
      {
        $a_result = array();
        foreach ($array_data as $value) {
          $val = array();
          foreach ($value as $row)
            $val[] = $row;
          $a_result[] = $val;
        }
      }
      return $a_result;
    }
    /**
    * Reestructura el array de llaves, para enviarselo a la vista.
    *
    * @return array keys
    * @param array keys
    */
    private function reBuildArrayKeys($array_keys)
    {
      $keys = array();
      $i = 1;
      foreach ($array_keys as $value) {
        $key_temp['heading'] = $value->Heading;
        $key_temp['data_type'] = $value->DataType->ID;
        $key_temp['data_type_2'] = $value->DataType->LookupName;
        $key_temp['col_id'] = $i;
        $keys[] = $key_temp;
        $i++;
      }
      return $keys;
    }
     /**
    * Reestructura el array de llaves, para enviarselo a la vista, pero con URL's.
    *
    * @return array keys
    * @param array keys
    */
    private function reBuildArrayKeysWithUrl($array_keys,$urlPerCol,$colIdUrl)
    {
      $keys = array();
      $i = 1;

      foreach ($array_keys as $value) {
        if ($colIdUrl == $i){
          $key_temp['heading'] = $value->Heading;
          $key_temp['data_type'] = 99;
          $key_temp['data_type_2'] = "URL";
          $key_temp['col_id'] = $i;
          $keys[] = $key_temp;
          $i++;
        }
        else {
          $key_temp['heading'] = $value->Heading;
          $key_temp['data_type'] = $value->DataType->ID;
          $key_temp['data_type_2'] = $value->DataType->LookupName;
          $key_temp['col_id'] = $i;
          $keys[] = $key_temp;
          $i++;
        }
      }
      return $keys;
    }
    /**
    * convierte una fecha a parametro usado en chile
    *
    * @return string fecha convertida a formato usado en chile
    * @param string fecha en formato Y-m-d H:i:s
    */
    private function toChileanDate($date)
    {
      $fecha = strtotime($date);
      $fecha = date("d/m/Y H:i:s", $fecha );
      return $fecha;
    }

}
