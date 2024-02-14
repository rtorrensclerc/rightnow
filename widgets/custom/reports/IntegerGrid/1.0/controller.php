<?php
namespace Custom\Widgets\reports;

class IntegerGrid extends \RightNow\Libraries\Widget\Base {

    private $page = 0;
    private $array_operator_filter = array ('=' => 1, '!=' => 2, '<' => 3, '<=' => 4, '>' => 5, '>=' => 6, 'between' => 7, 'like' => 7,  'list' => 10);

    function __construct($attrs)
    {
        parent::__construct($attrs);
        $this->page = getUrlParm('page');
    }

    function getData()
    {
      $this->data['js']['first_page'] = 1; 
      $this->CI->load->model('custom/widget/GridReport');
      if (!empty($this->data['attrs']['per_page']))
        $this->CI->GridReport->setPerPage($this->data['attrs']['per_page']);
      if (!empty($this->data['attrs']['page']))
        $this->CI->GridReport->setPage($this->data['attrs']['page']);
      else
        $this->CI->GridReport->setPage($this->page);

      if (!empty($this->data['attrs']['json_filters']))
        $this->buildFilters($this->data['attrs']['json_filters']);


      $result = $this->CI->GridReport->getReportData($this->data['attrs']['report_id']);

      if ($result === false)
      {
        $this->data['error']             = $this->CI->GridReport->getError();
        print_r($this->data['error']);
      }
      else
      {
        $this->data['error']                            = false;
        $temp_data                                      = array();
        $temp_data                                      = $this->replaceKeysForNumbers($result['data']);
        if ($this->data['attrs']['url_per_col'] != '' && $this->data['attrs']['col_id_url'] != '')
          $this->data['result']['headers']              = $this->reBuildArrayKeysWithUrl($result['keys'],$this->data['attrs']['url_per_col'],$this->data['attrs']['col_id_url']);
        else
          $this->data['result']['headers']              = $this->reBuildArrayKeys($result['keys']);
        $this->data['result']['data']                   = $this->specialFormat($temp_data, $this->data['result']['headers']);
        $this->data['total_pages']                      = $this->CI->GridReport->getTotalPages();
        $this->data['js']['total_pages']                = $this->data['total_pages'];
      }
      return parent::getData();
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
    * @param string en formato JSON con los filtros Ej: {[{"name": "filtro", "operator": "=", "type": "int", "value": "rg"}]}
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

          $this->CI->GridReport->addFilter($filter['name'],
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
    public function shouldShowHellip($pageNumber, $currentPage, $endPage) {
        return abs($pageNumber - $currentPage) == (($currentPage == 1 || $currentPage == $endPage) ? 3 : 2);
    }

    /**
     * Determines if the given page number should be displayed.
     * The pagination pattern followed here is:
     *     1 ... 4 5 6 ... 12.
     * if, for example, 5 is the current/clicked page out of a total of 12 pages.
     * @param integer $pageNumber Page number to check
     * @param integer $currentPage Current/clicked page number
     * @param integer $endPage Last page number in the pagination
     * @return bool True if the page number should be displayed.
     */
    public function shouldShowPageNumber($pageNumber, $currentPage, $endPage) {
        // Always display the first and last pages.
        // Display the next (or previous) two pages when you're on the first or last page.
        // Unless you're on other pages, in which case we want to display page numbers adjacent to the current page only.
        return $pageNumber == 1 || ($pageNumber == $endPage) || (abs($pageNumber - $currentPage) <= (($currentPage == 1 || $currentPage == $endPage) ? 2 : 1));
    }
    /**
     * Checks if the given page is the current page or not.
     * @param integer $pageNumber Arbitrary page number
     * @param integer $currentPage Current/clicked page number
     * @return bool True if the page numbers match
     */
    public function isCurrentPage($pageNumber, $currentPage) {
        return $pageNumber == $currentPage;
    }

}
