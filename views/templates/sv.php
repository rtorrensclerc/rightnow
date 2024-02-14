<!DOCTYPE html>
<html lang="#rn:language_code#">
<rn:meta javascript_module="standard" />

<head>
  <?php $this->load->helper('utils'); ?>
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
  <rn:theme path="/euf/assets/themes/sv" css="site.css" />
  <script src="/euf/assets/js/integer.js"></script>
  <script src="/euf/assets/js/vendors/d3.min.js"></script>
  <script src="/euf/assets/js/vendors/download.js"></script>
  <script src="/euf/assets/js/vendors/xlsx.full.min.js"></script>
  <link href="https://fonts.googleapis.com/css?family=Lato:400,400i,700,700i" rel="stylesheet">
  <rn:head_content />
  <rn:widget path="utils/ClickjackPrevention" />
</head>

<body class="yui-skin-sam yui3-skin-sam">


  <? // MENÚ MÓVIL ############################################ 
  ?>
  <div class="navigationOffCanvas">
    <ul class="rn_NavigationMenuOffCanvas reset-list">
      <h2>Menú Principal</h2>

      <?
      $CI = get_instance();
      $a_accountValues = $CI->session->getSessionData('Account_loggedValues');
      ?>

      <? if (profiling('1,2', true)) : ?>
        <rn:condition logged_in="true">
          <!-- Inicio -->
          <li class="rn_NavigationTab">
            <div class="rn_NavigationTab">
              <rn:widget path="navigation/NavigationTab" label_tab="Inicio" link="/app/sv/helpcenter" pages="sv/sv/helpcenter" />
            </div>
          </li>

          <!-- Facturación y Pagos -->
          <? if (isEnabled(5) || isEnabled(8) || isEnabled(6)) : ?>
            <li class="rn_NavigationTab sublevel">
              <div class="rn_NavigationTab">
                <h2>Facturación y Pagos</h2>
              </div>
              <ul class="rn_SubNavigation submenu">
                <? if (isEnabled(5) || isEnabled(6)) : ?>
                  <li>
                    <!-- <rn:widget path="navigation/NavigationTab" label_tab="Resumen Mensual" link="/app/sv/billing_payments/billing_payments"
                  pages="/app/sv/billing_payments/billing_payments" /> -->
                    <rn:widget path="navigation/NavigationTab" label_tab="Resumen Mensual" link="/app/sv/billing_payments/invoice_payments" pages="/app/sv/billing_payments/invoice_payments" />
                  </li>
                <? endif; ?>
                <? if (isEnabled(8)) : ?>
                  <li>
                    <rn:widget path="navigation/NavigationTab" label_tab="Últimos Documentos Emitidos" link="/app/sv/billing_payments/invoice_payments" pages="/app/sv/billing_payments/invoice_payments" />
                  </li>
                <? endif; ?>
              </ul>
            </li>
          <? endif; ?>

          <!-- Solicitudes -->
          <li class="rn_NavigationTab sublevel">
            <div class="rn_NavigationTab">
              <h2>Solicitudes</h2>
            </div>
            <ul class="rn_SubNavigation submenu">
              <li>

              <? if (isEnabled(3)) : ?>
                  <rn:widget path="navigation/NavigationTab" label_tab="Insumos" link="/app/sv/supplier/form" pages="sv/sv/supplier/form" /><? endif; ?>
                <? if (isEnabled(3)) : ?>
                  <rn:widget path="navigation/NavigationTab" label_tab="Insumos Múltiples" link="/app/sv/supplier/form_multiple" pages="sv/supplier/form_multiple" /><? endif; ?>
                <? if (isEnabled(2)) : ?>
                  <rn:widget path="navigation/NavigationTab" label_tab="Soporte Impresoras" link="/app/sv/request/technical" pages="sv/request/technical" /><? endif; ?>
                <? if (isEnabled(9)) : ?>
                  <rn:widget path="navigation/NavigationTab" label_tab="RPA" link="/app/sv/request/Digital_Support/p/68/c/93" pages="sv/request/Digital_Support/p/68/c/93" /><? endif; ?>
                <? if (isEnabled(10)) : ?>
                  <rn:widget path="navigation/NavigationTab" label_tab="Aula Digital" link="/app/sv/request/Digital_Support/p/68/c/91" pages="sv/request/Digital_Support" /><? endif; ?>
                <? if (isEnabled(11)) : ?>
                  <rn:widget path="navigation/NavigationTab" label_tab="Firma Digital" link="/app/sv/request/Digital_Support/p/68/c/92" pages="sv/request/Digital_Support" /><? endif; ?>
                  <? if (isEnabled(12)) : ?>
                  <rn:widget path="navigation/NavigationTab" label_tab="Gestion Documental" link="/app/sv/request/Digital_Support/p/68/c/90" pages="sv/request/Digital_Support" /><? endif; ?>
                  <? if (isEnabled(13)) : ?>
                  <rn:widget path="navigation/NavigationTab" label_tab="BPO" link="/app/sv/request/Digital_Support/p/68/c/93" pages="sv/request/Digital_Support" /><? endif; ?>
                <rn:widget path="navigation/NavigationTab" label_tab="Mis Solicitudes" link="/app/sv/request/history" pages="sv/request/history" />
              
              </li>
            </ul>

          </li>
        </rn:condition>

        <? if (isEnabled(1)) : ?>
          <!--li class="rn_NavigationTab">
            <div class="rn_NavigationTab">
              <rn:widget path="navigation/NavigationTab" label_tab="Servicio al Cliente" link="/app/sv/request/contact" pages="sv/request/contact" />
            </div>
          </li -->
        <? endif; ?>


        <rn:condition logged_in="true">
          <? if (isEnabled(7)) : ?>
            <!-- Administración -->
            <li class="rn_NavigationTab sublevel">
              <div class="rn_NavigationTab">
                <h2>Administración</h2>
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
        </rn:condition>

        <rn:condition logged_in="true">
          <h2>Mi Cuenta</h2>
          <li class="rn_NavigationTabAccount">
            <a href="#">Mi Cuenta</a>
            <ul class="submenu reset-list rn_Hidden">
              <li>
                <rn:widget path="navigation/NavigationTab" label_tab="#rn:msg:ACCOUNT_OVERVIEW_LBL#" link="/app/account/overview" pages="account/overview" />
              </li>
              <li>
                <rn:widget path="navigation/NavigationTab" label_tab="#rn:msg:ACCOUNT_SETTINGS_LBL#" link="/app/account/profile" pages="account/profile" />
              </li>
            </ul>
          </li>
        </rn:condition>
      <? endif; ?>
    </ul>
  </div>
  <? // FIN MENÚ MÓVIL ############################################ 
  ?>

  <input type="checkbox" id="nav-trigger" class="nav-trigger" />
  <label for="nav-trigger" class="labelNavTrigger rn_Sprite"></label>

  <div class="rn_Wrap">
    <header>
      <div class="rn_TopBar">

        <div class="rn_Container">
          <rn:widget path="custom/social/SocialNetworks" />
          <rn:condition logged_in="false">
         
          <rn:widget path="custom/menu/SearchQuestions" />
          <rn:widget path="custom/menu/AccountLogin" />
          </rn:condition>
          <rn:condition logged_in="true">
            <rn:widget path="custom/menu/AccountItem" />
            
          </rn:condition>
          
          <rn:widget path="custom/login/LoginStatus" in_line="true" show_login="false" />
        </div>
      </div>

      <div class="rn_Container row">
        <div class="rn_Brand rn_FloatLeft small-12">
          <a id="logo" class="rn_Sprite" href="/app/sv/home"></a>
         
        </div>
        
      </div>
      
      <nav class="rn_Container row">
        <div class="rn_NavigationBar">
          <ul class="rn_NavigationMenu reset-list">

            <rn:condition logged_in="true">

              <!-- Inicio -->
              <li class="rn_NavigationTab">
                <div class="rn_NavigationTab">
                  <rn:widget path="navigation/NavigationTab" label_tab="Inicio" link="/app/sv/helpcenter" pages="sv/sv/helpcenter" />
                </div>
              </li>

              <!-- Facturación y Pagos -->
              <? if (isEnabled(5) || isEnabled(8) || isEnabled(6)) : ?>
                <li class="rn_NavigationTab sublevel">
                  <div class="rn_NavigationTab">
                    <!--a href="#FacturaPagos">Facturación y Pagos</a-->
                    <a href="/app/sv/billing_payments/invoice_payments">Facturación y Pagos</a>
                  </div>
                  <ul class="rn_SubNavigation submenu">
                    <? if (isEnabled(5) || isEnabled(6)) : ?>
                      <li>
                        <rn:widget path="navigation/NavigationTab" label_tab="Resumen Mensual" link="/app/sv/billing_payments/billing_payments" pages="/app/sv/billing_payments/billing_payments" />
                      </li> 
                    <? endif; ?>
                    <? if (isEnabled(8)) : ?>
                      <li>
                        <rn:widget path="navigation/NavigationTab" label_tab="Últimos Documentos" link="/app/sv/billing_payments/invoice_payments" pages="/app/sv/billing_payments/invoice_payments" />
                      </li>
                    <? endif; ?>
                     <li>
                        <rn:widget path="navigation/NavigationTab" label_tab="Ingreso de Contadores" link="https://www.dimacofi.cl/centro-de-ayuda/#tabCont" target="_blank" />
                      </li>
                    
                  </ul>
                </li>
              <? endif; ?>

              <? if (isEnabled(3)) : ?>

              <li class="rn_NavigationTab sublevel">
                <div class="rn_NavigationTab">
                  <!--a href="#SoporteTecnico">Solicitudes</a-->
                  <a href="/app/sv/request/history">Insumos</a>
                </div>
                <ul class="rn_SubNavigation submenu">
                  <li>
                    
                      <rn:widget path="navigation/NavigationTab" label_tab="Insumos" link="/app/sv/supplier/form" pages="sv/sv/supplier/form" />
                
                      <rn:widget path="navigation/NavigationTab" label_tab="Insumos Múltiples" link="/app/sv/supplier/form_multiple" pages="sv/supplier/form_multiple" />
                
                      <rn:widget path="navigation/NavigationTab" label_tab="Tracking Insumos" link="https://www.dimacofi.cl/centro-de-ayuda/#tabTrack" pages="https://www.dimacofi.cl/centro-de-ayuda/#tabTrack" target="_blank"/>
                    
                    <rn:widget path="navigation/NavigationTab" label_tab="Mis Solicitudes" link="/app/sv/request/history" pages="sv/request/history" />
                  </li>
                </ul>
              </li>

              <? endif; ?>
              <!-- Solicitudes -->
              <li class="rn_NavigationTab sublevel">
                <div class="rn_NavigationTab">
                  <!--a href="#SoporteTecnico">Solicitudes</a-->
                  <a href="/app/sv/request/history">Solicitudes</a>
                </div>
                <ul class="rn_SubNavigation submenu">
                  <li>
                  <? if (isEnabled(2)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="Soporte Técnico" link="/app/sv/request/technical" pages="sv/request/technical" />
                      <rn:widget path="navigation/NavigationTab" label_tab="Busqueda Equipos" link="/app/sv/request/asset_search" pages="sv/request/asset_search" />
                    <? endif; ?>
                    <? if (isEnabled(9)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="RPA" link="/app/sv/request/Digital_Support/p/68/c/89" pages="sv/request/Digital_Support" /><? endif; ?>
                    <? if (isEnabled(10)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="Aula Digital" link="/app/sv/request/Digital_Support/p/68/c/91" pages="sv/request/Digital_Support" /><? endif; ?>
                    <? if (isEnabled(11)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="Firma Electrónica" link="/app/sv/request/Digital_Support/p/68/c/92" pages="sv/request/Digital_Support" /><? endif; ?>
                    <? if (isEnabled(12)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="Gestion Documental" link="/app/sv/request/Digital_Support/p/68/c/90" pages="sv/request/Digital_Support" /><? endif; ?>
                      <? if (isEnabled(13)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="BPO" link="/app/sv/request/Digital_Support/p/68/c/93" pages="sv/request/Digital_Support" /><? endif; ?>
                      <? if (isEnabled(16)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="BPO" link="/app/sv/request/Digital_Support/p/68/c/93" pages="sv/request/Digital_Support" /><? endif; ?>
    
                    <rn:widget path="navigation/NavigationTab" label_tab="Mis Solicitudes" link="/app/sv/request/history" pages="sv/request/history" />
                     
                  </li>
                </ul>
              </li>
           <!-- Servicio al Cliente -->
              <? if (isEnabled(1)) : ?>    
                <!--li class="rn_NavigationTab">
                  <div class="rn_NavigationTab">
                  <a href="#SoporteTecnico">Servicio al Cliente</a>
                  </div>
                </li-->
                <li class="rn_NavigationTab sublevel">
                <div class="rn_NavigationTab">
                  <!--a href="#SoporteTecnico">Solicitudes</a-->
                  <a href="/app/sv/request/history">Tu Opinión</a>
                </div>
                <ul class="rn_SubNavigation submenu">
                  <li>
                    <? if (isEnabled(1)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="Felicitaciones" link="/app/sv/request/contact/p/67" pages="sv/request/technical" /><? endif; ?>
                    <? if (isEnabled(1)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="Sugerencias" link="/app/sv/request/contact/p/50" pages="sv/request/Digital_Support" /><? endif; ?>
                    <? if (isEnabled(1)) : ?>
                      <rn:widget path="navigation/NavigationTab" label_tab="Reclamos" link="/app/sv/request/contact/p/66" pages="sv/request/Digital_Support" /><? endif; ?>
                  </li>
                </ul>
              </li>
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
                  <a href="/app/sv/Questions">Ayuda</a>
                </div>
              </li>
            </rn:condition>
          </ul>
        </div>
      </nav>
      
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
          <div class="rn_Copy">
            © <?= date('Y') ?> Dimacofi S.A.
          </div>
        </div>

        <div class="rn_MediumFullWidth">
          <div class="rn_FooterLinks rn_MediumTextCenter">

            <div class="rn_FooterLinksItem">
              <a href="http://www.dimacofi.cl/">
                <div class="rn_FooterLinksItem-media ico_footerHyperlink rn_Sprite">
                  &nbsp;
                </div>
                <div class="rn_FooterLinksItem-body rn_SmallHide">
                  <div class="rn_FooterLinksItem-title">
                    www.dimacofi.cl
                  </div>
                </div>
              </a>
            </div>

            <div class="rn_FooterLinksItem">
              <a href="mailto:contacto@dimacofi.cl">
                <div class="rn_FooterLinksItem-media ico_footerEmail rn_Sprite">
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
</body>

</html>