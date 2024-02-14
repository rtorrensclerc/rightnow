<rn:widget path="custom/redirect/RedirectBlocked" />
<rn:meta title="Solicitud Realizada" template="2020.php" clickstream="confirm_request" login_required="false" />

<div class="rn_PageContent rn_Home rn_Wraper">

  <div class="rn_SuccessMesage">
    <div class="message">
      <h2>Gracias por registrar su solicitud</h2>
      <rn:condition logged_in="true">
      <!--p>Su solicitiud <strong><rn:field name="Incident.Category.LookupName" /></strong> ha sido registrada</p-->
      <p>Su solicitiud  ha sido registrada con Exito</p>
      <p>y asociada al número de referencia <strong><a href="/app/#rn:config:CP_INCIDENT_RESPONSE_URL#/i_id/#rn:url_param_value:i_id##rn:session#">#<rn:field name="Incident.ReferenceNumber" /></a></strong>.</p>
    </rn:condition>
    <rn:condition logged_in="false">
      <p>#rn:msg:CUSTOM_MSG_NEED_UPD_2# <a href="/app/#rn:config:CP_INCIDENT_RESPONSE_URL#/i_id/#rn:url_param_value:i_id##rn:session#">Enlace</a></p>
    </rn:condition>
    <rn:condition logged_in="true">
      <p>#rn:msg:NEED_UPD_EXP_OR_M_GO_TO_HIST_O_UPD_IT_MSG# <a href="/app/#rn:config:CP_INCIDENT_RESPONSE_URL#/i_id/#rn:url_param_value:i_id##rn:session#">Enlace</a></p>
    </rn:condition>
    </div>
    <div class="icon"></div>
  </div>

</div>
