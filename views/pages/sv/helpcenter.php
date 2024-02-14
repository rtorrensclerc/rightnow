<rn:widget path="custom/redirect/RedirectBlocked" />

<rn:meta title="Mi Dimacofi" template="2020.php" clickstream="help_center" login_required="true" />
<?
  // $CI = get_instance();
  // $obj_info_contact= $CI->session->getSessionData('info_contact');
?>



<?
  $CI = &get_instance();
 

  $obj_info_contact= $CI->session->getSessionData('info_contact');
  //echo "<br>Inicio " . $obj_info_contact['Inicio'];

  //echo "<br>Profile " .$obj_info_contact['ProfileType']['ID'];
   // SI es la primera vez aparece el mensage de bloqueo
   //echo json_encode($obj_info_contact);
  // echo $obj_info_contact['Block'];
  //echo json_encode($obj_info_contact);
  if($obj_info_contact['Block']==1)
  {
?>
  <rn:widget path="custom/info/CustomerInfo"  rut="1" />
<?
  }
  
  
   /*
    if($obj_info_contact['ProfileType']['ID']==17)
    {
      if( $obj_info_contact['Inicio'])
      {
        
        ?>
        <rn:widget path="Custom/Info/PopUpMonitor" />
        <?
        
        $obj_info_contact['Inicio']=0;
        $CI->session->setSessionData(array('info_contact' => $obj_info_contact));

      }
    }
    else
    {
      if( $obj_info_contact['Inicio'])
      {
        ?>
        <rn:widget path="Custom/Info/PopUpNoMonitor" />
        <?
        
        $obj_info_contact['Inicio']=0;
        $CI->session->setSessionData(array('info_contact' => $obj_info_contact));

      }
    }*/
    //echo json_encode($obj_info_contact);
    if( $obj_info_contact['PopHour'])
    {
      ?>
      <rn:widget path="Custom/Info/PopUpHour" />
      <?
      
      $obj_info_contact['PopHour']=0;
      $CI->session->setSessionData(array('info_contact' => $obj_info_contact));

    }
    /*
  else
  {
    
    switch($obj_info_contact['ProfileType']['ID'])
    {
      
      case 17:    // monitoreo Nube print
        echo "<br>Profile NBP" ;
        if( $obj_info_contact['Inicio']==1)
        {
          ?>
          <rn:widget path="Custom/Info/PopUpMonitor" />
          <?
          
          $obj_info_contact['Inicio']=$obj_info_contact['Inicio']+1;
          $CI->session->setSessionData(array('info_contact' => $obj_info_contact));

        }
        default:
        if( $obj_info_contact['Inicio']==1)
        {
          ?>
          <rn:widget path="Custom/Info/PopUpNoMonitor" />
          <?
          
          $obj_info_contact['Inicio']=$obj_info_contact['Inicio']+1;
          $CI->session->setSessionData(array('info_contact' => $obj_info_contact));

        }
    }
  }

  */
    ?>
 

<rn:container report_id="101571">
  <div class="rn_Container">
    <div class="rn_Shortcuts">
      

        <? if (isEnabled(5) || isEnabled(8) || isEnabled(6)) : ?>
          <div class="rn_Shortcuts-col">
        <ul class="rn_ShortcutsList payments">
          <h2>Facturación y Pagos</h2>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-bill">
            <a href="/app/sv/billing_payments/invoice_payments">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Factura</h3>
            </a>
          </li>

          <? if (isEnabled(5) || isEnabled(6)) : ?>
          <!--li class="rn_ShortcutsItem rn_ShortcutsItem-billdetail">
            <a href="/app/sv/billing_payments/billing_payments">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Detalle</h3>
            </a>
          </li -->
          <? endif; ?>

          <? if (isEnabled(8)) : ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-payments">
            <a href="/app/sv/billing_payments/invoice_payments">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Pago</h3>
            </a>
          </li>
          <? endif; ?>
          <? if (isEnabled(19)) : ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-counters">
            <a href="https://www.dimacofi.cl/centro-de-ayuda/#tabCont" target="_blank">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Ingreso</br>Contadores</h3>
            </a>
          </li>
          <? endif; ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-billfinder">
            <a href="/app/sv/billing_payments/search_invoice">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Búsqueda</br>Rápida de Factura</h3>
            </a>
          </li>
        </ul>
      </div>
      <? endif; ?>

      <? if (isEnabled(3) ): ?>
      <div class="rn_Shortcuts-col">
        <ul class="rn_ShortcutsList supplies">
          <h2>Solicitud de Insumos</h2>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-supplies">
            <a href="/app/sv/supplier/form">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Solicitud</br>Insumos</h3>
            </a>
          </li>
          <? if (isEnabled(4) ): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-multiplesupplies">
            <a href="/app/sv/supplier/form_multiple">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Solicitud</br>Insumos Multiples</h3>
            </a>
          </li>
          <? endif; ?>
          <? if (isEnabled(3) ): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-trackingsupplies">
            <a href="https://www.dimacofi.cl/centro-de-ayuda/#tabTrack" target="_blank">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Tracking</br>Insumos</h3>
            </a>
          </li>
          <? endif; ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-supplier-request">
            <a href="/app/sv/request/search_supplies_request" >
              <div class="rn_ShortcutsIcon rn_MonitorSprite"></div>
              <h3 class="rn_ShortcutsList">Mis Solicitudes </br> de Insumos</h3>
            </a>
          </li>
          <? if (isEnabled(18) ): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-reverselogistics">
            <a href="/app/sv/request/reverse_logistics" >
              <div class="rn_ShortcutsIcon rn_MonitorSprite"></div>
              <h3 class="rn_ShortcutsList">Logística </br> Inversa</h3>
            </a>
          </li>
          <? endif; ?>
        </ul>
      </div>
      <? endif; ?>


      <? if (isEnabled(2) || isEnabled(9) || isEnabled(10) || isEnabled(11) || isEnabled(12) || isEnabled(13) || isEnabled(16)): ?>
      <div class="rn_Shortcuts-col">
        <ul class="rn_ShortcutsList support">
          <h2>Solicitud de Soporte</h2>

          <? if (isEnabled(2)): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-multifunctional ">
         
            <a href="/app/sv/request/technical">
              <div class="rn_ShortcutsIcon rn_ProductsSprite">
              </div>
              <h3 class="rn_ShortcutsList">Impresoras y <br>Multifuncionales</h3>
              </a>
          </li>
          <? endif; ?>

          <? if (isEnabled(9)): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-rpa">
            <a href="/app/sv/request/Digital_Support/p/68/c/89">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">RPA<br>.</h3>
            </a>
          </li>
          <? endif; ?>

          
          <? if (isEnabled(10)): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-digitalclassroom">
            <a href="/app/sv/request/Digital_Support/p/68/c/91">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Aula <br>Digital</h3>
            </a>
          </li>
          <? endif; ?>

          <? if (isEnabled(11)): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-digitalsignature">
            <a href="/app/sv/request/Digital_Support/p/68/c/92">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Firma <br>Digital</h3>
            </a>
          </li>
          <? endif; ?>

          <? if (isEnabled(12)): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-documentmanager">
            <a href="/app/sv/request/Digital_Support/p/68/c/90">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Gestión <br>Documental</h3>
            </a>
          </li>
          <? endif; ?>


          <? if (isEnabled(13)): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-bpo">
            <a href="/app/sv/request/Digital_Support/p/68/c/93">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">Digitalización<br></h3>
            </a>
          </li>
          <? endif; ?>
          <? if (isEnabled(16)): ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-daas">
            <a href="/app/sv/request/Digital_Support/p/68/c/96">
              <div class="rn_ShortcutsIcon rn_ProductsSprite"></div>
              <h3 class="rn_ShortcutsList">DAAS</h3>
            </a>
          </li>
          <? endif; ?>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-service-request">
            <a href="/app/sv/request/search_services_request" >
              <div class="rn_ShortcutsIcon rn_MonitorSprite"></div>
              <h3 class="rn_ShortcutsList">Mis Solicitudes </br>de Soporte</h3>
              
            </a>
          </li>
          <!--li class="rn_ShortcutsItem rn_ShortcutsItem-monitorhh">
            <a href="https://calendar.app.google/KcWf5isqJbiPrVUS6" >
              <div class="rn_ShortcutsIcon rn_MonitorSprite"></div>
              <h3 class="rn_ShortcutsList">Agenda Operador Remoto</h3>
            </a>
          </li-->
        </ul>
      </div>
      <? endif; ?>

      <? if (isEnabled(17)): ?>
      <div class="rn_Shortcuts-col">
        <ul class="rn_ShortcutsList monitor">
          <h2>Monitor de Equipos</h2>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-monitorcounter">
            <a href="/app/sv/request/search_transactions" >
              <div class="rn_ShortcutsIcon rn_MonitorSprite"></div>
              <h3 class="rn_ShortcutsList">Monitor de </br>Contadores</h3>
            </a>
          </li>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-monitorsupplies">
            <a href="/app/sv/request/search_supplies_status" >
              <div class="rn_ShortcutsIcon rn_MonitorSprite"></div>
              <h3 class="rn_ShortcutsList">Monitor de </br>Insumos</h3>
            </a>
          </li>
          <li class="rn_ShortcutsItem rn_ShortcutsItem-monitorhh">
            <a href="/app/sv/request/search_hh_status" >
              <div class="rn_ShortcutsIcon rn_MonitorSprite"></div>
              <h3 class="rn_ShortcutsList">Estado de </br>Equipos</h3>
            </a>
          </li>
          <!--li class="rn_ShortcutsItem rn_ShortcutsItem-monitorcounter-hist">
            <a href="/app/sv/request/search_transactions_hist" >
              <div class="rn_ShortcutsIcon rn_MonitorSprite"></div>
              <h3 class="rn_ShortcutsList">Contadores<br>Historicos</h3>
            </a>
          </li-->
         
    
          
        </ul>
      </div>
      <? endif; ?>
      
    
  </div>
    <ul class="rn_ShortcutsList-feedback">
      <h2>Tu Opinión Nos Importa</h2>

      <li class="rn_ShortcutsItem rn_ShortcutsItem-congratulations">
        <a href="/app/sv/request/contact/p/67">
          <div class="rn_FeedbackIcon rn_FeedbackSprite"></div>
          <h3 class="rn_ShortcutsList">Felicitaciones</h3>
        </a>
      </li>

      <li class="rn_ShortcutsItem rn_ShortcutsItem-claims">
        <a href="/app/sv/request/contact/p/66">
          <div class="rn_FeedbackIcon rn_FeedbackSprite"></div>
          <h3 class="rn_ShortcutsList">Reclamos</h3>
        </a>
      </li>

      <li class="rn_ShortcutsItem rn_ShortcutsItem-suggestions">
        <a href="/app/sv/request/contact/p/50">
          <div class="rn_FeedbackIcon rn_FeedbackSprite"></div>
          <h3 class="rn_ShortcutsList">Sugerencias</h3>
        </a>
      </li>

      <li class="rn_ShortcutsItem rn_ShortcutsItem-queries">
        <a href="/app/sv/questions">
          <div class="rn_FeedbackIcon rn_FeedbackSprite"></div>
          <h3 class="rn_ShortcutsList">Preguntas</h3>
        </a>
      </li>

      <li class="rn_ShortcutsItem rn_ShortcutsItem-myejec">
         <a onclick="Integer.showModalOnClick();">
          <div class="rn_FeedbackIcon rn_FeedbackSprite"></div>
          <h3 class="rn_ShortcutsList">Mi Ejecutivo</h3>
        </a>
      </li>
      <!-- <li class="rn_ShortcutsItem rn_ShortcutsItem-ad">
        <a href="app/sv/users/user_management">
          <div class="rn_FeedbackIcon rn_FeedbackSprite"></div>
          <h3 class="rn_ShortcutsList">Administración </br>de Usuarios</h3>
        </a>
      </li> -->
    </ul>
</rn:container>

