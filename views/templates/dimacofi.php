 <!DOCTYPE html>
<html lang="#rn:language_code#">
<rn:meta javascript_module="standard"/>

<head>
    <?php $this->load->helper('utils'); ?>
    <meta charset="utf-8"/>
    <title><rn:page_title/></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="/euf/assets/images/apple-touch-icon.png">
    <link rel="icon" href="/euf/assets/images/favicon.png" type="image/png"/>
    <link rel="icon" sizes="192x192" href="/euf/assets/images/icon_hires.png">
    <meta name="theme-color" content="#000000">
    <meta name="msapplication-navbutton-color" content="#000000">
    <meta name="apple-mobile-web-app-status-bar-style" content="#000000">
    <rn:theme path="/euf/assets/themes/dimacofi" css="site.css"/>
    <script src="/euf/assets/js/integer.js"></script>
    <rn:head_content/>
    <rn:widget path="utils/ClickjackPrevention"/>
</head>

<body class="yui-skin-sam yui3-skin-sam">
<a href="#rn_MainContent" class="rn_SkipNav rn_ScreenReaderOnly">#rn:msg:SKIP_NAVIGATION_CMD#</a>

<div class="navigationOffCanvas">
  <ul class="rn_NavigationMenuOffCanvas reset-list">
    <h2>Menú Principal</h2>



    <?php
      $CI = get_instance();
      $a_accountValues = $CI->session->getSessionData('Account_loggedValues');
      //echo "<h2>ALO Menú Principal ------- " .  json_encode(profiling('1'))  . "------------</h2>" ;
     
      // Parche Uso de cookies
      $accountValues  = unserialize($_COOKIE['Account_loggedValues']);
     ?>

    <? if(profiling('1')): ?>

    <?if($a_accountValues['ProfileID']==41 || $a_accountValues['ProfileID']==76)
     {?>
      <li><rn:widget path="navigation/NavigationTab" label_tab="Logistica" link="/app/reparacion/logistica" pages="reparacion/logistica"/></li>
     <?}
     else {
     ?>
    <li><rn:widget path="navigation/NavigationTab" label_tab="Atenciones Terreno" link="/app/reparacion/last_request" pages="reparacion/last_request"/></li>
    <li><rn:widget path="navigation/NavigationTab" label_tab="Atenciones Remoto" link="/app/reparacion/ar_request" pages="reparacion/ar_request"/></li>
    <li><rn:widget path="navigation/NavigationTab" label_tab="Mis Reparaciones" link="/app/reparacion/home" pages="reparacion/home"/></li>
    <li><rn:widget path="navigation/NavigationTab" label_tab="Mis Reparaciones Cargo" link="/app/reparacion/cargo" pages="reparacion/cargo"/></li>
    
      
    <li><rn:widget path="navigation/NavigationTab" label_tab="Buscador de HH" link="/app/reparacion/hh/list" pages="reparacion/hh/list"/></li>
    <li><rn:widget path="navigation/NavigationTab" label_tab="Preguntas Frecuentes" link="/app/reparacion/answers/list" pages="reparacion/answers/list"/></li>
    
    <!--li><rn:widget path="navigation/NavigationTab" label_tab="Soporte Programado" link="/app/reparacion/special/special_suport" pages="reparacion/special/special_suport"/></li-->
    <?
  
     }
    ?>
    <? else: ?>
    <rn:condition logged_in="true">
    <li><rn:widget path="navigation/NavigationTab" label_tab="Principal" link="/app/home" pages="home"/></li>
    <rn:condition_else>
    <li><rn:widget path="navigation/NavigationTab" label_tab="Iniciar Sesión" link="/app/reparacion/login" pages="reparacion/login"/></li>
    </rn:condition>
    <rn:condition logged_in="true">
    <h2>Mi Cuenta</h2>
    <li class="rn_NavigationTabAccount">
        <a href="#">Mi Cuenta</a>
        <ul class="submenu reset-list rn_Hidden">
           <li>
             <rn:widget path="navigation/NavigationTab" label_tab="#rn:msg:ACCOUNT_OVERVIEW_LBL#" link="/app/account/overview" pages="account/overview"/>
           </li>
           <li>
             <rn:widget path="navigation/NavigationTab" label_tab="#rn:msg:ACCOUNT_SETTINGS_LBL#" link="/app/account/profile" pages="account/profile"/>
           </li>
        </ul>
    </li>
  </rn:condition>
    <? endif; ?>

  </ul>
</div>

<input type="checkbox" id="nav-trigger" class="nav-trigger" />
<label for="nav-trigger" class="labelNavTrigger rn_Assets"></label>

<div class="rn_Wrap">
  <header>
    <div class="rn_TopBar">
      <div class="rn_Container">
        <? if(profiling('1')): ?>
          <rn:widget path="custom/login/LoginAccount" in_line="true" />
        <? elseif(profiling('2')): ?>
          <rn:widget path="custom/login/LoginStatus" in_line="true" />
        <? endif; ?>
      </div>
    </div>

    <div class="rn_Container row">
      <div class="rn_Brand rn_FloatLeft small-12">
        <a id="logo" class="rn_Assets" href="/"></a>
      </div>
    </div>

    <nav class="rn_Container row">
        <div class="rn_NavigationBar">
            <ul class="rn_NavigationMenu reset-list">
                <? if(profiling('1')): ?>
                <?if($a_accountValues['ProfileID']==41 || $a_accountValues['ProfileID']==76)
                 {?>
                  <li><rn:widget path="navigation/NavigationTab" label_tab="Logistica" link="/app/reparacion/logistica" pages="reparacion/logistica"/></li>
                 <?}
                 else {
                 ?>
                 
                <li><rn:widget path="navigation/NavigationTab" label_tab="Atenciones Terreno" link="/app/reparacion/last_request" pages="reparacion/last_request"/></li>
                <li><rn:widget path="navigation/NavigationTab" label_tab="Atenciones Remoto" link="/app/reparacion/ar_request" pages="reparacion/ar_request"/></li>
                <li><rn:widget path="navigation/NavigationTab" label_tab="Mis Reparaciones" link="/app/reparacion/home" pages="reparacion/home"/></li>
                <li><rn:widget path="navigation/NavigationTab" label_tab="Mis Rep. Cargo" link="/app/reparacion/cargo" pages="reparacion/cargo"/></li>
                
                <li><rn:widget path="navigation/NavigationTab" label_tab="Buscador de HH" link="/app/reparacion/hh/list" pages="reparacion/home"/></li>
								<li><rn:widget path="navigation/NavigationTab" label_tab="Preguntas Frecuentes" link="/app/reparacion/answers/list" pages="reparacion/answers/list"/></li>
              
                <!--li><rn:widget path="navigation/NavigationTab" label_tab="Soporte Programado" link="/app/reparacion/special/special_suport" pages="reparacion/special/special_suport"/></li-->
                <?
                 
                 }
                ?>
                <? else: ?>
                <rn:condition logged_in="true">
                <li><rn:widget path="navigation/NavigationTab" label_tab="Principal" link="/app/home" pages="home"/></li>
                <rn:condition_else>
                <li><rn:widget path="navigation/NavigationTab" label_tab="Iniciar Sesión" link="/app/reparacion/login" pages="reparacion/login"/></li>
                </rn:condition>
                <rn:condition logged_in="true">
                <li class="rn_NavigationTabAccount">
                    <a href="#">Mi Cuenta</a>
                    <ul class="submenu reset-list rn_Hidden">
                       <li>
                         <rn:widget path="navigation/NavigationTab" label_tab="#rn:msg:ACCOUNT_OVERVIEW_LBL#" link="/app/account/overview" pages="account/overview"/>
                       </li>
                       <li>
                         <rn:widget path="navigation/NavigationTab" label_tab="#rn:msg:ACCOUNT_SETTINGS_LBL#" link="/app/account/profile" pages="account/profile"/>
                       </li>
                    </ul>
                </li>
              </rn:condition>
                <? endif; ?>
            </ul>
        </div>
    </nav>
  </header>

  <div class="rn_Body row">
      <div class="rn_MainColumn" role="main">
          <a id="rn_MainContent"></a>
          <rn:page_content/>
      </div>
  </div>

  <footer class="rn_Footer">
      <div class="rn_Container row">

        <div class="rn_FloatLeft rn_SmallTextCenter rn_SmallFullWidth">
         <div class="rn_Copy">
           © 2016 Dimacofi S.A.
         </div>
       </div>

       <div class="rn_FloatRight rn_MediumFullWidth">
           <div class="rn_FooterLinks rn_MediumTextCenter">

             <div class="rn_FooterLinksItem">
               <a href="http://www.dimacofi.cl/">
                 <div class="rn_FooterLinksItem-media ico_footerHyperlink rn_Assets">
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
               <a href="tel:6006001001">
               <div class="rn_FooterLinksItem-media ico_footerPhone rn_Assets">
                 &nbsp;
               </div>
               <div class="rn_FooterLinksItem-body rn_SmallHide">
                 <div class="rn_FooterLinksItem-title">
                     Llámenos al 600 600 1001
                 </div>
               </div>
             </a>
             </div>

             <div class="rn_FooterLinksItem">
               <? if(profiling('1')): ?>
                <a href="/app/reparacion/home">
               <? else: ?>
                <a href="/app/reparacion/login">
               <? endif; ?>
                 <div class="rn_FooterLinksItem-media ico_footerIntranet rn_Assets">
                     &nbsp;
                 </div>
                 <div class="rn_FooterLinksItem-body rn_MediumHide">
                   <div class="rn_FooterLinksItem-title">
                       Intranet Técnicos
                   </div>
                 </div>
               </a>
             </div>

             <div class="rn_FooterLinksItem">
               <a href="mailto:contacto@dimacofi.cl">
                 <div class="rn_FooterLinksItem-media ico_footerEmail rn_Assets">
                     &nbsp;
                 </div>
                 <div class="rn_FooterLinksItem-body rn_MediumHide">
                 </div>
               </a>
             </div>

             <div class="rn_FooterLinksItem">
               <a href="https://twitter.com/empresadimacofi">
                 <div class="rn_FooterLinksItem-media ico_footerTwitter rn_Assets">
                     &nbsp;
                 </div>
                 <div class="rn_FooterLinksItem-body rn_MediumHide">
                 </div>
               </a>
             </div>

             <div class="rn_FooterLinksItem">
               <a href="https://www.linkedin.com/company/dimacofi-s-a">
                 <div class="rn_FooterLinksItem-media ico_footerLinkedin rn_Assets">
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
