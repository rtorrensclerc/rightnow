<!DOCTYPE html>
<html lang="#rn:language_code#">
<rn:meta javascript_module="standard" />

<head>
  <?php
    $this->load->helper('utils');
    $CI = get_instance();
    $temporalKey = $CI->session->getSessionData('temporal_key');
  ?>
  <? if ($temporalKey) : // Condición para contraseña temporal ?>
  <rn:condition hide_on_pages="utils/create_account, account/change_password, utils/account_assistance, account/password_changed, utils/submit/password_changed, utils/login_form, reparacion/answers/detail, reparacion/answers/list, reparacion/hh/detail, reparacion/hh/list, reparacion/special/special_detail, reparacion/special/special_suport, reparacion/active_parts, reparacion/ar_request, reparacion/c_active_parts, reparacion/c_last_request, reparacion/c_part_request, reparacion/cargo, reparacion/home, reparacion/informe_detail, reparacion/last_request, reparacion/login, reparacion/logistica_detail, reparacion/logistica, reparacion/part_request, reparacion/parts_request, reparacion/request_detail, reparacion/request_detailAr, reparacion/request, utils/submit/profile_updated, utils/help_search, error, error404"/>
    <? header('Location: ' . \RightNow\Utils\Url::getOriginalUrl(false) . '/app/account/change_password'); ?>
  </rn:condition>
  <? endif; ?>
  <meta charset="utf-8" />
  <title>
    <rn:page_title />
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="apple-touch-icon" href="/euf/assets/images/apple-touch-icon.png">
  <link rel="icon" href="/euf/assets/images/favicon.png" type="image/png" />
  <link rel="icon" sizes="192x192" href="/euf/assets/images/icon_hires.png">
  <meta name="theme-color" content="#000000">
  <meta name="msapplication-navbutton-color" content="#000000">
  <meta name="apple-mobile-web-app-status-bar-style" content="#000000">
  <rn:theme path="/euf/assets/themes/2020" css="site.css" />
  <!-- <link href="//127.0.0.1:8080//site_dev.css" rel="stylesheet"> -->
  
  <script src="/euf/assets/js/integer.js"></script>
  <script src="/euf/assets/js/vendors/d3.min.js"></script>
  <script src="/euf/assets/js/vendors/download.js"></script>
  <script src="/euf/assets/js/vendors/xlsx.full.min.js"></script>
  <script src="/euf/assets/js/vendors/siema.min.js"></script>
 <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-G49691G6FB"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-G49691G6FB');
</script>
  <link href="//fonts.googleapis.com/css?family=Lato:300,400,400i,700,700i" rel="stylesheet">
  <rn:head_content />
  <!-- <rn:widget path="utils/ClickjackPrevention" /> -->
</head>

<body class="yui-skin-sam yui3-skin-sam<?= (\RightNow\Utils\Framework::isLoggedIn())?" logged":" nologged" ?>" style="font-size: 1em;">

  <input type="checkbox" id="nav-trigger" class="nav-trigger" />
  <label for="nav-trigger" class="labelNavTrigger rn_Sprite"></label>

  <? // MENÚ MÓVIL ############################################ 
  ?>
  <? // FIN MENÚ MÓVIL ############################################ 
  ?>

  <div class="rn_Wrap">
    <header>

      <div class="rn_Container row">
        <div class="rn_Brand rn_FloatLeft small-12">
          <a id="logo" class="rn_SpriteLogos" href="/app/sv/home"></a>
        </div>
        <div class="rn_NavigationBar">
          <ul class="rn_NavigationMenu reset-list">

            <rn:condition logged_in="true">

           
              <? if (!$temporalKey) : // Condición para contraseña temporal ?>

              <!-- Inicio -->
              <li class="rn_NavigationTab">
                <div class="rn_NavigationTab">
                  <rn:widget path="navigation/NavigationTab" label_tab="Inicio" link="/app/sv/helpcenter" pages="sv/sv/helpcenter" />
                </div>
              </li>

              <!-- Facturación y Pagos -->
              <? if (isEnabled(5) || isEnabled(8) || isEnabled(6)|| isEnabled(17)) : ?>
                <li class="rn_NavigationTab sublevel">
                  <div class="rn_NavigationTab">
                    <!--a href="#FacturaPagos">Facturación y Pagos</a-->
                    <a href="/app/sv/billing_payments/invoice_payments">Facturación y Pagos</a>
                  </div>
                  <ul class="rn_SubNavigation submenu">

                    <? if (isEnabled(5) || isEnabled(6)) : ?>
                      <!-- li>
                        <rn:widget path="navigation/NavigationTab" label_tab="Resumen Mensual" link="/app/sv/billing_payments/billing_payments" pages="/app/sv/billing_payments/billing_payments" />
                      </li --> 
                    <? endif; ?>

                    <? if (isEnabled(6)) : ?>
                      <li>
                        <rn:widget path="navigation/NavigationTab" label_tab="Últimos Documentos" link="/app/sv/billing_payments/invoice_payments" pages="/app/sv/billing_payments/invoice_payments" />
                      </li>
                      <li>
                        <rn:widget path="navigation/NavigationTab" label_tab="Búsqueda Rápida de Factura" link="/app/sv/billing_payments/search_invoice" pages="/app/sv/billing_payments/search_invoice" />
                      </li>
                    <? endif; ?>
                    <? if (isEnabled(19)) : ?>
                    <li>
                        <rn:widget path="navigation/NavigationTab" label_tab="Ingreso de Contadores" link="https://www.dimacofi.cl/ingreso-contadores" pages="https://www.dimacofi.cl/ingreso-contadores" target="_blank" />
                    </li>
                    <? endif; ?>
                    
                  </ul>
                </li>
              <? endif; ?>

              <? if (isEnabled(3)) : ?>
              <li class="rn_NavigationTab sublevel">
                <div class="rn_NavigationTab">
                  <!--a href="#SoporteTecnico">Solicitudes</a-->
                  <a href="/app/sv/request/search_supplies_request">Insumos</a>
                </div>
                <ul class="rn_SubNavigation submenu">
                  <li><rn:widget path="navigation/NavigationTab" label_tab="Insumos" link="/app/sv/supplier/form" pages="sv/sv/supplier/form" /></li>
                  <? if(isEnabled(4)): ?><li><rn:widget path="navigation/NavigationTab" label_tab="Insumos Múltiples" link="/app/sv/supplier/form_multiple" pages="sv/supplier/form_multiple" /></li><? endif; ?>
                  <li><rn:widget path="navigation/NavigationTab" label_tab="Tracking Insumos" link="https://www.dimacofi.cl/centro-de-ayuda/#tabTrack" pages="https://www.dimacofi.cl/centro-de-ayuda/#tabTrack" target="_blank"/></li>
                  <li><rn:widget path="navigation/NavigationTab" label_tab="Mis Solicitudes" link="/app/sv/request/search_supplies_request" pages="/app/sv/request/search_supplies_request" /></li>
                </ul>
              </li>
              <? endif; ?>

              <!-- Solicitudes -->
              <li class="rn_NavigationTab sublevel">
                <div class="rn_NavigationTab">
                  <a href="/app/sv/request/search_services_request">Soporte</a>
                </div>

                <ul class="rn_SubNavigation submenu">

                    <? if (isEnabled(2)) : ?>
                      <li><rn:widget path="navigation/NavigationTab" label_tab="Soporte Técnico" link="/app/sv/request/technical" pages="sv/request/technical" /></li>
                      <!--li><rn:widget path="navigation/NavigationTab" label_tab="Soporte Técnico2" link="/app/sv/request/Contact3" pages="/app/sv/request/Contact3" /></li-->
                    <? endif; ?>

                    <!-- <? if (isEnabled(2)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="Busca Equipos" link="/app/sv/request/asset_search" pages="sv/request/asset_search" /></li>
                    <? endif; ?>  -->

                    <? if (isEnabled(9)) : ?>
                      <li><rn:widget path="navigation/NavigationTab" label_tab="RPA" link="/app/sv/request/Digital_Support/p/68/c/89" pages="sv/request/Digital_Support" /></li>
                    <? endif; ?>

                    <? if (isEnabled(10)) : ?>
                      <li><rn:widget path="navigation/NavigationTab" label_tab="Aula Digital" link="/app/sv/request/Digital_Support/p/68/c/91" pages="sv/request/Digital_Support" /></li>
                    <? endif; ?>

                    <? if (isEnabled(11)) : ?>
                      <li><rn:widget path="navigation/NavigationTab" label_tab="Firma Electrónica" link="/app/sv/request/Digital_Support/p/68/c/92" pages="sv/request/Digital_Support" /></li>
                    <? endif; ?>

                    <? if (isEnabled(12)) : ?>
                      <li><rn:widget path="navigation/NavigationTab" label_tab="Gestion Documental" link="/app/sv/request/Digital_Support/p/68/c/90" pages="sv/request/Digital_Support" /></li>
                    <? endif; ?>

                    <? if (isEnabled(13)) : ?>
                      <li><rn:widget path="navigation/NavigationTab" label_tab="BPO" link="/app/sv/request/Digital_Support/p/68/c/93" pages="sv/request/Digital_Support" /></li>
                    <? endif; ?>
                    <? if (isEnabled(16)) : ?>
                      <li><rn:widget path="navigation/NavigationTab" label_tab="DAAS" link="/app/sv/request/Digital_Support/p/68/c/96" pages="sv/request/Digital_Support" /></li>
                    <? endif; ?>
                  
    
                    <li><rn:widget path="navigation/NavigationTab" label_tab="Mis Solicitudes" link="/app/sv/request/search_services_request" pages="/app/sv/request/search_services_request" /></li>
                    <? if (isEnabled(15)) : ?>
                    <!--li><rn:widget path="navigation/NavigationTab" label_tab="Todas Solicitudes" link="/app/sv/request/history/org/2" pages="app/sv/request/history/org/2" /></li-->
                    <? endif; ?>
                </ul>
              </li>

               <!-- Monitor -->
               <? if (isEnabled(17)) : ?>    
                <li class="rn_NavigationTab sublevel">
                <div class="rn_NavigationTab">
                  <a href="/app/sv/request/history">Monitor de Equipos</a>
                </div>
                <ul class="rn_SubNavigation submenu">
                    <li><rn:widget path="navigation/NavigationTab" label_tab="Monitor Contadores" link="/app/sv/request/search_transactions" pages="app/sv/request/search_transactions" /></li>
                    <li><rn:widget path="navigation/NavigationTab" label_tab="Monitor de Insumos " link="/app/sv/request/search_supplies_status" pages="app/sv/request/search_supplies_status" /></li>
                    <li><rn:widget path="navigation/NavigationTab" label_tab="Estado de Equipos" link="/app/sv/request/search_hh_status" pages="app/sv/request/search_hh_status" /></li>
                    <li><rn:widget path="navigation/NavigationTab" label_tab="Contadores Historicos" link="/app/sv/request/search_transactions_hist" pages="app/sv/request/search_transactions_hist" /></li>
                   
                   
                </ul>
              </li>
              <? endif; ?>

              <!-- Servicio al Cliente >
              <? if (isEnabled(1)) : ?>    
                <li class="rn_NavigationTab sublevel">
                <div class="rn_NavigationTab">
                  <a href="/app/sv/request/history">Tu Opinión</a>
                </div>
                <ul class="rn_SubNavigation submenu">
                    <li><rn:widget path="navigation/NavigationTab" label_tab="Felicitaciones" link="/app/sv/request/contact/p/67" pages="sv/request/technical" /></li>
                    <li><rn:widget path="navigation/NavigationTab" label_tab="Reclamos" link="/app/sv/request/contact/p/66" pages="sv/request/Digital_Support" /></li>
                    <li><rn:widget path="navigation/NavigationTab" label_tab="Sugerencias" link="/app/sv/request/contact/p/50" pages="sv/request/Digital_Support" /></li>
                </ul>
              </li-->
              <? endif; ?>

              
            </rn:condition>
            
            
            <rn:condition logged_in="true">
              <? if (isEnabled(7)) : ?>
                <!-- Gestor de Usuarios -->
                <li class="rn_NavigationTab sublevel">
                  <div class="rn_NavigationTab">
                    <a href="/app/sv/users/user_management">Administración</a>
                  </div>
                  <ul class="rn_SubNavigation submenu">
                    <li>
                      <rn:widget path="navigation/NavigationTab" label_tab="Crear Usuario" link="/app/sv/users/user" pages="sv/sv/users/user" />
                    </li>
                    <li>
                      <rn:widget path="navigation/NavigationTab" label_tab="Gestión Usuarios" link="/app/sv/users/user_management" pages="sv/sv/users/user_management" />
                    </li>
                  </ul>
                </li>
              <? endif; ?>
              <li class="rn_NavigationTab">
                <div class="rn_NavigationTab">
                  <!--a href="#SoporteTecnico">Solicitudes</a-->
                  <a href="/app/sv/questions">Preguntas Frecuentes</a>
       
      
                </div>
              </li>

              <? endif; // Fin de condición para contraseña temporal ?>
             
            </rn:condition>
          </ul>
        </div>
        
        
        <rn:widget path="custom/login/LoginStatus" in_line="true" show_login="false" />
       
        <rn:condition logged_in="false">
         
          <rn:widget path="custom/menu/SearchQuestions" />
          
          </rn:condition>
          
        <div class="rn_PageHeader">
          <h1><rn:page_title /></h1>
        </div>
      </div>
              
    </header>
    
    <div class="rn_Body row">
      <div class="rn_MainColumn" role="main">
        <a id="rn_MainContent"></a>
        <rn:page_content />
      </div>
    </div>

    <footer class="rn_Footer">
      <div class="rn_Container row">

        <div class="rn_FloatLeft rn_SmallTextCenter rn_SmallFullWidth">
          <div class="rn_SpriteLogos logo">

          </div>
        </div>

        <div class="rn_MediumFullWidth">
          <ul class="reset-list">
            <li class="rn_FooterLinksItem"><a href="https://www.dimacofi.cl/somos-dimacofi/">Somos Dimacofi</a></li>
            <li class="rn_FooterLinksItem"><a href="https://www.dimacofi.cl/casos-de-exito/">Casos de éxito</a></li>
            <li class="rn_FooterLinksItem"><a href="https://www.dimacofi.cl/blog/">Novedades</a></li>
            <li class="rn_FooterLinksItem"><a href="https://www.dimacofi.cl/centro-de-ayuda/">Centro de Ayuda</a></li>
          </ul>
        </div>

        <div class="rn_MediumFullWidth">
          <ul class="reset-list">
            <li class="rn_FooterLinksItem"><a href="https://www.dimacofi.cl/convenio-marco/">Convenio marco</a></li>
            <li class="rn_FooterLinksItem"><a href="https://www.dimacofi.cl/oficinas/">Oficinas</a></li>
            <li class="rn_FooterLinksItem"><a target="_blank" rel="noopener noreferrer" href="https://dimacofi.trabajando.cl/">Trabaja con nosotros</a></li>
          </ul>
        </div>


        <div class="rn_MediumFullWidth">
          <div class="rn_FooterLinks rn_MediumTextCenter">

            <div class="rn_FooterLinksItem">
              <div class="rn_FooterLinksItem-media ico_footerMercadoPublico rn_SpriteLogos">
                &nbsp;
              </div>
              <div class="rn_FooterLinksItem-body rn_SmallHide">
              </div>
            </div>

            <div class="rn_FooterLinksItem">
              <a href="https://twitter.com/EmpresaDimacofi">
                <div class="rn_FooterLinksItem-media ico_footerTwitter rn_Sprite">
                  &nbsp;
                </div>
                <div class="rn_FooterLinksItem-body rn_MediumHide">
                </div>
              </a>
            </div>

            <div class="rn_FooterLinksItem">
              <a href="https://www.linkedin.com/company/1206306">
                <div class="rn_FooterLinksItem-media ico_footerLinkedin rn_Sprite">
                  &nbsp;
                </div>
                <div class="rn_FooterLinksItem-body rn_MediumHide">
                </div>
              </a>
            </div>

          </div>
        </div>

      </div>
    </footer>
  </div>

  <div id="contact_modal" style="display: none; position: fixed;
    z-index: 1;
    left: 5%;
    top: 5%;
    right: 5%;
    opacity:1;
    padding: 2%;
    box-shadow: rgba(0, 0, 0, 0.5) 0px 0.5rem 1rem;
    background-color: rgb(255, 255, 255);
    transition: 2s;
    border-radius: 20px;
    z-index: 100;">
    <div class="rn_ModalHeader rn_Account">
      <div class="rn_Container">
        <h1>Ejecutivos de Cuenta</h1>
      </div>
    </div>

    <?php
      $CI = get_instance();
      $contact_values = $this->session->getSessionData('info_contact');
  
    ?>
    <div id="rn_PageContent" class="rn_Profile">
      <div class="rn_Padding">
        <rn:container>
          <div class="rn_Required rn_LargeText">#rn:url_param_value:msg#</div>
          
            <div id="rn_ErrorLocation" style="display:none;"></div>
            
            
            
            <div class="side-right">
              <legend>Project Management (PM) </legend>
              <div>
                <strong>

                  <?php if($contact_values["tPM"] != null) :?>
                    <span><?=hex2bin($contact_values["tPM"])?></span>
                    <?php else: ?>
                      <span>No tienes un Project Management asignado</span>
                  <?php endif; ?>
                </strong>
              </div>
              <div>
                <strong>
                  <span>
                  <?php if($contact_values["tPM"] != null) :?>
                        <?php if($contact_values["tPM_EMAIL"] != null) :?>
                          <a href="mailto:<?=hex2bin($contact_values["tPM_EMAIL"])?>" target="_blank"><?=hex2bin($contact_values["tPM_EMAIL"])?></a>
                        <?php else: ?>
                          <span>El Project Management no tiene un correo de contacto habilitado.</span>
                        <?php endif; ?>
                    <?php else: ?>
                      <span></span>
                    <?php endif; ?>
                  </span>
                </strong>
              </div>
              <div>
                <span>#rn:msg:CUSTOM_MSG_DESC_PM#</span>
              </div>
            </div>
            <div class="side-left">
              <legend>Key Account Manager (KAM) </legend>
              <div>
                <strong>
                  <?php if($contact_values["tKAM"] != null) :?>
                    <span><?=hex2bin($contact_values["tKAM"])?></span>
                    <?php else: ?>
                      <span>No tienes un Key Account Manager asignado</span>
                  <?php endif; ?>
                </strong>
              </div>
              <div>
                <strong>
                  <span>
                    <?php if($contact_values["tKAM"] != null) :?>
                        <?php if($contact_values["tKAM_EMAIL"] != null) :?>
                          <a href="mailto:<?=hex2bin($contact_values["tKAM_EMAIL"])?>" target="_blank"><?=hex2bin($contact_values["tKAM_EMAIL"])?></a>
                        <?php else: ?>
                          <span>El Key Account Manager no tiene un correo de contacto habilitado.</span>
                        <?php endif; ?>
                    <?php else: ?>
                      <span></span>
                    <?php endif; ?>
                  </span>
                </strong>
              </div>
              <div>
                <span>#rn:msg:CUSTOM_MSG_DESC_KAM#</span>
              </div>
            </div>
            
            <div class="buttons" >
              
              <div class="rn_NavigationMenu side-right">
                <button id="closeModal" onclick="Integer.hideModalOnClick();">Cerrar</button>
              </div>
            </div>
        </rn:container>
      </div>
    </div>
  </div>


</body>

</html>
