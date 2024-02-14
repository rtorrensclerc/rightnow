<?php
namespace Custom\Widgets\Info;
use RightNow\Connect\v1_2 as RNCPHP;
class trackingdetail extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
        $this->setAjaxHandlers(array(
            'getTrx_ajax_endpoint' => array(
                'method'    => 'handle_getTrx_ajax_endpoint',
                'clickstream' => 'getTrx_ajax_endpoint'
            )
        ));
    }
    function getData() {

        $this->data['id']=$this->data['attrs']['id'];
        return parent::getData();
    }

     /**
     * Handles the handle_getTrx_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_getTrx_ajax_endpoint($params) {
        // Perform AJAX-handling here...

        $report_id             = 101870;
        $filter_value          = $this->data['attrs']['id'];
    
        //logMessage("  filter_value " .     $filter_value);
        $status_filter         = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name   = 'resource_id';
        $status_filter->Values = array($filter_value);
        $filters               = new RNCPHP\AnalyticsReportSearchFilterArray;
        $filters[]             = $status_filter;
        $ar                    = RNCPHP\AnalyticsReport::fetch($report_id);
        $arr                   = $ar->run( 0, $filters );
  
        // Inicio - ALTERNATIVA ENCRIPTADA
  
        $ingresado=false;
        $supervision=false;
        $preparando=false;
        $despachado=false;
        $entregado=false;
        $derivado=false;
        $cancelado=false;
        $asigna=false; 
        $ruta=false;
        $trabajo=false;
        $Finalizada=false;
        for ( $i = $arr->count(); $i--; )
        {
          $row = $arr->next();

          if($row['disp_id']==25)
          {
          switch($row['STATUS_O'])
          {
                case 1:
                case 4:    
                case 129:
                    if(!$ingresado)
                    {
                        $row['STATUS_DESC_O']="Ingresado";
                        $array_response['Tickets'][] = $row;
                        $ingresado=true;
                    }

                    break;
                case 119:
                        if(!$derivado)
                        {
                            $row['STATUS_DESC_O']="Derivado a asistencia remota";
                            $array_response['Tickets'][] = $row;
                            $derivado=true;
                        }
    
                        break;

                case 118:
                
                    if(!$asigna)
                    {
                        $row1['EVENT_DATE_O']="SOLICITUD DE SERVICIO";
                        $row1['STATUS_DESC_O']="";
                        $array_response['Tickets'][]= $row1;
                        $row['STATUS_DESC_O']="Técnico Asignado";
                        $array_response['Tickets'][] = $row;
                        //$asigna=true;
                    }

                    break;  
                case 163:
            
                    if(!$ruta)
                    {
                        $row['STATUS_DESC_O']="Técnico En ruta";
                        $array_response['Tickets'][] = $row;
                        //$ruta=true;
                    }

                    break;
                case 165:
        
                    if(!$trabajo)
                    {
                        $row['STATUS_DESC_O']="Técnico Trabajando";
                        $array_response['Tickets'][] = $row;
                        //$trabajo=true;
                    }

                    break;
                case 111:
                    if(!$trabajo)
                    {
                        $row['STATUS_DESC_O']="Repuestos Despachados";
                        $array_response['Tickets'][] = $row;
                        $trabajo=true;
                    }
                    break;
                case 112:
                 
                        $row['STATUS_DESC_O']="Repuestos Entregado";
                        $array_response['Tickets'][] = $row;
                        $trabajo=true;
                 
                    
                    break;
                case 14:
                case 176:
                case 177:
                case 178:
               
                case 113:
                case 157:
                case 156:
                    if(!$preparando)
                    {
                      
                        $row1['EVENT_DATE_O']="SOLICITUD DE REPUESTOS";
                        $row1['STATUS_DESC_O']="";
                        $array_response['Tickets'][]= $row1;

                        $row['STATUS_DESC_O']="Preparando Repuestos";
                        
                        $array_response['Tickets'][] = $row;
                        $preparando=true;
                    }

                    break;
                case 175:
                    if(!$supervision)
                    {
                        if ($asigna)
                        {
                            $row['STATUS_DESC_O']="Repuesto en Supervisión";
                        }
                        else
                        {
                            $row['STATUS_DESC_O']="Supervisión";
                        }
                        $array_response['Tickets'][] = $row;
                        $supervision=true;
                    }
                    break;    
               /* case 176:
                case 177:
                case 178:
                case 111:
                case 113:
                case 157:
                case 156:
                    if(!$preparando)
                    {
                        $row['STATUS_DESC_O']="Preparando";
                        $array_response['Tickets'][] = $row;
                        $preparando=true;
                    }
                    break;    */
                case 140:
                case 195:
                    if(!$despachado)
                    {
                        $row['STATUS_DESC_O']="Despachado";
                        $array_response['Tickets'][] = $row;
                        $despachado=true;
                    }
                    break;   
                case 2:
                case 112:
                    if(!$entregado)
                    {
                        $row['STATUS_DESC_O']="Entregado";
                        $array_response['Tickets'][] = $row;
                        $entregado=true;
                    }  
                    break;
                case 147:
                case 148:
                case 149:
                case 104:
                    if(!$cancelado)
                    {
                        $row['STATUS_DESC_O']="Cancelado";
                        $array_response['Tickets'][] = $row;
                        $cancelado=true;
                    }  
                    break;
                case 166:
                    if(!$Finalizada)
                    {
                        $row['STATUS_DESC_O']="Visita Finalizada";
                        $array_response['Tickets'][] = $row;
                        $Finalizada=true;
                        $i=0;
                    }  
                    break;
                default:
                   break;
          }
        }
        else
        {
            switch($row['STATUS_O'])
            {
                  case 1:
                  case 4:    
                  case 129:
                      if(!$ingresado)
                      {
                          $row['STATUS_DESC_O']="Ingresado";
                          $array_response['Tickets'][] = $row;
                          $ingresado=true;
                      }
  
                      break;
                  case 111:
                        if(!$despachado)
                        {
                            $row['STATUS_DESC_O']="Despachado" ;
                            $array_response['Tickets'][] = $row;
                            $despachado=true;
                        }
                      break;
                  case 112:
                   
                          $row['STATUS_DESC_O']="Entregado";
                          $array_response['Tickets'][] = $row;
                          $trabajo=true;
                   
                      
                      break;
                  case 14:
                  case 176:
                  case 177:
                  case 178:
                 
                  case 113:
                  case 157:
                  case 156:
                      if(!$preparando)
                      {
                          $row['STATUS_DESC_O']="Preparando";
                          
                          $array_response['Tickets'][] = $row;
                          $preparando=true;
                      }
                      break;
                  case 175:
                
                        $row['STATUS_DESC_O']="Supervisión";
                        $array_response['Tickets'][] = $row;
                      break;    
                 /* case 176:
                  case 177:
                  case 178:
                  case 111:
                  case 113:
                  case 157:
                  case 156:
                      if(!$preparando)
                      {
                          $row['STATUS_DESC_O']="Preparando";
                          $array_response['Tickets'][] = $row;
                          $preparando=true;
                      }
                      break;    */
                  case 140:
                  case 195:
                      if(!$despachado)
                      {
                          $row['STATUS_DESC_O']="Despachado";
                          $array_response['Tickets'][] = $row;
                          $despachado=true;
                      }
                      break;   
                  case 2:
                    
                          $row['STATUS_DESC_O']="Cerrado";
                          $array_response['Tickets'][] = $row;
                      break;
                  case 112:
                      if(!$entregado)
                      {
                          $row['STATUS_DESC_O']="Entregado";
                          $array_response['Tickets'][] = $row;
                          $entregado=true;
                      }  
                      break;
                  case 147:
                  case 148:
                  case 149:
                  case 104:
                      if(!$cancelado)
                      {
                          $row['STATUS_DESC_O']="Cancelado";
                          $array_response['Tickets'][] = $row;
                          $cancelado=true;
                      }  
                      break;
                  default:
                     break;
            }

        }
          
         
        }
        $response = json_encode($array_response);
        //$this->sendResponse(json_encode($array_response));
      
        $Trx=json_decode($response);
                
        $params['Trx_data']=$Trx->Tickets;
        
        echo json_encode($params);
  
        // FIN - Alternativa Encritpada

    }  
}