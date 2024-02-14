<?php
namespace Custom\Models\widget;
use RightNow\Connect\v1_3 as RNCPHP;


class GridReport extends  \RightNow\Models\Base
{
    private $error = '';
    private $filters = null;
    private $per_page = 0;
    private $page = 0;
    private $total_pages = 0;

    function __construct()
    {
        parent::__construct();
    }

    /**
    * Agrega Filtros al array filters
    *
    * @return void
    * @param string nombre, operador y valor
    */
    public function addFilter($name, $operator, $value)
    {
      if (!($this->filters instanceof RNCPHP\AnalyticsReportSearchFilterArray))
        $this->filters = new RNCPHP\AnalyticsReportSearchFilterArray;

      $status_filter               = new RNCPHP\AnalyticsReportSearchFilter;
      $status_filter->Name         = $name;

      // $status_filter->Values       = array($value);
      $status_filter->Values       = array();
      if (is_array($value))
      {
        foreach ($value as $val) {
          $status_filter->Values[] = $val;
        }
      }

      $status_filter->Operator     = new RNCPHP\NamedIDOptList();
      $status_filter->Operator->ID = $operator; //4 es igual a menor, 5 es igual a mayor.
      $this->filters[]             = $status_filter;

    }

    /**
    * Ejecutar query analytics reports
    *
    * @return array filas resultantes del reporte
    * @param int ID de reporte
    */
    public function getReportData($id)
    {
      try{
        $id_report = $id;
        $ar = RNCPHP\AnalyticsReport::fetch($id_report);

        /*
        echo "<pre>";
        print_r($ar->Filters[0]->Operator->ID);
        echo "</pre>";
        */
        
        $arr_total = $ar->run(0, $this->filters);
        $this->total_pages = ($arr_total->count()) / $this->per_page;
        /*
        echo "Count ". $arr_total->count() ."<br>";
        echo "Count ". $this->per_page     ."<br>";
        echo "Count ". $this->total_pages  ."<br>";
        */
        $realPage = $this->page * $this->per_page;
        $arr = $ar->run($realPage, $this->filters, $this->per_page);
        $keys = array();
        $keys = $ar->Columns; // Columnas del Reporte
        for ( $ii = $arr->count(); $ii--; )
        {
            $row = $arr->next();
            $a_resp[] = $row;
        }

        $a_final_resp['keys'] = $keys;
        $a_final_resp['data'] = $a_resp;
        return $a_final_resp;
      }
      catch (RNCPHP\ConnectAPIError $err){
  			$this->error = $err->getMessage();
  			return false;
		  }
    }

    /**
    * setear valor de cantidad valores por página al objeto
    *
    * @return void
    * @param int value
    */
    public function setPerPage($value)
    {
      $this->per_page = $value;
    }

    /**
    * setear valor de página al objeto
    *
    * @return void
    * @param int value
    */
    public function setPage($value)
    {
      $this->page = $value;
    }

    /**
    * obtener valor de página al objeto
    *
    * @return int total de páginas desde 0-N
    * @param void
    */
    public function getTotalPages()
    {
      return round($this->total_pages);
    }

    /**
    * obtener cadena de error
    *
    * @return string cadena de error
    * @param void
    */
    public function getError()
    {
      return $this->error;
    }

}
