<?php
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
     <h2>Detalle de la Solicitud<? if($this->data['order']->ID) echo ' ' . $this->data['order']->ID; ?></h2>
     <h3>Estado: <strong><?= ($this->data['order']->StatusWithType->Status->LookupName)?$this->data['order']->StatusWithType->Status->LookupName:'(Sin Valor)' ?></strong></h3>
      <? if($this->data['order']->ReferenceNumber): ?>
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
      <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->DOS->Direccion->dir_envio)?$this->data['order']->CustomFields->DOS->Direccion->dir_envio:'(Sin Valor)' ?></div>
    </div>
    <div class="rn_FieldDisplay rn_Output">
      <span class="rn_DataLabel">Tipo Contrato</span>
      <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->OP->Incident->CustomFields->c->tipo_contrato)?$this->data['order']->CustomFields->OP->Incident->CustomFields->c->tipo_contrato:'(Sin Valor)' ?></div>
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
      <span class="rn_DataLabel">Número de Orden OM</span>
      <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->c->order_number_om)?$this->data['order']->CustomFields->c->order_number_om:'(Sin Valor)' ?></div>
    </div>
    <div class="rn_FieldDisplay rn_Output">
      <span class="rn_DataLabel">Tipo de Solicitud</span>
      <div class="rn_DataValue rn_LeftJustify"><? if($this->data['order']->Disposition->Parent->Name) echo $this->data['order']->Disposition->Parent->Name; ?><?= ($this->data['order']->Disposition->Name)?' - ' .$this->data['order']->Disposition->Name:'(Sin Valor)' ?></div>
    </div>
    <div class="rn_FieldDisplay rn_Output">
      <span class="rn_DataLabel">ID de Orden</span>
      <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->ID)?$this->data['order']->ID:'(Sin Valor)' ?></div>
    </div>
    <div class="rn_FieldDisplay rn_Output">
      <span class="rn_DataLabel">Nombre Técnico</span>
      <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->AssignedTo->Account->DisplayName)?$this->data['order']->AssignedTo->Account->DisplayName:'(Sin Valor)' ?></div>
    </div>
    <div class="rn_FieldDisplay rn_Output">
      <span class="rn_DataLabel">RUT Técnico</span>
      <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->AssignedTo->Account->CustomFields->c->rut_tecnico)?$this->data['order']->AssignedTo->Account->CustomFields->c->rut_tecnico:'(Sin Valor)' ?></div>
    </div>
 

    <?
    // Solo le deben aparecer a los tecnios de la cola de AR
    // 
    $despacho=array();
    $despacho['values'][0]='Sin Valor';
    $despacho['values'][1]=$this->data['datosHH']->respuesta->Tecnico->Nombre; // echo json_encode($this->data['datosHH']->respuesta->Tecnico->Nombre);
    $despacho['values'][2]='Courier';
    $despacho['values'][3]='Dirección de Cliente';
    $despacho['values'][4]='Otro';

    $despacho['id'][0]=0;
    $despacho['id'][1]=1;
    $despacho['id'][2]=2;
    $despacho['id'][3]=3;
    $despacho['id'][4]=4;
    ?>
    
    <? if($this->data['order']->StatusWithType->Status->ID === 1): ?>
    <div class="rn_FieldDisplay rn_Output">
      <span class="rn_DataLabel">Despachar a</span>
      <div class="rn_DataValue rn_LeftJustify">
        <?
          select_option($despacho,$this->data["order"]->CustomFields->c->external_reference,'despachar',false);
        ?>
      </div>
    </div>
    <? endif; ?>

  <? if($this->data['order']->StatusWithType->Status->ID === 1): ?>
    <div>
    <div class="rn_FieldDisplay rn_Output fullWidth">
      <span class="rn_DataLabel">Nota Entrega</span>
      <? if($this->data['order']->StatusWithType->Status->ID === 1): ?>
      <textarea class="rn_DataValue rn_LeftJustify" name="shipping_instructions"><?= $this->data['order']->CustomFields->c->shipping_instructions ?></textarea>
      <? else: ?>
      <div class="rn_DataValue rn_LeftJustify"><?= $this->data['order']->CustomFields->c->shipping_instructions ?></div>
      <? endif; ?>
    </div>
</div>

  <? else: ?>
    <div>
    <div class="rn_FieldDisplay rn_Output fullWidth">
      <span class="rn_DataLabel">Nota Entrega</span>
      <? if($this->data['order']->StatusWithType->Status->ID === 1): ?>
      <textarea class="rn_DataValue rn_LeftJustify" name="shipping_instructions"><?= $this->data['order']->CustomFields->c->shipping_instructions ?></textarea>
      <? else: ?>
      <div class="rn_DataValue rn_LeftJustify"><?= $this->data['order']->CustomFields->c->shipping_instructions ?></div>
      <? endif; ?>
    </div>
</div>

  <? endif; ?>

<? endif; ?>
