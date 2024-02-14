<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
    <div class="rn_PageHeader">
        <div class="rn_Container">
            <h1>Servicios Especiales</h1>
        </div>
    </div>

    <h1>Búsqueda de Ticket por HH</h1>
    <div class="rn_FieldDisplay rn_Output">
        <rn:widget path="custom/input/InputField" id="ID_HH" name="ID_HH" label_input="Numero de HH" value=""  display_type="number" required="false" wide="false" />
    </div>


    <div class="rn_FieldDisplay rn_Output">
        <input type="button" id="btn_get_ticket" name="btn_get_ticket" value="Buscar">
    </div>
    <?php
            $id= $this->data['attrs']['no_hh'];
            $CI             = get_instance();
            $accountValues  = $CI->session->getSessionData('Account_loggedValues');
            // Parche Uso de cookies
            $accountValues  = unserialize($_COOKIE['Account_loggedValues']);
            $a_Filters2     = array();
            $a_Filters2[]   = array("name" => 'cuentaID', "operator" => '=' , "type" => 'INT' , "value" => "{$accountValues['ID']}");
            
            if( $id!="")
            {
    ?>
    <h3>Resultados para busqueda</h3>
    <?
                $a_Filters2[]   = array("name" => 'HH', "operator" => '=' , "type" => 'INT' , "value" => "{$id}");            
            }
    ?>
    <div id="rn_PageContent rn_Home">
        <div class="rn_Padding column">
            <rn:widget path="custom/reports/IntegerGrid" report_id="102037"
                json_filters="#rn:php:json_encode($a_Filters2)#" per_page="200" show_paginator="false"
                url_per_col="/app/reparacion/special/special_detail/ref_no/" col_id_url="1" />
        </div>
    </div>
   <?
        if($accountValues['ID']==175 || $accountValues['ID']==160  || $accountValues['ID']==163|| $accountValues['ID']==53 || $accountValues['ID']==120 || $accountValues['ID']==17|| $accountValues['ID']==64136 || $accountValues['ID']==203 || $accountValues['ID']==85208 )
        {
   ?>
    <div class="rn_FieldDisplay rn_Output">
    <!--h1>Crear Tickets Especiales</h1>
    
            <input type="file" name="" id="">
            <input type="button" id="btn_upload_file" name="btn_upload_file" value="Crear" -->
    
        <fieldset>
            <div class="form-content">
            
            <div class="upload_box">
                <h3>Cargue archivo CSV Para crear Tickets</h3>
                <input type="file" name="" id="">
                <br>
                <a href="#" id="btn_upload_csv" class="btn">Subir Archivo</a>
            </div>
            </div>
        </fieldset>

      <p id="demo"></p>
    </div>
    <?
        }
    ?>

</div>