<?
    // PROD
    //$a_accountValues  = unserialize($_COOKIE['Account_loggedValues']);

function select_option($ops,$id,$name,$status,$titulo)
    {
      $selc='';
      $i=0;
    
      echo '<span class="rn_DataLabel">'. $titulo .'</span>';
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
  <a href="tel:+34555005500"><?=$this->data['order']->PrimaryContact->Phones[0]->Number?></a>
  </div>
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
      <?
      
      switch ($this->data['order']->CustomFields->c->seguimiento_tecnico->ID) {
        case 299:
          
          
          if($this->data['order']->StatusWithType->Status->ID==198 && $this->data['order']->CustomFields->c->seguimiento_tecnico->ID==299)
          {
            echo '<option value="297">Reagenda asistencia remota</option>';
          }
          break;
        case 298:
          
          echo '<option value="299">Técnico Remoto Trabajando</option>';
          if($this->data['order']->StatusWithType->Status->ID==119 && $this->data['order']->CustomFields->c->seguimiento_tecnico->ID==298)
          {
            echo '<option value="297">Reagenda asistencia remota</option>';
          }
          break;
        case null:
        case 297:
         

          echo '<option value="299">Técnico Remoto Trabajando</option>';
  
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
        case 299:
          if ($this->data["order"]->CustomFields->c->tipo->ID==34 or( $this->data['order']->Disposition->ID==82 or $this->data['order']->Disposition->ID==83 or $this->data['order']->Disposition->ID==84 or $this->data['order']->Disposition->ID==85))
          {
            echo '<option value="19">Visita Finalizada</option>';
          }
        /*  else
          {
          // Debe vailidar si ya esta formado el Documento o no hay coneccion  y so estan los contadores adjuntos para la siguente etapa
           
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
                echo '<option value="297">Reagenda asistencia remota</option>';
            
          }
          echo '<option value="17">visita a Re-Agendar</option>';
*/
          break;
        case 43:
          /* Tipo de Soporte es Sopote AR*/
          
          if($this->data["order"]->CustomFields->c->support_type->ID==272 || $this->data["order"]->CustomFields->c->support_type->ID==274)
          {
             echo '<option value="43">PRE SOLICITUD DE REPUESTOS</option>';
             echo '<option value="19">Visita Finalizada</option>';
          }
          
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
  if($this->data['order']->CustomFields->c->seguimiento_tecnico->ID==299)
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
</div>
<div class="rn_FieldDisplay rn_Output" <?= $style_d ?> >
        <input type="button" name="btn_contadores" value="Upload Contadores" id="id_btn_contadores" >
        <form enctype="multipart/form-data" method="post" id="id_frm_fileinfo_contadores" name="frm_fileinfo_contadores">
            <label>Seleccionar Archivo :</label>
            <input type="file" accept="image/jpeg" name="file_contadores" required id="id_file_contadores"/>
        </form>
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
    <?if($this->data['order']->CustomFields->c->seguimiento_tecnico->ID==299 ){?>
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

    <?if($this->data['order']->CustomFields->c->seguimiento_tecnico->ID==299 && $this->data['grupo']&18){?>
         <input type="input" id="cont2_hh"  value="<?=$cont2_value ?>">

    <?}else {?>
          <input type="hidden" id="cont2_hh"  value="<?=$cont2_value?>">
          <label ><?=$cont2_value?></label>
    <?}?>

  </div>
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


<div align= "center" id="myProgress" class="myProgress">
  <div id="myBar" class="myBar">
    <div id="outter_1" class="outter"></div>
  </div>
</div>

<?
//if( $this->data['order']->CustomFields->c->seguimiento_tecnico->ID==299){
  ?>

  <?echo "..." . $this->data['order']->CustomFields->c->motivo_solucion->ID ;?>

  <div id="att">
  
  <h2>Antecedentes técnicos </h2> <!--Acá el Segundo 2do este debe abrirse como una pagina adicional 19/06/2017 R.S. -->

  <div class="rn_FieldDisplay rn_Output">

        <?
    
     

         
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
            select_option($MotSol, $this->data['order']->CustomFields->c->motivo_solucion->ID,'motivo_solucion',false,'Motivo Solución');

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
        /*$diagnostico['values'][9]='Solución AR';
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
      /*  $diagnostico['id'][9]=275;
        $diagnostico['id'][10]=276;
        $diagnostico['id'][11]=277;
        $diagnostico['id'][12]=278;*/

        select_option($diagnostico,$this->data['order']->CustomFields->c->diagnostico->ID,'diagnostico',false,'Diagnóstico');
        ?>
  </div>
 
  
  <div class="rn_FieldDisplay rn_Output">

        <?
        // Solo le deben aparecer a los tecnios de la cola de AR
        // 
        $cola=array();
        $cola['values'][0]='Sin Valor';
        $cola['values'][1]='Solución AR';
        $cola['values'][2]='Solicitud repuesto AR';
        $cola['values'][3]='Solicitud repuesto AR terreno';
        $cola['values'][4]='Sin solución AR';
     
        

        $cola['id'][0]=null;
        $cola['id'][1]=280;
        $cola['id'][2]=281;
        $cola['id'][3]=282;
        $cola['id'][4]=283;
        
          select_option($cola,$this->data['order']->CustomFields->c->ar_flow->ID,'arflow',false,'Flujo AR');
        ?>
          <input type="hidden" id="QueueID"  value="<?$this->data['order']->Queue->ID?>">

  </div>
  

<div class="rn_FieldDisplay rn_Output">

        <?
        // Solo le deben aparecer a los tecnios de la cola de AR
        // 
        $cola=array();
        $cola['values'][0]='Sin Valor';
        $cola['values'][1]='No contesta';
        $cola['values'][2]='Solicita Tecnico terreno';
        $cola['values'][3]='Modelo no corresponde';
        $cola['values'][4]='Falla de equipo';
     
        

        $cola['id'][0]=300;
        $cola['id'][1]=301;
        $cola['id'][2]=302;
        $cola['id'][3]=303;
        $cola['id'][4]=304;
        
        

        
          select_option($cola,$this->data['order']->CustomFields->c->ar_reason->ID,'motivoar',false,'Motivo');
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
  </div> 
  
  
  <div class="rn_FieldDisplayFull rn_Output">

  <span class="rn_Label">Notas</span><br>
  <textarea name="name" id="nota" rows="8" cols="80"></textarea>
  
  <?
  if($this->data['order']->Threads)
  {
   foreach ($this->data['order']->Threads as $key => $value) {
   if(substr($value->Text,0,2)<>'ID' 
   &&  strlen($value->Text)>0  
   && strlen(strstr($value->Text,'Llamado de información HH'))==0 
   &&  strlen(strstr($value->Text,'ingresados correctamente'))==0 
   &&  strlen(strstr($value->Text,'Dirección asociada al HH'))==0
   &&  strlen(strstr($value->Text,'URL'))==0
   &&  strlen(strstr($value->Text,'DATA'))==0
   &&  strlen(strstr($value->Text,'ENVIANDO '))==0
   &&  strlen(strstr($value->Text,'->'))==0
   &&  strlen(strstr($value->Text,'Inicio Contadores'))==0
   &&  strlen(strstr($value->Text,'Leyendo 1000019'))==0)
   {
  ?>

 <?=date('Y-m-d H:i:s',$value->CreatedTime)?>:<?=$value->Text?><br>
  <?
  }
  }
  }
   ?>
  </div>



  <tfoot>

          <input type="button" name="btn_request" value="Guardar" >
          <input type="button" name="btn_cancel" value="Cancelar" >


          


  </tfoot>
  </div>
<br>

