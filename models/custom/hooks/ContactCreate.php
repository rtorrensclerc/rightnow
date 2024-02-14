<?php
namespace Custom\Models\hooks;
use RightNow\Connect\v1_3 as RNCPHP;

class ContactCreate extends \RightNow\Models\Base
{

    function __construct()
    {
      parent::__construct();
    }

    public function associateOrganization(&$a_data)
    {
      $org_id = $a_data['data']->Organization->ID;

      // Asociar Organización según ID temporal
      if (!empty($a_data['data']->CustomFields->c->temp_org_id))
      {
        $a_data['data']->Organization = RNCPHP\Organization::fetch($a_data['data']->CustomFields->c->temp_org_id);
      }

      // Asociar organización según RRDUpdater
      if (!empty($a_data['data']->CustomFields->c->rut_org))
      {
        $rut     = $a_data['data']->CustomFields->c->rut_org;
        $obj_org = RNCPHP\Organization::first("CustomFields.c.rut = '{$rut}'");

        if ($obj_org instanceof RNCPHP\Organization)
        {
          $a_data['data']->Organization = $obj_org;
        }
      }

      $a_data['data']->Login                    = $a_data['data']->Emails[0]->Address;
      // Se bloquea temporalmente
      $a_data['data']->CustomFields->c->blocked = true;

      // Validación para correos electrónicos no corporativos
      $cfg_domain   = RNCPHP\Configuration::fetch(CUSTOM_CFG_BLACK_LIST_DOMAIN);
      $arr_domains  = explode(';',$cfg_domain->Value);
      $pos          = 0;
      $count_domain = 0;
      
      foreach ($arr_domains as $key => $item_domain)
      {
        $pos = strpos(strtoupper($a_data['data']->Emails[0]->Address), strtoupper($item_domain));
        
        if ($pos !== false)
        {
          $count_domain++;
        }
      }
      
      /**
      * 1.- Ver si aquí la organización tiene convenio o no (si tiene convenio = FULL y en caso contrario 7)
      **/
      
      
      $a_data['data']->ContactType->ID                 = 6;
      $a_data['data']->CustomFields->PROF->ProfileType = 4;
      if ($count_domain == 0)
      {
        $a_data['data']->CustomFields->c->blocked        = false;
      }
      else
      {
        $a_data['data']->CustomFields->c->blocked        = true;
      }

      // if ($count_domain == 0)
      // {
      //   if ($a_data['data']->Organization === null)
      //   {    
      //     $a_data['data']->CustomFields->PROF->ProfileType = 7; // Acceso básico
      //   }
      //   else
      //   {
      //     $a_data['data']->CustomFields->PROF->ProfileType = 3; // acceso a toda la sucursal virtual
      //   }
      // }
      // else
      // {
      //   if ($a_data['data']->Organization === null) 
      //   {
      //     $a_data['data']->CustomFields->PROF->ProfileType = 7; // Acceso básico
      //   } 
      //   else 
      //   {
      //     $a_data['data']->CustomFields->PROF->ProfileType = 4; // acceso a solicitudes, insumos y soporte
      //   }
      // }

    }

}
