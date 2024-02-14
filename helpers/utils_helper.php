<?php
use RightNow\Connect\v1_3 as RNCPHP;
/**
* Permite recorrer y pintar un objeto para ser comprendido de forma simple
*
* Ejemplo: dev_print($this->data['result']->orderItems[0],NULL,0,2,"\$this->data['result']->orderItems[0]");
* @param {Object}  Objeto a descomponer
* @param {String}  Prefijo del objeto al ser llamado recursivamente
* @param {Integer} Contador de recursividad
* @param {Integer} Máximo de recursividad
* @param {String}  Texto Prefijo (sólo informativo)
*/
function dev_print($obj, $key_base = NULL, $counter = 0, $max_counter = 2, $prefix=''){
  $counter++;
  $data;

  if($key_base){
    $obj = $obj->$key_base;
    $prefix .= '->'.$key_base;
  }

  foreach ($obj as $key => $value) {

    if(!is_object($obj->$key)){
      $data[] = array("{$prefix}->{$key}", $obj->$key);
    } else {
      if($counter <= $max_counter)
        dev_print($obj, $key, $counter, $max_counter, $prefix);
    }
  }

  showTable($data);
}



/**
* Obtiene el RUT de la organizacion tomando la sesión del contacto
*
* @param void
*/
function getCompanyRutBySession()
{
  $CI = get_instance();
  if (\RightNow\Utils\Framework::isLoggedIn())
  {
      $c_id = $CI->session->getProfile()->c_id->value;
      $CI->load->model('custom/Contact');
      $user = $CI->Contact->getContactById($c_id);
      if (is_null($user->Organization))
      {
          return false;
      }
      else
      {
          $organization = $user->Organization;
          $rut = $organization->CustomFields->c->rut;

          return $rut;

      }

  }
  else
  {
      return false;
  }
}

/**
* Imprime un array de 2 niveles
*
* @param {Array} Array a imprimir
*/
function showTable($data){
  echo "<table class=\"dev_table\">";
  echo "<tbody>";
  for ($i=0; $i < count($data); $i++) {
    echo "<tr><td>{$i}</td><td>{$data[$i][0]}</td><td>{$data[$i][1]}</td></tr>";
  }
  echo "</tbody>";
  echo "</table>";
}


/**
 * Listado de módulos
 * 
 *  1. Formulario Servicio al Cliente
 *  2. Formulario de Asistencia Técnica
 *  3. Solicitud de Insumos
 *  4. Solicitud de Insumos Múltiple
 *  5. Pago de Factura
 *  6. Consulta Facturación
 *  7. Gestión de Usuario
 *  8. Últimos Documentos de Facturación
 *  9. RPA
 * 10. Aula Digital
 * 11. Firma Digital
 * 12. Gestión Documental
 * 13. BPO
 *
 * @param int ID del módulo
 */
function isEnabled($moduleId)
{

  
  $is_logged = \RightNow\Utils\Framework::isLoggedIn();

  if ($is_logged === TRUE)
  {
    
    $CI               = get_instance();
    $obj_info_contact = $CI->session->getSessionData('info_contact');
    
    if (is_array($obj_info_contact["json_profile"]))
    { 

      $a_json_profile = $obj_info_contact["json_profile"]['modules']; //Array
     
      foreach ($a_json_profile as $a_profile)
      {
        if ($a_profile['module']['id'] === $moduleId)
        {
          if ($a_profile['access'] === TRUE)
          {
            return TRUE;
          }
          else
          {
            break;
          }
        }
        else
        {
          continue;
        }
      }
      return FALSE;
    } 
    else
    {
      return FALSE;
    }
  }
  else
  {
    return FALSE;
  }
}

/**
 * Tipos de Contactos
 * 1. Cuenta
 * 2. Contacto
 *
 * @param string valores de perfiles separador por coma (ej: 1,3)
 */
function profiling($valores, $showAllways = false)
{
    $arr_perfiles = explode(',', $valores);
    $valores = '';
    $habilitado = false;
    $CI = get_instance();
    $isLogged = $CI->session->getSessionData('Account_isLogged');
    $isLogged = unserialize($_COOKIE['Account_loggedValues']);

    $obj_info_contact = $CI->session->getSessionData('info_contact'); //información de contacto

    if ($showAllways)
    {
      $habilitado = true;
    }
    else
    {
      for ($i = 0, $cant = sizeof($arr_perfiles);!$habilitado && $i < $cant;++$i)
      {
          if (($obj_info_contact['ContactType']['ID'] == $arr_perfiles[$i] && \RightNow\Utils\Framework::isLoggedIn()) || ($arr_perfiles[$i] == 5 && $isLogged) || $isLogged)
          {
              $habilitado = true;
          }
      }
    }

    return $habilitado;
}

/**
 *
 */
function isBrowserCompatible()
{
    $browser = getBrowser();

    if ($browser['shortName'] == 'IE' && $browser['version'] < 9) {
        return false;
    }

    return true;
}

/**
 * @param string version
 * @param string browser
 */
function getBrowser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version = '';

    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    if (preg_match('/rv/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $sname = 'IE';
        $ub = 'rv';
    } elseif (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $sname = 'IE';
        $ub = 'MSIE';
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $sname = 'FF';
        $ub = 'Firefox';
    } elseif (preg_match('/Chrome/i', $u_agent)) {
        $bname = 'Google Chrome';
        $sname = 'C';
        $ub = 'Chrome';
    } elseif (preg_match('/Safari/i', $u_agent)) {
        $bname = 'Apple Safari';
        $sname = 'S';
        $ub = 'Safari';
    } elseif (preg_match('/Opera/i', $u_agent)) {
        $bname = 'Opera';
        $sname = 'O';
        $ub = 'Opera';
    } elseif (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $bname = 'N';
        $ub = 'Netscape';
    }

    $known = array('rv', 'Version', $ub, 'other');
    $pattern = '#(?<browser>'.implode('|', $known).')[/ :]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }

    $i = count($matches['browser']);
    if ($i != 1) {
        if (strripos($u_agent, 'Version') < strripos($u_agent, $ub)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }

    if ($version == null || $version == '') {
        $version = '?';
    }

    return array(
      'userAgent' => $u_agent,
      'name' => $bname,
      'shortName' => $sname,
      'version' => intval($version),
      'platform' => $platform,
      'pattern' => $pattern,
    );
}


/**
  *
  * Función que trabsfirnar un csv en un array
  *
  *
 * @param string $fileCSV: Archivo CSV
 */
function parserTextCSV($fileCSV, $delimiter = null)
{
  $delimiter  = ($delimiter) ? $delimiter : detectDelimiter($fileCSV, false);
  // if($delimiter === ",")
  //   $rows      = array_map('str_getcsv', explode("\n",$fileCSV));
  // else
  $rows = array_map('textToCsv', explode("\n",$fileCSV));
  // $rows = array_map(function($v) use ($delimiter) {return str_getcsv($v, $delimiter);}, file($fileCSV));
  // var_dump($rows);
  //$rows    = array_map(function($v) use ($delimiter) {return str_getcsv($v, $delimiter);}, explode("\n",$fileCSV));
  // $rows   = array_map(function($v){return str_getcsv($v, ';');}, file($fileCSV));
  $header = array_map('trim',array_shift($rows));
  $csv    = array();
  foreach ($rows as $row) {
    if (count($header) !== count($row))
      continue;
    $csv[] = array_combine($header, $row);
  }
  // return $csv;
  $a_csv['csv']    = $csv;
  $a_csv['header'] = $header;
  return $a_csv;
}

function textToCsv($arr)
{
  if(strlen($arr) > 0)
  {
    $delimiter    = ($delimiter) ? $delimiter : detectDelimiter($arr, false);
    $a_data_line  = explode($delimiter, $arr);
    $data         = array_map("trim", $a_data_line);

    return $data;
  }
}

/**
  *
  * Función que trabsfirnar un csv en un array
  *
  *
 * @param string $fileCSV: Archivo CSV
 */
function parserCSV($fileCSV, $delimiter = null)
{
    if ($delimiter === null)
      $delimiter = detectDelimiter($fileCSV);
    else
      $delimiter = ",";

    $delimiter = (string) $delimiter;
    // $rows   = array_map('str_getcsv', file($fileCSV));
    $rows   = array_map(function($v) use ($delimiter) {return str_getcsv($v, $delimiter);}, file($fileCSV));
    // $rows   = array_map(function($v){return str_getcsv($v, ';');}, file($fileCSV));
    $header = array_shift($rows);
    $csv    = array();
    foreach ($rows as $row) {
      if (count($header) !== count($row))
        return false;
      $csv[] = array_combine($header, $row);
    }
    // return $csv;
    $a_csv['csv']    = $csv;
    $a_csv['header'] = $header;
    return $a_csv;
}


/**
*
* Función que detecta el delimitador del csv
*
* @param string $csvFile Path to the CSV file
* @return  string Delimiter
*/
function detectDelimiter($csvFile, $is_file = null)
{
    $delimiters = array(
        ';' => 0,
        ',' => 0,
        "\t" => 0,
        "|" => 0
    );

    if(is_null($is_file) || $is_file === true)
    {
      $handle = fopen($csvFile, "r");
      $firstLine = fgets($handle);
      fclose($handle);
      foreach ($delimiters as $delimiter => &$count) {
          $count = count(str_getcsv($firstLine, $delimiter));
      }

      return array_search(max($delimiters), $delimiters);
    }
    elseif ($is_file === false)
    {
      $a_rows   = explode("\n" , $csvFile);
      $headers  = $a_rows[0];

      foreach ($delimiters as $delimiter => &$count) {
          $count = count(str_getcsv($headers, $delimiter));
      }

      return array_search(max($delimiters), $delimiters);

    }


}


function searchArrayText($a_words, $texto)
{
  if (!is_array($a_words))
  {
    return false;
  }

  foreach ($a_words as $key => $word)
  {
    $findme      = $word;
    $pos         = strpos($texto, $findme);
    if ($pos !== false)
    {
      return true;
    }
  }
  return false;
}


/**
* Transforma listas a menus
*/
function transformListToMenu($a_menuRN)
{
  $a_menu = array();
  foreach ($a_menuRN as $key => $menu) {
    $a_tempMenu['name']    = $menu->LookupName;
    $a_tempMenu['ID']      = $menu->ID;
    $a_menu[] = $a_tempMenu;
  }
  return $a_menu;
}

/**
* Transforma listas a menus
*/
function getIncidentById($i_id)
{
  $incident = RNCPHP\Incident::fetch($i_id);
  return $incident;
}

/**
* Lista de estados de incidente, se busca el valor del array y devueklve la key si encuentar dicho id
*/

/**
 * Lista de estados de incidente, se busca el valor del array y devuelve la key si encuentar dicho id
 *
 * @param int $status_id. ID de estado de RN
 * @return string|bool Retorna la cadena con la traducción del nuevo estado.  Si no lo encuentra devuelve falso.
 */
function getStatusIncidentid($status_id)
{
  $status_array = array(
    'Ingresado'               => array(1,117, 129),
    'Actualizado'             => array(8),
    'Técnico Asignado'        => array(162,118,119),
    'En Ruta'                 => array(163),
    'Re Agendado'             => array(164),
    'Trabajando'              => array(165,143),
    'Repuestos en Tránsito'   => array(167),
    'Trabajando'              => array(168,169,170,171,172,173),
    'Supervisión'             => array(175),
    'Procesando'              => array(176,111,113,151,152,153,155,157,158),
    'En Aprobación'           => array(177),
    'Enviado'                 => array(178),
    'En Evaluación Comercial' => array(188,189,190,191,193),
    'Cerrado'                 => array(2),
    'Cancelado'               => array(147,148,149),
    'Entregado'               => array(112),
    'Despachado'              => array(140),
    'Visita Finalizada'       => array(166),
  );

  foreach ($status_array as $key => $value) 
  {
    if(in_array($status_id, $value))
    {
      return $key;
    }
  }
  return FALSE;
}

/**
   * Añade una variable en un mensaje de las base de mensajes la cual esta definida por 
   * 
   * @param string $expresion
   * @param string $variable
   * @param string $cadena
   * @return string
   */
  function replaceMessageBase($expresion, $messageBase, $cadena)
  {
    $obj_messageBase = RNCPHP\MessageBase::fetch($cadena);
    $obj_messageBase = (string) $obj_messageBase->Value;
    $resultado       = str_replace($expresion, $messageBase, $obj_messageBase);
    return $resultado;
  }  

  function getMessageBase($string)
  {
    $cadena   = RNCPHP\MessageBase::fetch($string);
    $response = $cadena->Value;

    return $response;
  }
