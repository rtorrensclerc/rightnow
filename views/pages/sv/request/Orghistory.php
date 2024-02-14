<? if(!\RightNow\Utils\URL::getParameter('org')) header("Location: ".\RightNow\Utils\URL::addParameter(\RightNow\Utils\URL::getOriginalUrl(),'org','2')); ?>
<rn:widget path="custom/redirect/RedirectBlocked" />
<rn:meta title="Todas las Solicitudes" template="2020.php" clickstream="form_request" login_required="true"/>




<!--div class="content-body">
<rn:widget path="custom/reports/OrgHistory" report_id="102068" />
</div-->



<div class="message">
		#rn:msg:CUSTOM_MSG_COVID_WARNING#
</div>

<?php
  $CI             = get_instance();
  $accountValues  = $CI->session->getProfile('info_contact');
  $CI->load->model('custom/GeneralServices');
  //echo json_encode($accountValues);
  

  $ContactData = $CI->GeneralServices->getOrganizationStatus($accountValues->contactID);
  //echo json_encode($ContactData->Ruts->List->data);
  //echo count($ContactData->Ruts->List->data) . '-' . $accountValues->contactID;
  if(is_array($ContactData->Ruts->List->data))
  {
    $ruts="";
    for($i=0;$i<count($ContactData->Ruts->List->data); $i++)
    {
      $ruts= $ruts . "'" . $ContactData->Ruts->List->data[$i]->rut_cliente ."'," ;
      //echo $ruts .'<br>';
      
    }
    $ruts=$ruts . "''";
  }
  else
  {
    $ruts= "'" .$ContactData->Ruts->List->data->rut_cliente ."'";
  }
  
  // Parche Uso de cookies

  $accountValues  = unserialize($_COOKIE['Account_loggedValues']);
  $a_Filters1     = array();
  $a_Filters1[]   = array("name" => 'cuentaID', "operator" => 'list' , "type" => 'INT' , "value" =>  $ruts);
  //echo json_encode($a_Filters1[0]);
  
?>
<div id="rn_PageContent rn_Home">
    <div class="rn_Padding column">
      <rn:widget path="custom/reports/IntegerGrid" report_id="102271" json_filters="#rn:php:json_encode($a_Filters1)#" per_page="20" show_paginator="true" url_per_col="/app/account/questions/detail/i_id/" col_id_url="1" />
    </div>
</div>
<div class="content-body">
    <rn:container report_id="102091">
    <div class="wrapper">
        <h2 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_RESULTS_CMD#</h2>
        <rn:widget path="reports/ResultInfo" per_page="50" />
        <rn:widget path="reports/Grid" label_caption="<span class='rn_ScreenReaderOnly'>#rn:msg:SEARCH_YOUR_SUPPORT_HISTORY_CMD#</span>"  />
        <rn:widget path="reports/Paginator" >
    </div>

    </rn:container>
</div>

