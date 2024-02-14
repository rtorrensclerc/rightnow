<?
    function select_option($ops,$id,$name)
    {
      $selc='';
      $i=0;
      echo '<select  required="true" id="' . $name . '">';
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
    <h2>Detalle de la Solicitud [<?=$this->data['order']->CustomFields->c->seguimiento_tecnico->LookupName?>]</h2>
    <div class="rn_FieldDisplay rn_Output">
            <rn:widget path="custom/input/InputField" id="Asunto" name="Asunto" label_input="Asunto" value="#rn:php:$this->data['order']->Subject#" display_type="text" required="true" wide="true" read_only="true"/>
    </div>
    <div class="rn_FieldDisplay rn_Output">
            <rn:widget path="custom/input/InputField" id="Cliente" name="Cliente" label_input="Cliente" value="#rn:php:$this->data['order']->CustomFields->DOS->Direccion->Organization->LookupName#" display_type="text" required="true" wide="true" read_only="true"/>
    </div>
    <div class="rn_FieldDisplay rn_Output">
            <rn:widget path="custom/input/InputField" id="RUTCliente" name="RUTCliente" label_input="RUT Cliente" value="#rn:php:$this->data['order']->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut#" display_type="text" required="true" wide="false" read_only="true"/>
    </div>


    <?


         
         if(strlen($this->data['order']->CustomFields->c->predictiondata)==0)
         {

            /*
                direccion
                comuna
                Region
                Nueva_Etiqueta
                Cambio_Etiqueta
                Estado_Conexion
                Estado_General
                motivo_solucion
                Phone
                Alerta_insumo
            */
            $specialvalues='{"Direccion":".","Comuna":".","Region":".","Nueva_Etiqueta":"100","Cambio_Etiqueta":"100","Estado_Conexion":"100","Estado_General":"100","motivo_solucion":"100","Phone":".","Alerta_Insumo":"100","IgualSerie":"100"}';
         }
         $specialvalues=json_decode($this->data['order']->CustomFields->c->predictiondata,false);
        
         ?>
       
         

    

    <div class="rn_FieldDisplay rn_Output" id="DireccionDespachoDiv"  <?=$style_dd?> >
       
            <rn:widget path="custom/input/InputField" id="DireccionDespacho" name="DireccionDespacho" label_input="Dirección Despacho" value="#rn:php:$this->data['order']->CustomFields->DOS->Direccion->dir_envio#" display_type="text" required="true" wide="false" read_only="true"/>

    </div>

    
    

    <div class="rn_FieldDisplay rn_Output">
            <rn:widget path="custom/input/InputField" id="TipoContrato" name="TipoContrato" label_input="Tipo Contrato" value="#rn:php:$this->data['order']->CustomFields->c->tipo_contrato#" display_type="text" required="true" wide="false" read_only="true"/>
    </div>
    
    
    
    <div class="rn_FieldDisplay rn_Output">
       
            <rn:widget path="custom/input/InputField" id="Solicitante" name="Solicitante" label_input="Solicitante" value="#rn:php:$this->data['order']->PrimaryContact->LookupName# #rn:php:$this->data['order']->PrimaryContact->Emails[0]->Address# #rn:php:$this->data['order']->PrimaryContact->Phones[0]->Number#" display_type="text" required="true" wide="false" read_only="true"/>

    </div>
    <div class="rn_FieldDisplay rn_Output">
        <span class="rn_DataLabel">Estado</span>
        <div class="rn_DataValue rn_LeftJustify">
            <input type="hidden" id="id_status_prev" value="<?=$this->data['order']->CustomFields->c->seguimiento_tecnico->ID?>">
            <select type="select" id="select_status">
                <option value="<?=$this->data['order']->CustomFields->c->seguimiento_tecnico->ID?>" selected>
                    <?= ($this->data['order']->CustomFields->c->seguimiento_tecnico->LookupName)?$this->data['order']->CustomFields->c->seguimiento_tecnico->LookupName:'(Sin Valor)' ?>
                </option>
                        <?switch ($this->data['order']->CustomFields->c->seguimiento_tecnico->ID) {
                        case 15:
                            echo '<option value="18">Visita Técnico Trabajando</option>';
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
                            
                            echo '<option value="15">Visita Técnico Asignado</option>';
                            echo '<option value="17">Visita  a Re-agendar</option>';
                        break;
                        }
                        }
                    case 43:
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
        <div class="rn_FieldDisplay rn_Output">
            <rn:widget path="custom/input/InputField" id="Modelo" name="Modelo" label_input="Tipo<br>Modelo" value="#rn:php:$this->data['order']->CustomFields->c->modelo_hh#" display_type="text" required="true" wide="false" read_only="true"/>
        </div>
    </div>
    <div class="rn_FieldDisplay rn_Output">
        <div class="rn_FieldDisplay rn_Output">
            <rn:widget path="custom/input/InputField" id="HHMáquina" name="HHMáquina" label_input="HH" value="#rn:php:$this->data['order']->CustomFields->c->id_hh#" display_type="text" required="true" wide="false" read_only="true"/>
        </div>
    </div>
    
    <div class="rn_FieldDisplay rn_Output">
        <div class="rn_FieldDisplay rn_Output">
            <rn:widget path="custom/input/InputField" id="TipoSolicitud" name="TipoSolicitud" label_input="Tipo<br>Solicitud" value="#rn:php:$this->data['order']->Disposition->Name#" display_type="text" required="true" wide="false" read_only="true"/>

            <input type="hidden" id="disposition" value="<?=   $this->data['order']->Disposition->ID ?>">
            <input type="hidden" id="seguimiento_tecnico" value="<?= $this->data['order']->CustomFields->c->seguimiento_tecnico->ID ?>">
            <input type="hidden" id="VisitNumber" value="<?= $this->data['Conditions']->VisitNumber ?>">
            <input type="hidden" id="tipo_id" value="<?= $this->data["order"]->CustomFields->c->tipo->ID ?>">
        </div>
    </div>
    <div class="rn_FieldDisplay rn_Output fullWidth">
        <div class="rn_DataValue rn_LeftJustify">Horario <br> Días de Atención :
            <?=$this->data['order']->CustomFields->DOS->Direccion->Rango_dias->LookupName?></div>
        <div class="rn_DataValue rn_LeftJustify">Mañana: Desde
            <?=$this->data['order']->CustomFields->DOS->Direccion->start_am->LookupName?> Hasta
            <?=$this->data['order']->CustomFields->DOS->Direccion->fin_am->LookupName?></div>
        <div class="rn_DataValue rn_LeftJustify">Tarde: Desde
        
            <?=$this->data['order']->CustomFields->DOS->Direccion->start_pm->LookupName?>
            Hasta<?=$this->data['order']->CustomFields->DOS->Direccion->fin_pm->LookupName?></div>
    </div>


    <?
$x=1;    
$bn=0;
$cl=0;
/*

echo "<br>copia_BN [" .json_encode($this->data['copia_BN']->TipoContador->LookupName) ."]<br>";
echo "copia_Color [" .json_encode($this->data['copia_Color']->TipoContador->LookupName) ."]<br>";
echo "copia_Dupl [" .json_encode($this->data['copia_Dupl']) ."]<br>";
echo "copia_FAncho [" .json_encode($this->data['copia_FAncho']) ."]<br>";
echo "copia_Metro [" .json_encode($this->data['copia_Metro']) ."]<br>";
           
*/            
$c1=$this->data['cont1_hh'] ;
//echo "["  . $c1 . "]";
$c2=$this->data['cont2_hh'] ;
$resultado_x= $this->data['grupo']&$x;
if($resultado_x==1)
{
    $bn=1;
    $cont1_Name=$this->data['copia_BN']->TipoContador->LookupName;
    $cont1_value=$this->data['copia_BN']->Valor;
}
$x=2;
$resultado_x= $this->data['grupo']&$x;


if($resultado_x==2)
{
    $cl=1;

    $cont2_Name=$this->data['copia_Color']->TipoContador->LookupName;
    $cont2_value=$this->data['copia_Color']->Valor;
  

}
$x=4;
$resultado_x= $this->data['grupo']&$x;
if($resultado_x==4)
{
    $cl=1;

    $cont1_Name=$this->data['copia_Dupl']->TipoContador->LookupName;
    $cont1_value=$this->data['copia_Dupl']->Valor;
    if($this->data['copia_Color']->Valor>0)
    {
     $cont2_Name=$this->data['copia_Color']->TipoContador->LookupName;
     $cont2_value=$this->data['copia_Color']->Valor;
    }
}
$x=8;
$resultado_x= $this->data['grupo']&$x;
if($resultado_x==8)
{

    $bn=1;
    $cont1_Name=$this->data['copia_FAncho']->TipoContador->LookupName;
    $cont1_value=$this->data['copia_FAncho']->Valor;
}
$x=16;
$resultado_x= $this->data['grupo']&$x;
if($resultado_x==16)
{

    $cl=1;
    $cont2_Name=$this->data['copia_Metro']->TipoContador->LookupName;
    $cont2_value=$this->data['copia_Metro']->Valor;
}

  if($this->data['order']->CustomFields->c->seguimiento_tecnico->ID==18)
  {
        $style_d="";
  }
  else {
        $style_d='style="display:none"';
  }

?>
  
  <input type="hidden" id="id_cont2_existe" value="<?=($cont2_value)?1:0?>">
  <input type="hidden" id="id_cont1_name" value="<?=$cont1_Name?>">
  <input type="hidden" id="id_cont2_name" value="<?=$cont2_Name?>">
  <input type="hidden" id="id_cont1_value" value="<?=$cont1_value?>">
  <input type="hidden" id="id_cont2_value" value="<?=$cont2_value?>">

  
    

    <div class="rn_FieldDisplay rn_Output" <?= $style_d ?>>
   
        <div class="rn_FieldDisplay rn_Output">
            <rn:widget path="custom/input/InputField" id="EquipoDetenido" name="EquipoDetenido" label_input="Equipo<br>Detenido" value="#rn:php:$this->data['order']->CustomFields->c->equipo_detenido#" display_type="checkbox" required="true" wide="false"/>
        </div>

        <div class="rn_FieldDisplay rn_Output">
            <input type="button" name="btn_contadores" value="Upload Contadores" id="id_btn_contadores">
            <form enctype="multipart/form-data" method="post" id="id_frm_fileinfo_contadores"
                name="frm_fileinfo_contadores">
               
                <label>Seleccionar Archivo :</label>
                <input type="file" accept="image/jpeg" name="file_contadores" required id="id_file_contadores" />
            </form>
        </div>

        
        <div class="rn_FieldDisplay rn_Output">
            <rn:widget path="custom/input/InputField" id="rb_sin_cobertura" name="rb_sin_cobertura" label_input="Sin conexion a Internet" value="#rn:php:$this->data['Conditions']->NoDataMobile#" display_type="checkbox" required="true" wide="false"/>
        </div>
  
           
       
        <div class="rn_FieldDisplay rn_Output">
            <input type="button" name="btn_cobertura" value="Upload OT" id="id_btn_cobertura"
                style=<?=($this->data['Conditions']->NoDataMobile)?'':'display:none;'?>>
            <form enctype="multipart/form-data" method="post" id="id_frm_fileinfo_ot" name="frm_fileinfo_ot"
                style=<?=($this->data['Conditions']->NoDataMobile)?'':'display:none;'?>>
                <label>Seleccionar Archivo :</label>
                <input type="file" accept="image/jpeg" name="fileinfo_ot" required id="id_fileinfo_ot" />
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
<?}

/*?>

       
        <h2>Gastos del incidente </h2>
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
            select_option($gastos, 90,'expend_type');
            ?>

        </div>
        <div class="rn_FieldDisplay rn_Output">
            <span class="rn_DataLabel"></span>
            <input type="HIDDEN" rows="5" cols="50" id="gasto" value="0">
        </div>
        <br>
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

       
*/
?>
        <div align="center" id="myProgress" class="myProgress">
            <div id="myBar" class="myBar">
                <div id="outter_1" class="outter"></div>
            </div>
        </div>

        </div>
        <?if( $this->data['order']->CustomFields->c->seguimiento_tecnico->ID==18)
    {
    ?>


<h2>Resultado</h2>
<div class="rn_FieldDisplay rn_Output">
            
            <span class="rn_DataLabel">Resultado Atención</span>
        <?
            $MotSol=array();
            $MotSol['values'][0]='Sin Valor';
            $MotSol['values'][1]='Exito';
            $MotSol['values'][2]='Fracaso';
    
            $MotSol['id'][0]=0;
            $MotSol['id'][1]=1;
            $MotSol['id'][2]=2;

            select_option($MotSol, $specialvalues->motivo_solucion,'motivo_solucion');

        ?>

    </div>
  
    <h2>Check List </h2>
    
    <!--Acá el Segundo 2do este debe abrirse como una pagina adicional 19/06/2017 R.S. -->
<?
/*
?>
    <div class="rn_FieldDisplay rn_Output">
            
            <span class="rn_DataLabel">Motivo Solución</span>
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
            select_option($MotSol, $this->data['order']->CustomFields->c->motivo_solucion->ID,'motivo_solucion');

        ?>

    </div>
     
?>
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


        $diagnostico['id'][0]=94;
        $diagnostico['id'][1]=53;
        $diagnostico['id'][2]=52;
        $diagnostico['id'][3]=54;
        $diagnostico['id'][4]=55;
        $diagnostico['id'][5]=56;
        $diagnostico['id'][6]=58;
        $diagnostico['id'][7]=61;
        $diagnostico['id'][8]=176; //Agregado 28/08 R.S

      
          ?><span class="rn_DataLabel">Diagnóstico</span>
        <?
          select_option($diagnostico,$this->data['order']->CustomFields->c->diagnostico->ID,'diagnostico');
        ?>
    </div>

    <h2>Condiciones del incidente</h2>
    <!--Acá el T3er bloque este debe abrirse como una pagina adicional 19/06/2017 R.S. -->

    <div class="rn_FieldDisplayFull rn_Output">

        <span class="rn_Label">Descripción del Incidente</span>
        <textarea name="Description" id="Description" rows="8"  cols="80"><?=$this->data['Conditions']->Description?></textarea>
    </div>

    <div class="rn_FieldDisplayFull rn_Output">
        <span class="rn_Label">Solution del Problema</span>
      
        <textarea name="Solution" id="Solution" rows="8" cols="80"><?=$this->data['Conditions']->Solution?></textarea>
    </div>
<?*/
?>
  
    
    <div class="rn_FieldDisplay rn_Output">
        <rn:widget path="custom/input/SelectField" id="Nueva_Etiqueta" name="Nueva_Etiqueta" label_input="¿Requiere Nueva Etiqueta?" value="#rn:php:$specialvalues->Nueva_Etiqueta#" required="true" disabled="false" />
    </div>
    <div class="rn_FieldDisplay rn_Output">
        <rn:widget path="custom/input/SelectField" id="Cambio_Etiqueta" name="Cambio_Etiqueta" label_input="Cambio Etiqueta" value="#rn:php:$specialvalues->Cambio_Etiqueta#" required="true" disabled="false" />
    </div>
    <div class="rn_FieldDisplay rn_Output">
        <rn:widget path="custom/input/SelectField" id="Alerta_Insumo" name="Alerta_Insumo" label_input="Alerta insumo" value="#rn:php:$specialvalues->Alerta_Insumo#" required="true" disabled="false" />
    </div>
    <div class="rn_FieldDisplay rn_Output">
        <rn:widget path="custom/input/SelectField" id="IgualSerie" name="IgualSerie" label_input="¿Coincide Serie Fisica, Lógica, Etiqueta HH?" value="#rn:php:$specialvalues->IgualSerie#" required="true" disabled="false" />
    </div>
    <h2>Dirección</h2>
    <div class="rn_FieldDisplay rn_Output">
        <?
        if($this->data['order']->CustomFields->c->direccion_incorrecta=="")
        {
                $incorrecta=100;
        }
        if($this->data['order']->CustomFields->c->direccion_incorrecta=="0")
        {
            $incorrecta=0;
        }
        if($this->data['order']->CustomFields->c->direccion_incorrecta=="1")
        {
            $incorrecta=1;
        }
        ?>

        <rn:widget path="custom/input/SelectField" id="direccion_incorrecta" name="direccion_incorrecta" label_input="direccion Correcta" value="#rn:php:$incorrecta#" required="true" disabled="false" />
    </div>
    <?
         if($this->data['order']->CustomFields->c->direccion_incorrecta!=true || $this->data['order']->CustomFields->c->direccion_incorrecta=="" )
         {
               $style_dd='';
               $style_di='style="display:none"';
         }
         else {
            $style_dd='style="display:none"';
            $style_di='';
         }
    ?>
    <div class="rn_FieldDisplay rn_Output" id="DireccionCorrectaDiv" <?=$style_di?> >
        
            <rn:widget path="custom/input/InputField" id="Direccion" name="Direccion" label_input="Dirección" value="#rn:php:$specialvalues->Direccion#" display_type="text" required="true" wide="false" read_only="false"/>
      
            <rn:widget path="custom/input/InputField" id="Comuna" name="Comuna" label_input="Comuna" value="#rn:php:$specialvalues->Comuna#" display_type="text" required="true" wide="false" read_only="false"/>
        
            <rn:widget path="custom/input/InputField" id="Region" name="Region" label_input="Región" value="#rn:php:$specialvalues->Region#" display_type="text" required="true" wide="false" read_only="false"/>
    </div>
    <h2>Contacto Tecnico</h2>

   <div class="rn_FieldDisplay rn_Output">
        <span class="rn_DataLabel">Nombre Contacto </span>
        <input type="text" id="Reception_Name"
            value="<?=($this->data['Conditions']->Reception_Name)?$this->data['Conditions']->Reception_Name:'Indicar'?>">
    </div>
    <div class="rn_FieldDisplay rn_Output">
        <span class="rn_DataLabel">Correo Contacto</span>
        <input type="text" id="AlternativeEmails" value="<?=$this->data['Conditions']->AlternativeEmails?>">
    </div>
    <div class="rn_FieldDisplay rn_Output">
        <span class="rn_DataLabel">Telefono Contacto</span>
        <input type="text" id="Phone" value="<?=($specialvalues->Phone)?$specialvalues->Phone:'Indicar'?>">
    </div>
    <h2>Otros Contacto</h2>

    <div class="rn_FieldDisplay rn_Output">
        <span class="rn_DataLabel">Nombre Contacto </span>
        <input type="text" id="Reception_Name2"
            value="<?=($specialvalues->Reception_Name2)?$specialvalues->Reception_Name2:'Indicar'?>">
    </div>
    <div class="rn_FieldDisplay rn_Output">
        <span class="rn_DataLabel">Correo Contacto</span>
        <input type="text" id="AlternativeEmails2" value="<?=$specialvalues->AlternativeEmails2?>">
    </div>
    <div class="rn_FieldDisplay rn_Output">
        <span class="rn_DataLabel">Telefono Contacto</span>
        <input type="text" id="Phone2" value="<?=($specialvalues->Phone2)?$specialvalues->Phone2:'Indicar'?>">
    </div>
    <h2>Contadores</h2>
    <div class="rn_FieldDisplay rn_Output" id="id_contadores1">
        
            <rn:widget path="custom/input/InputField" id="cont1_hha" name="cont1_hha" label_input="#rn:php:$cont1_Name . ' ACTUAL '#" value="#rn:php:$cont1_value#" display_type="number" required="true" wide="false" read_only="true"/>
    </div>
    <div class="rn_FieldDisplay rn_Output" id="id_contadores11">
                <rn:widget path="custom/input/InputField" id="cont1_hh" name="cont1_hh" label_input="#rn:php:$cont1_Name#" value="#rn:php:$c1#" display_type="number" required="true" wide="false" />
    </div>
    
    <?
    

    if($cl)
    {
    ?>
    <div class="rn_FieldDisplay rn_Output" id="id_contadores22">
    <?
    }
    else
    {
    ?>
    <div class="rn_FieldDisplay rn_Output" id="id_contadores22" style="display: none;">
    <?
    }
    ?>
        
            <rn:widget path="custom/input/InputField" id="cont2_hha" name="cont2_hha" label_input="#rn:php:$cont2_Name . ' ACTUAL '#"  value="#rn:php:$cont2_value#" display_type="number" required="true" wide="false" read_only="true" />
    </div>
    <?
    if($cl)
    {
    ?>
    <div class="rn_FieldDisplay rn_Output" id="id_contadores2">
    <?
    }
    else
    {
    ?>
    <div class="rn_FieldDisplay rn_Output" id="id_contadores2" style="display: none;">
    <?
    }
    ?>
        <rn:widget path="custom/input/InputField" id="cont2_hh" name="cont2_hh" label_input="#rn:php:$cont2_Name#" value="#rn:php:$c2#" display_type="number" required="true" wide="false" />
    </div>
    <h2>Condiciones</h2>
    <div class="rn_FieldDisplay rn_Output">
        <rn:widget path="custom/input/SelectField" id="Estado_Conexion" name="Estado_Conexion" label_input="Estado Conexión" value="#rn:php:$specialvalues->Estado_Conexion#" required="true" disabled="false" />
    </div>
<?
if($specialvalues->Estado_Conexion=="3")
{
   $style_dd='';
    
}
else {
   $style_dd='style="display:none"';
}

?>
    <div class="rn_FieldDisplay rn_Output"  id="IPDiv" <?=$style_dd?> >
        <span class="rn_DataLabel">Dirección IP</span>
        <input type="text" id="IpNumber" value="<?=$this->data['Conditions']->IpNumber?>">
    </div>

    <div class="rn_FieldDisplay rn_Output">
        <rn:widget path="custom/input/SelectField" id="Estado_General" name="Estado_General" label_input="Estado General" value="#rn:php:$specialvalues->Estado_General#" required="true" disabled="false" />
    </div>


  
   
   
    
    
  
    <h2>Otros</h2>
    
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

    select_option($OperatingSystem,$this->data['Conditions']->OperatingSystem,'OperatingSystem');
    ?>
    </div>


    <div class="rn_FieldDisplay rn_Output">
        <span class="rn_DataLabel">Copiadora</span>
        <?
        $Copy=array();
        $Copy['values'][0]='SI';
        $Copy['values'][1]='NO';
        $Copy['id'][0]=1;
        $Copy['id'][1]=0;
        select_option($Copy,$this->data['Conditions']->Copy,'Copy');
        ?>
        <br>
        <span class="rn_DataLabel">Scanner</span>
        <?
    $Scan=array();
    $Scan['values'][0]='SI';
    $Scan['values'][1]='NO';
    $Scan['id'][0]=1;
    $Scan['id'][1]=0;
    select_option($Scan,$this->data['Conditions']->Scan,'Scan');
    ?>
        <br>
        <span class="rn_DataLabel">Impresora</span>
        <?
    $Printer=array();
    $Printer['values'][0]='SI';
    $Printer['values'][1]='NO';
    $Printer['id'][0]=1;
    $Printer['id'][1]=0;
    select_option($Printer,$this->data['Conditions']->Printer,'Printer');
    ?>
        <br>
        <span class="rn_DataLabel">Fax</span>
        <?
    $Fax=array();
    $Fax['values'][0]='SI';
    $Fax['values'][1]='NO';
    $Fax['id'][0]=1;
    $Fax['id'][1]=0;
    select_option($Fax,$this->data['Conditions']->Fax,'Fax');
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
            select_option($PrintFlow,$this->data['Conditions']->PrintFlow,'PrintFlow');
            ?>
    </div>

    
    <?
    /*
    ?>
    <div class="rn_FieldDisplay rn_Output">
            <span class="rn_DataLabel">Area</span>
            <input type="text" id="Area" maxlength="40"
                value="<?= ($this->data['Conditions']->Area)?$this->data['Conditions']->Area:'Sin Valor' ?>">
    </div>
    <div class="rn_FieldDisplay rn_Output">
            <span class="rn_DataLabel">Centro de Costo</span>
            <input type="text" id="CostCenter" maxlength="40"
                value="<?=($this->data['Conditions']->CostCenter)?$this->data['Conditions']->CostCenter:'Sin Valor'?>">
    </div>
    <?
    */
    ?>
  


  <div class="rn_FieldDisplay rn_Output">

<rn:widget path="custom/input/InputField" id="ClickScanner" name="ClickScanner" label_input="Consumo Scanner" value="#rn:php:$specialvalues->ClickScanner#" display_type="number" required="true" wide="false" />
</div>
<div class="rn_FieldDisplay rn_Output">

<rn:widget path="custom/input/InputField" id="UsersNumber" name="UsersNumber" label_input="Cantidad Usuarios" value="#rn:php:$specialvalues->UsersNumber#" display_type="number" required="true" wide="false" />
</div>
    
   
    <?
   }

    ?>
    
    <h2></h2>
    <tfoot>

        <input type="button" name="btn_request" value="Guardar">
        <input type="button" name="btn_cancel" value="Cancelar">

    </tfoot>
    <div class="rn_ContentTab_Loading" style="display:none;">
        <rn:widget path="custom/Info/waiting" />
    </div>
</div>