<?
    // PROD
    //$a_accountValues  = unserialize($_COOKIE['Account_loggedValues']);

function select_option($ops,$id,$name,$status)
    {
      $selc='';
      $i=0;

      if($status)
      {
        echo '<select  disabled="true" required="true" id="' . $name .  '" name="' . $name .  '">';
      }
      else
      {
        echo '<select   required="true" id="' . $name .  '" name="' . $name .  '">';
      }



      foreach ($ops['id'] as $key => $value) {
        $selc='';
        if($value==$id)
        {
          $selc='selected';
        }
        echo '<option value="' . $ops['id'][$i]  . '" ' . $selc . '>'  . $ops['values'][$i]  .  '</option>';
        $i++;
      }
      echo '</select>';
    }
?>

<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

  <h2>Detalle de la Solicitud [<?=$this->data['order']->CustomFields->c->seguimiento_tecnico->LookupName?>] - <?=$this->data['Conditions']->VisitNumber?> <?=$this->data["order"]->CustomFields->c->equipo_detenido?> - <?=$this->data['firma'] ?>  -<?=$this->data["order"]->CustomFields->c->tipo->ID?> </h2>
   
  <?

  if($this->data['order']->nbp->status->NUBEPRINT<>'NO MONITOREADO' and $this->data['order']->nbp)
  {
    ?>
    <div class="estadotable">
    <h3>ESTADO : <?echo $this->data['order']->nbp->status->STATUSOFFLINE?></h3>
    <h3>ULTIMA CONEXION : <?echo $this->data['order']->nbp->status->ULTIMA_CONEXION?></h3>
    <h3>DIAS ULTIMA CONEXION:<?echo $this->data['order']->nbp->status->DIAS_OFFLINE?></h3>
    <h3>DIRECCION IP : <?echo $this->data['order']->nbp->status->IP?></h3>
</div>

<? }
else
{?>
<div class="estadotable">
    <h3>ESTADO : Equipo NO monitoreado</h3>
    
</div>

<?}?>
<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel">Asunto</span>
  <div class="rn_DataValue rn_LeftJustify"><?=$this->data['order']->Subject ?></div>
</div>


<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel">Cliente</span>
  <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->DOS->Direccion->Organization->LookupName)?$this->data['order']->CustomFields->DOS->Direccion->Organization->LookupName:'(Sin Valor)' ?></div>
</div>
<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel">RUT Cliente</span>
  <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut)?$this->data['order']->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut:'(Sin Valor)' ?></div>
</div>
<div class="rn_FieldDisplay rn_Output fullWidth">
  <span class="rn_DataLabel">Dirección Despacho</span>
  <div class="rn_DataValue rn_LeftJustify"><?
    if($this->data['order']->CustomFields->c->direccion_incorrecta)
    {
       echo $this->data['order']->CustomFields->c->direccion_correcta;
    }
    else
    {
      echo $this->data['order']->CustomFields->DOS->Direccion->dir_envio . '<br>' . $this->data['order']->CustomFields->DOS->Direccion->ebs_comuna . '<br>' . $this->data['order']->CustomFields->DOS->Direccion->ebs_region;    
    }  
  ?></div>



</div>

<div class="rn_FieldDisplay rn_Output fullWidth">

  <div class="rn_DataValue rn_LeftJustify">Horario <br> Días de Atención : <?=$this->data['order']->CustomFields->DOS->Direccion->Rango_dias->LookupName?></div>
  <div class="rn_DataValue rn_LeftJustify">Mañana: Desde <?=$this->data['order']->CustomFields->DOS->Direccion->start_am->LookupName?> Hasta <?=$this->data['order']->CustomFields->DOS->Direccion->fin_am->LookupName?></div>
  <div class="rn_DataValue rn_LeftJustify">Tarde: Desde <?=$this->data['order']->CustomFields->DOS->Direccion->start_pm->LookupName?> Hasta<?=$this->data['order']->CustomFields->DOS->Direccion->fin_pm->LookupName?></div>

</div>


<div class="rn_FieldDisplay rn_Output ">
  <span class="rn_DataLabel">Solicitante</span>
  <div class="rn_DataValue rn_LeftJustify"><?=$this->data['order']->PrimaryContact->LookupName?><br><?=$this->data['order']->PrimaryContact->Emails[0]->Address?><br>
  <a href="tel:+34555005500"><?=$this->data['order']->PrimaryContact->Phones[0]->Number?></a></div>
</div>

<?if(1)
{
?>
<div class="rn_FieldDisplay rn_Output ">
  <span class="rn_DataLabel">Contacto</span>
  <div class="rn_DataValue rn_LeftJustify"><?=$this->data['order']->CustomFields->c->shipping_instructions?></div>
</div>
<?
}

?>

<div class="rn_FieldDisplay rn_Output fullWidth">
  <span class="rn_DataLabel">Tipo Contrato</span>
  <div class="rn_DataValue rn_LeftJustify"><?= $this->data['order']->CustomFields->c->tipo_contrato ?>
    <input type="hidden" id="tipo_contrato"  value="<?=$this->data['order']->CustomFields->c->tipo_contrato ?>">
  </div>
</div>
<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel">Estado</span>
  <div class="rn_DataValue rn_LeftJustify">
   <input type="hidden" id="id_status_prev"  value="<?=$this->data['order']->CustomFields->c->seguimiento_tecnico->ID?>">
    <select type="select" id="select_status" >
      <option value="<?=$this->data['order']->CustomFields->c->seguimiento_tecnico->ID?>" selected><?= ($this->data['order']->CustomFields->c->seguimiento_tecnico->LookupName)?$this->data['order']->CustomFields->c->seguimiento_tecnico->LookupName:'(Sin Valor)' ?></option>
      <?switch ($this->data['order']->CustomFields->c->seguimiento_tecnico->ID) {
        case null:
          echo '<option value="18">Visita Técnico Trabajando</option>';
          echo '<option value="17">visita a Re-Agendar</option>';
          echo '<option value="43">PRE SOLICITUD DE REPUESTOS</option>';
          break;
        case 15:
          echo '<option value="16">Visita Técnico En ruta</option>';
        break;
        case 16:
          echo '<option value="18">Visita Técnico Trabajando</option>';
          echo '<option value="17">visita a Re-Agendar</option>';
        //  echo '<option value="15">Visita Técnico Asignado</option>';
        break;
        case 18:
          if ($this->data["order"]->CustomFields->c->tipo->ID==34 or( $this->data['order']->Disposition->ID==82 or $this->data['order']->Disposition->ID==83 or $this->data['order']->Disposition->ID==84 or $this->data['order']->Disposition->ID==85))
          {
            echo '<option value="19">Visita Finalizada</option>';
          }
          else
          {
          // Debe vailidar si ya esta formado el Documento o no hay coneccion  y so estan los contadores adjuntos para la siguente etapa
          if($this->data['firma']==1 and $this->data['Contadores']==1 )
          {
              echo '<option value="19">Visita Finalizada</option>';
              if($this->data['order']->CustomFields->c->tipo_contrato=='Cargo' && ($this->data['order']->Disposition->ID<>27  && $this->data['order']->Disposition->ID<>28))
              {
                echo '<option value="24">Visita CARGO Presupuesto</option>';
              }
              else
              {
               if(($this->data['order']->Disposition->ID<>27  && $this->data['order']->Disposition->ID<>28))
               {
                echo '<option value="43">PRE SOLICITUD DE REPUESTOS</option>';
               }
              }
              echo '<option value="15">Visita Técnico Asignado</option>';
              echo '<option value="17">Visita  a Re-agendar</option>';
           break;
          }
        }
        case 43:
          /* Tipo de Soporte es Sopote AR*/
          if($this->data["order"]->CustomFields->c->support_type->ID==272)
          {
             echo '<option value="43">PRE SOLICITUD DE REPUESTOS</option>';
             echo '<option value="19">Visita Finalizada</option>';
          }
          echo '<option value="18">Visita Técnico Trabajando</option>';
          break;
       case 24:
              echo '<option value="18">Visita Técnico Trabajando</option>';
             break;
       case 17:
           echo '<option value="16">Visita Técnico En ruta</option>';
           break;
       case 96:
          echo '<option value="98">Despacho Entregado</option>';
           break;
       case 97:
         echo '<option value="98">Despacho Entregado</option>';
       break;
      }
     ?>
    </select>
  </div>
</div>
<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel">Modelo</span>
  <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->c->modelo_hh)?$this->data['order']->CustomFields->c->modelo_hh:'(Sin Valor)' ?></div>
</div>
<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel">HH Máquina</span>
  <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->c->id_hh)?$this->data['order']->CustomFields->c->id_hh:'(Sin Valor)' ?></div>
</div>

<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel">Tipo de Solicitud</span>
  <div class="rn_DataValue rn_LeftJustify"><?= $this->data['order']->Disposition->Name ?></div>
  <input type="hidden" id="disposition"  value="<?=   $this->data['order']->Disposition->ID ?>">
  <input type="hidden" id="seguimiento_tecnico"  value="<?=   $this->data['order']->CustomFields->c->seguimiento_tecnico->ID ?>">
  <input type="hidden" id="VisitNumber"  value="<?=   $this->data['Conditions']->VisitNumber ?>">
  <input type="hidden" id="tipo_id"  value="<?=   $this->data["order"]->CustomFields->c->tipo->ID ?>">

</div>

<?
  if($this->data['order']->CustomFields->c->seguimiento_tecnico->ID==18)
  {
        $style_d="";
  }
  else {
        $style_d='style="display:none"';
  }
?>
<div class="rn_FieldDisplay rn_Output" <?= $style_d ?> >
  <span class="rn_DataLabel">Equipo Detenido</span>

<?
  if($this->data["order"]->CustomFields->c->equipo_detenido)
  {
?>
  <input type="checkbox" id="rb_equipo_detenido" name="rb_equipo_detenido" checked >
<?
}
else
{
 ?>
<input type="checkbox" id="rb_equipo_detenido" name="rb_equipo_detenido" >
<?
}
?>

      <div class="rn_FieldDisplay rn_Output">
        <input type="button" name="btn_contadores" value="Upload Contadores" id="id_btn_contadores" >
        <form enctype="multipart/form-data" method="post" id="id_frm_fileinfo_contadores" name="frm_fileinfo_contadores">
            <label>Seleccionar Archivo :</label>
            <input type="file" accept="image/jpeg" name="file_contadores" required id="id_file_contadores"/>
        </form>
      </div>
</div>


<?

if($this->data['grupo']&1)
{
    $cont1_Name=$this->data['copia_BN']->TipoContador->LookupName;
    $cont1_value=$this->data['copia_BN']->Valor;
}
if($this->data['grupo']&2)
{
    $cont2_Name=$this->data['copia_Color']->TipoContador->LookupName;
    $cont2_value=$this->data['copia_Color']->Valor;

}
if($this->data['grupo']&4)
{
    $cont1_Name=$this->data['copia_Dupl']->TipoContador->LookupName;
    $cont1_value=$this->data['copia_Dupl']->Valor;
    if($this->data['copia_Color']->Valor>0)
    {
     $cont2_Name=$this->data['copia_Color']->TipoContador->LookupName;
     $cont2_value=$this->data['copia_Color']->Valor;
    }
}
if($this->data['grupo']&8)
{
    $cont1_Name=$this->data['copia_FAncho']->TipoContador->LookupName;
    $cont1_value=$this->data['copia_FAncho']->Valor;
}
if($this->data['grupo']&16)
{
    $cont2_Name=$this->data['copia_Metro']->TipoContador->LookupName;
    $cont2_value=$this->data['copia_Metro']->Valor;
}
?>




<div class="rn_FieldDisplay rn_Output" id="id_contadores" >

  <span class="rn_DataLabel"><?=$cont1_Name?></span>
  <div class="rn_DataValue rn_LeftJustify">
    <?if($this->data['order']->CustomFields->c->seguimiento_tecnico->ID==18 ){?>
         <input type="input" id="cont1_hh"  value="<?=   $cont1_value ?>">

   <?}else {?>
          <input type="hidden" id="cont1_hh"  value="<?=   $cont1_value ?>">
          <label ><?=   $cont1_value ?></label>
    <?}?>

  </div>
</div>

<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel"><?=$cont2_Name?></span>
  <div class="rn_DataValue rn_LeftJustify">

    <?if($this->data['order']->CustomFields->c->seguimiento_tecnico->ID==18 && $this->data['grupo']&18){?>
         <input type="input" id="cont2_hh"  value="<?=$cont2_value ?>">

    <?}else {?>
          <input type="hidden" id="cont2_hh"  value="<?=$cont2_value?>">
          <label ><?=$cont2_value?></label>
    <?}?>

  </div>
</div>

<div class="rn_FieldDisplay rn_Output" <?= $style_d ?>>
  <span class="rn_DataLabel">Sin Cobertura ( Sin conexion a Internet)</span>

  <?
    if($this->data['Conditions']->NoDataMobile)
    {
  ?>
    <input type="checkbox" id="rb_sin_cobertura" name="rb_sin_cobertura" checked>
  <?
  }
  else
  {
   ?>
  <input type="checkbox" id="rb_sin_cobertura" name="rb_sin_cobertura" >
  <?
  }
    ?>
</div>
<div class="rn_FieldDisplay rn_Output">
  <input type="button" name="btn_cobertura" value="Upload OT" id="id_btn_cobertura" style=<?=($this->data['Conditions']->NoDataMobile)?'':'display:none;'?>>
  <form enctype="multipart/form-data" method="post" id="id_frm_fileinfo_ot" name="frm_fileinfo_ot" style=<?=($this->data['Conditions']->NoDataMobile)?'':'display:none;'?>>
      <label>Seleccionar Archivo :</label>
      <input type="file" accept="image/jpeg" name="fileinfo_ot" required id="id_fileinfo_ot"/>
  </form>
</div>
<?
if($this->data['order']->FileAttachments ){?>
<div class="rn_FieldDisplay rn_Output">
  <h2>Archivos Adjuntos:   </h2>
<?
$CI = get_instance();
$username = $CI->session->getSessionData("username");
$password = $CI->session->getSessionData("password");

initConnectAPI($username,$password);
if($this->data['order']->FileAttachments)
{
foreach ($this->data['order']->FileAttachments as $file) {
?>
      <a ><?=$file->FileName?></a><br>
<?
}
}
?>
</div>
<?}?>


<h2>Garantia</h2>
<div class="rn_FieldDisplay rn_Output" >

  <?
    if($this->data["order"]->CustomFields->c->req_garantia)
    {
  ?>
  <span class="rn_DataLabel" style="color: red;">Requiere Garantia</span>

    <input type="checkbox" id="rb_sin_cobertura" name="rb_sin_cobertura" checked disabled>
  <?
  }
  else
  {
   ?>
   <span class="rn_DataLabel">Requiere Garantia</span>

  <input type="checkbox" id="rb_sin_cobertura" name="rb_sin_cobertura" disabled>
  <?
  }
    ?>
</div>


<h2>Gastos del incidente  </h2>
<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel">Gasto</span>
    <?  $gastos['values'][0]='Sin Gastos';
        $gastos['values'][1]='Locomoción Local';
        $gastos['values'][2]='Colectivo';
        $gastos['values'][3]='Propinas Estacionamiento';


        $gastos['id'][0]=90;
        $gastos['id'][1]=91;
        $gastos['id'][2]=92;
        $gastos['id'][3]=93;
        select_option($gastos, 90,'expend_type',false);
    ?>

</div>
<div class="rn_FieldDisplay rn_Output">
  <span class="rn_DataLabel"></span>
  <input type="HIDDEN" rows="5"  cols="50"   id="gasto" value="0">
</div>

<div class="rn_FieldDisplayFull rn_Output">
  <span class="rn_Label">Observacion Gastos</span><br>
  <textarea name="gsto_detail" id="gsto_detail" rows="8" cols="80">Sin Observacion</textarea>
</div>
<div class="rn_FieldDisplayFull rn_Output">

<?
  if($this->data['gastos'])
  {
   foreach ($this->data['gastos'] as $key => $value) {
?>
  <?
$texto='';
switch($value->ExpenseType)
{
  case 90:
    $texto='Sin Gastos';
    break;
  case 91:
    $texto='Locomoción Local';
    break;
  case 92:
    $texto='Colectivo';
    break;
  case 93;
    $texto='Propinas Estacionamiento';
    break;
}
?>
  <?=date('Y-m-d H:i:s',$value->CreatedTime)?> : <?=$texto?> : <?=$value->Description?> <br>
<?
   }
  }
?>
</div>


<div align= "center" id="myProgress" class="myProgress">
  <div id="myBar" class="myBar">
    <div id="outter_1" class="outter"></div>
  </div>
</div>

<?if( $this->data['order']->CustomFields->c->seguimiento_tecnico->ID==18)
{
  ?>

  <h2>Antecedentes técnicos </h2> <!--Acá el Segundo 2do este debe abrirse como una pagina adicional 19/06/2017 R.S. -->

  <div class="rn_FieldDisplay rn_Output">

        <?
    
     

          if(($this->data['order']->Disposition->ID<>27  && $this->data['order']->Disposition->ID<>28))
          {
            ?><span class="rn_DataLabel">Motivo Solución</span><?
            $MotSol=array();
            $MotSol['values'][0]='Sin Valor';
            $MotSol['values'][1]='Configuracion de Impresora';
            $MotSol['values'][2]='Cambio de Repuesto y Mantencion';
            $MotSol['values'][3]='Mantención y Ajuste';
            $MotSol['values'][4]='Instalación de equipo';
            $MotSol['values'][5]='Reprogramar visita por motivo de cliente';
            $MotSol['values'][6]='Agregar,Cambiar,Eliminar Usuario';
            $MotSol['values'][7]='Actualización de Firmware';
            $MotSol['values'][8]='Solicitud de repuestos por presupuesto';
            $MotSol['values'][9]='Cambio de repuesto por Garantía';
            $MotSol['values'][10]='Instalación de Accesorio';
            $MotSol['values'][11]='Desactivación';
            $MotSol['values'][12]='Cambio de Equipo';
            $MotSol['values'][13]='Equipo no instalado';
            $MotSol['values'][14]='Solucion Telefonica';
            $MotSol['values'][15]='Cliente Solucionó Problema';
            $MotSol['values'][16]='Ejecucion de Presupuesto';
            $MotSol['values'][17]='Reset Codigo SC';
            $MotSol['values'][18]='Reinicio de Sistema';
            $MotSol['values'][19]='Cambio de insumo';
            $MotSol['values'][20]='Configuración de Scanner';
            $MotSol['values'][21]='Mantenimiento';
    
            $MotSol['id'][0]=95;
            $MotSol['id'][1]=57;
            $MotSol['id'][2]=2;
            $MotSol['id'][3]=3;
            $MotSol['id'][4]=4;
            $MotSol['id'][5]=5;
            $MotSol['id'][6]=6;
            $MotSol['id'][7]=8;
            $MotSol['id'][8]=10;
            $MotSol['id'][9]=11;
            $MotSol['id'][10]=12;
            $MotSol['id'][11]=13;
            $MotSol['id'][12]=14;
            $MotSol['id'][13]=25;
            $MotSol['id'][14]=49;
            $MotSol['id'][15]=50;
            $MotSol['id'][16]=51;
            $MotSol['id'][17]=59;
            $MotSol['id'][18]=60;
            $MotSol['id'][19]=62;
            $MotSol['id'][20]=1;
            $MotSol['id'][21]=124;
            select_option($MotSol, $this->data['order']->CustomFields->c->motivo_solucion->ID,'motivo_solucion',false);
            ?>
            <widget path="custom/input/SelectField" id="hh_brand_list" name="hh_brand_list" label_input="Seleccionar Marca de HH" required="true" disabled="false" />
            <?
          }
          else {
            ?><span class="rn_DataLabel">Motivo Solución</span>
            
            <!--input type="hidden" id="motivo_solucion"  value="<?=4?>"-->
            <?
                $MotSol=array();
                $MotSol['values'][0]='Sin Valor';
                $MotSol['values'][1]='Instalación de equipo';
                $MotSol['values'][2]='Equipo no instalado';
                
        
                $MotSol['id'][0]=95;
                $MotSol['id'][1]=4;
                $MotSol['id'][2]=25;
                if($this->data['order']->CustomFields->c->motivo_solucion->ID==95)
                {
                  $this->data['order']->CustomFields->c->motivo_solucion->ID=4;
                }
            select_option($MotSol, $this->data['order']->CustomFields->c->motivo_solucion->ID,'motivo_solucion',false);
          
          
          }


        ?>

  </div>


  <div class="rn_FieldDisplay rn_Output">

        <?
        $diagnostico=array();
        $diagnostico['values'][0]='Sin Valor';
        $diagnostico['values'][1]='Falla de Equipo';
        $diagnostico['values'][2]='Falla de Operacion';
        $diagnostico['values'][3]='Reinstalacion';
        $diagnostico['values'][4]='Fin Vida Util de Repuestos';
        $diagnostico['values'][5]='Falla Externa';
        $diagnostico['values'][6]='Instalacion';
        $diagnostico['values'][7]='Mantencion Preventiva';
        $diagnostico['values'][8]='Daño inducido por terceros';//Agregado 28/08 R.S
    /*       $diagnostico['values'][9]='Solución AR';
            $diagnostico['values'][10]='Solicitud repuesto AR';
            $diagnostico['values'][11]='Solicitud repuesto AR terreno';
            $diagnostico['values'][12]='Sin solución AR';*/

        $diagnostico['id'][0]=94;
        $diagnostico['id'][1]=53;
        $diagnostico['id'][2]=52;
        $diagnostico['id'][3]=54;
        $diagnostico['id'][4]=55;
        $diagnostico['id'][5]=56;
        $diagnostico['id'][6]=58;
        $diagnostico['id'][7]=61;
        $diagnostico['id'][8]=176; //Agregado 28/08 R.S
   /*     $diagnostico['id'][9]=275;
        $diagnostico['id'][10]=276;
        $diagnostico['id'][11]=277;
        $diagnostico['id'][12]=278;*/

        if(($this->data['order']->Disposition->ID<>27  && $this->data['order']->Disposition->ID<>28))
        {
          ?><span class="rn_DataLabel">Diagnóstico</span><?
          select_option($diagnostico,$this->data['order']->CustomFields->c->diagnostico->ID,'diagnostico',false);
        }
        else {
        ?>
          <input type="hidden" id="diagnostico"  value="58">
        <?
        }

        ?>
  </div>
  <h2>Condiciones del incidente--</h2> <!--Acá el T3er bloque este debe abrirse como una pagina adicional 19/06/2017 R.S. -->

  <div class="rn_FieldDisplayFull rn_Output">

    <span class="rn_Label">Descripción del Incidente</span>
    <?
     if($this->data['order']->Disposition->ID<>27  && $this->data['order']->Disposition->ID<>28)
     {
    ?>
    <textarea name="Description" id="Description" rows="8" cols="80" ><?=$this->data['Conditions']->Description?></textarea>
    <?
    }
     else {
      ?>
       <textarea readonly name="Description" id="Description" rows="8" cols="80" >INSTALACION HH:<?=$this->data['order']->CustomFields->c->id_hh?></textarea>
      <?
     }
    ?>
  </div>

  <div class="rn_FieldDisplayFull rn_Output">
    <span class="rn_Label">Solution del Problema</span>
    <?
     if($this->data['order']->Disposition->ID<>27  && $this->data['order']->Disposition->ID<>28)
     {
    ?>
    <textarea name="Solution" id="Solution" rows="8" cols="80" ><?=$this->data['Conditions']->Solution?></textarea>
    <?
    }
     else {
      ?>
       <textarea  readonly name="Solution" id="Solution" rows="8" cols="80" >NO Aplica</textarea>
      <?
     }
    ?>

  </div>

  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Temperatura ambiente</span>
    <?
      $temperaturas=array();
      $temperaturas['values'][0]='Sin Valor';
      $temperaturas['values'][1]='OK';
      $temperaturas['values'][2]='Exceso Frio';
      $temperaturas['values'][3]='Exceso Calo';
      $temperaturas['id'][0]='SV';
      $temperaturas['id'][1]='OK';
      $temperaturas['id'][2]='EF';
      $temperaturas['id'][3]='EC';
      select_option($temperaturas,$this->data['Conditions']->Temperture,'Temperture',false);
?>
  </div>
    <div class="rn_FieldDisplay rn_Output">

    <?
      $IssueCausa=array();
      $IssueCausa['values'][0]='Sin Valor';
      $IssueCausa['values'][1]='Intervención de Terceros';
      $IssueCausa['values'][2]='Cumple Vida Útil';
      $IssueCausa['values'][3]='Falla Prematura';
      $IssueCausa['values'][4]='Instalación';

      $IssueCausa['id'][0]='SV';
      $IssueCausa['id'][1]='IT';
      $IssueCausa['id'][2]='CV';
      $IssueCausa['id'][3]='FP';
      $IssueCausa['id'][4]='IN';
      if(($this->data['order']->Disposition->ID<>27  && $this->data['order']->Disposition->ID<>28))
      {
        ?><span class="rn_DataLabel">Motivo de la Falla </span><?
        select_option($IssueCausa,$this->data['Conditions']->IssueCausa,'IssueCausa',false);
      }
      else {
        ?>
        <input type="hidden" id="IssueCausa"  value="IN">
        <?
      }
     ?>
  </div>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Condiciones Eléctricas</span>
    <?
      $ElectricalCondition=array();
      $ElectricalCondition['values'][0]='Sin Valor';
      $ElectricalCondition['values'][1]='Normal';
      $ElectricalCondition['values'][2]='Errático';
      $ElectricalCondition['values'][3]='Malas condiciones';
      $ElectricalCondition['id'][0]='SV';
      $ElectricalCondition['id'][1]='NR';
      $ElectricalCondition['id'][2]='ER';
      $ElectricalCondition['id'][3]='MC';
      select_option($ElectricalCondition,$this->data['Conditions']->ElectricalCondition,'ElectricalCondition',false);
    ?>
  </div>

  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Condiciones Ambientales</span>
    <?
      $EnviromentCondit=array();
      $EnviromentCondit['values'][0]='Sin Valor';
      $EnviromentCondit['values'][1]='Exceso de Polución';
      $EnviromentCondit['values'][2]='Exceso de Humedad';
      $EnviromentCondit['values'][3]='Lugar de vibraciones';
      $EnviromentCondit['values'][4]='Exposición directa al Sol';
      $EnviromentCondit['values'][5]='Condiciones adecuadas';
      $EnviromentCondit['id'][0]='SV';
      $EnviromentCondit['id'][1]='EP';
      $EnviromentCondit['id'][2]='EH';
      $EnviromentCondit['id'][3]='LV';
      $EnviromentCondit['id'][4]='ES';
      $EnviromentCondit['id'][5]='CA';
      select_option($EnviromentCondit,$this->data['Conditions']->EnviromentCondit,'EnviromentCondit',false);

    ?>
  </div>

  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Nombre recepción conforme </span>
    <input type="text" id="Reception_Name"  value="<?=($this->data['Conditions']->Reception_Name)?$this->data['Conditions']->Reception_Name:'N/A'?>">
  </div>
  <br>
  <br>
  <div class="rn_FieldDisplayfull rn_Output">
    <span class="rn_DataLabel">Correos Alternativos</span>
    <input type="text" id="AlternativeEmails"  value="<?=$this->data['Conditions']->AlternativeEmails?>">
  </div>
  <?
   }
   if(($this->data['order']->Disposition->ID==27  || $this->data['order']->Disposition->ID==28) & $this->data['order']->CustomFields->c->seguimiento_tecnico->ID==18)
  {
    ?>

  <h2>Datos de Instalacion [<?=$this->data['order']->Disposition->Name?>]   </h2> <!--Acá el 4to bloque este debe abrirse como una pagina adicional,
                                          siempre y cuando sea Disposición = Instalación 19/06/2017 R.S. -->
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Dirección IP</span>
    <input type="text" id="IpNumber" value="<?=$this->data['Conditions']->IpNumber?>">
  </div>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Sistema OP</span>
    <?
    $OperatingSystem=array();
    $OperatingSystem['values'][0]='OTRO';
    $OperatingSystem['values'][1]='WIN 7';
    $OperatingSystem['values'][2]='WIN 8';
    $OperatingSystem['values'][3]='WIN 10';
    $OperatingSystem['values'][4]='MAC';
    $OperatingSystem['values'][5]='DIST. LINUX';
    $OperatingSystem['id'][0]='OTRO';
    $OperatingSystem['id'][1]='WIN 7';
    $OperatingSystem['id'][2]='WIN 8';
    $OperatingSystem['id'][3]='WIN 10';
    $OperatingSystem['id'][4]='MAC';
    $OperatingSystem['id'][5]='DIST. LINUX';

    select_option($OperatingSystem,$this->data['Conditions']->OperatingSystem,'OperatingSystem',false);
    ?>
  </div>

  <p>Funciones habilitadas en la instalacion</p>

  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Copiadora</span>
        <?
        $Copy=array();
        $Copy['values'][0]='SI';
        $Copy['values'][1]='NO';
        $Copy['id'][0]=1;
        $Copy['id'][1]=0;
        select_option($Copy,$this->data['Conditions']->Copy,'Copy',false);
        ?>
    <br>
    <span class="rn_DataLabel">Scanner</span>
    <?
    $Scan=array();
    $Scan['values'][0]='SI';
    $Scan['values'][1]='NO';
    $Scan['id'][0]=1;
    $Scan['id'][1]=0;
    select_option($Scan,$this->data['Conditions']->Scan,'Scan',false);
    ?>
    <br>
    <span class="rn_DataLabel">Impresora</span>
    <?
    $Printer=array();
    $Printer['values'][0]='SI';
    $Printer['values'][1]='NO';
    $Printer['id'][0]=1;
    $Printer['id'][1]=0;
    select_option($Printer,$this->data['Conditions']->Printer,'Printer',false);
    ?>

    <br>
    <span class="rn_DataLabel">Fax</span>
    <?
    $Fax=array();
    $Fax['values'][0]='SI';
    $Fax['values'][1]='NO';
    $Fax['id'][0]=1;
    $Fax['id'][1]=0;
    select_option($Fax,$this->data['Conditions']->Fax,'Fax',false);
    ?>
  </div>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Flujo de impresión</span>
    <?
    $PrintFlow=array();
    $PrintFlow['values'][0]='Sin Valor';
    $PrintFlow['values'][1]='Servidor de Impresión';
    $PrintFlow['values'][2]='Impresión Directa';

    $PrintFlow['id'][0]='SV';
    $PrintFlow['id'][1]='SI';
    $PrintFlow['id'][2]='ID';
    select_option($PrintFlow,$this->data['Conditions']->PrintFlow,'PrintFlow',false);
    ?>
    <div class="rn_FieldDisplay rn_Output">
      <span class="rn_DataLabel">Area</span>
      <input type="text" id="Area"  maxlength= "40" value="<?= ($this->data['Conditions']->Area)?$this->data['Conditions']->Area:'Sin Valor' ?>">
    </div>
    <div class="rn_FieldDisplay rn_Output">
      <span class="rn_DataLabel">Centro de Costo</span>
      <input type="text" id="CostCenter" maxlength= "40"  value="<?=($this->data['Conditions']->CostCenter)?$this->data['Conditions']->CostCenter:'Sin Valor'?>">
    </div>

  </div>
  <?
  }
  ?>
  <br><area shape="default" coords="" href="#" alt="">
  <br>

  <div class="rn_FieldDisplayFull rn_Output">

  <span class="rn_Label">Notas</span><br>
  <textarea name="name" id="nota" rows="8" cols="80"></textarea>
  <br>
  <?
  if($this->data['order']->Threads)
  {
   foreach ($this->data['order']->Threads as $key => $value) {
   if(substr($value->Text,0,2)<>'ID' &&  strlen($value->Text)>0  && strlen(strstr($value->Text,'Llamado de información HH'))==0 &&  strlen(strstr($value->Text,'ingresados correctamente'))==0 &&  strlen(strstr($value->Text,'Dirección asociada al HH'))==0)
   {
  ?>
  <?=date('Y-m-d H:i:s',$value->CreatedTime)?> : <?=$value->Text?><br>
  <?
  }
  }
  }
   ?>
  </div>



  <tfoot>

          <input type="button" name="btn_request" value="Guardar" >
          <input type="button" name="btn_cancel" value="Cancelar" >


          <?
              if($this->data['firma']=="1")
              {
                  $disable='disabled';
              }
              if ($this->data['order']->CustomFields->c->seguimiento_tecnico->ID==18 )
              {
                if ($this->data["order"]->CustomFields->c->tipo->ID==34)
                {

                  ?>
                  <input type="hidden" value="Firma" name="btn_sign" id="id_btn_sign" <?=$disable?> style=<?=($this->data['Conditions']->NoDataMobile)?'display:none;':''?> >
                  <?
                }
                else{
                  ?>
                    <input type="button" value="Firma" name="btn_sign" id="id_btn_sign" <?=$disable?> style=<?=($this->data['Conditions']->NoDataMobile)?'display:none;':''?> >
                  <?
                }
              }

          ?>


  </tfoot>
  </div>
<br>
 <?
 $texto='';
 if($this->data['firma']=="0" and $this->data['order']->CustomFields->c->seguimiento_tecnico->ID==18)
 {
  if($this->data['order']->Disposition->IDi==27 || $this->data['order']->Disposition->ID==28)
         { /* instalaciones */

                 $texto='Este documento acredita la conformidad del servicio de instalación prestado por nuestro representante Dimacofi de acuerdo a los requerimientos solicitados.';
         }
         else{

                 if(strtoupper($this->data['order']->CustomFields->c->tipo_contrato)<>'Cargo')
                 {
                 /*Arriendo Convenio */
                      $texto='Este documento sólo es de uso interno entre Dimacofi y nuestros clientes , donde acreditan la conformidad del servicio prestado en la visita de nuestro Técnico especialista.';
                 }
                 else
                 {
                 /*Cargo */
                      $texto='Este documento acredita la conformidad del servicio prestado en la visita de nuestro Técnico especialista, esto de acuerdo a la solicitud de evaluación con cargo al cliente  realizada a Dimacofi, o la conformidad del presupuesto aceptado.';
                 }
         }
}
?>
  <div class="rn_FieldDisplayFull rn_Output">
  <span class="rn_Label"><?=$texto?></span><br>
  </div>
