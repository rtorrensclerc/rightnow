<?php
namespace Custom\Libraries;
require(APPPATH.'libraries/fpdf.php');
//PROD
class Fpdf2 extends Fpdf
{
  // Cabecera de página
  function Header()
  {
      // Logo
      //$dirImage = HTMLROOT . ASSETS_ROOT.'images_pdf/header.png';
      //$this->Image($dirImage,10,8,33);
      // Arial bold 15
      // $this->SetFont('Arial','B',15);
      // // Movernos a la derecha
      // $this->Cell(80);
      // // Título
      // $this->Cell(30,10,'Title',1,0,'C');
      // // Salto de línea
      // $this->Ln(20);
  }

  // Pie de página
  function Footer()
  {
      // Posición: a 1,5 cm del final
      // $this->SetY(-15);
      // // Arial italic 8
      // $this->SetFont('Arial','I',8);
      // // Número de página
      // $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
  }

  // Una tabla más completa
  function ImprovedTable($header, $data)
  {
      // Anchuras de las columnas
      $w = array(40, 35, 45, 40);
      // Cabeceras
      for($i=0;$i<count($header);$i++)
          $this->Cell($w[$i],7,$header[$i],1,0,'C');
      $this->Ln();
      // Datos
      foreach($data as $row)
      {
          $this->Cell($w[0],6,$row[0],'LR');
          $this->Cell($w[1],6,$row[1],'LR');
          $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
          $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
          $this->Ln();
      }
      // Línea de cierre
      $this->Cell(array_sum($w),0,'','T');
  }

  /**
	* Agrega información de la empresa centrada en en encabezado de la página
	*
	* @param string[] $data
	* @param integer $xPos
	* @param integer $yPos
	*/
	function addCompanyInformation($data, $xPos = 10, $yPos = 15){
		$bounds = [1];

		//$this->SetFont('Arial', 'B', 15);
		$this->SetY($yPos);

		//$this->SetX($xPos);
		//$this->SetLineWidth(1);
		//$this->SetDrawColor(255, 0, 0);
		//$this->Cell(6 ,6, utf8_decode('D'), 1, 0, 'C');
		//$this->Cell(6, 6, utf8_decode('Dimacofi S.A.'), 0, 0, 'L');
		//$this->SetDrawColor(0, 0, 0);
		//$this->SetLineWidth(0);
		$this->SetFont('Arial', '', 8);
		//$this->SetY(17);
		//$this->SetX($xPos);

		foreach($data as $key => $value){
			$this->SetX($xPos);
			$this->Cell($bounds[0], 4, utf8_decode($value), 0, 1, 'L');
			$this->SetFont('Arial', '', 8);
		}

	}

	/**
	* Agrega el título centrado en en encabezado de la página
	*
	* @param string[] $data
	* @param integer $xPos
	* @param integer $yPos
	*/
	function addTitleInformation($data, $xPos = 10, $yPos = 45){
		$bounds = [0];

		$this->SetFont('Arial', 'B', 9);
		$this->SetY($yPos);

		foreach($data as $key => $value){
			$this->SetX($xPos);
			$this->Cell($bounds[0], 4, utf8_decode($value), 0, 1, 'C');
		}
	}

	/**
	* Agrega una tabla con la información del cliente y del equipo
	*
	* @param array[] $customerData
	* @param array[] $hhData
	* @param integer $xPos
	* @param integer $yPos
	*/

	function addTexto($Texto, $xPos = 10, $yPos = 55){
		$bounds = [35, 60, 35, 60];
		$this->SetFont('Arial', 'B', 11);
		$this->Cell($bounds[0] + $bounds[1] + $bounds[2] + $bounds[3], 8, utf8_decode($Texto), 0, 1, 'L');
		$this->SetFont('Arial', '', 8);
	}

	function addTableCustomerInformation($customerData, $hhData, $xPos = 10, $yPos = 55){
		$bounds = [35, 60, 35, 60];
		$count_arr = count($customerData);
		$newLine = 0;
    $full = false;
		$last = false;
		$count = 0;

		$this->SetY($yPos);

		$this->SetFont('Arial', 'B', 11);
		$this->Cell($bounds[0] + $bounds[1] + $bounds[2] + $bounds[3], 8, utf8_decode('Datos del Cliente'), 0, 1, 'L');

    foreach($customerData as $key => $value){
			if($full){
				$count = 0;
			}

			if($count_arr-1 == $key){
				$last = true;
			}

			if(strpos(strtolower($value['label']), 'dirección') !== false
			|| strpos(strtolower($value['label']), 'razón social') !== false){
				$full = true;
			} else {
				$full = false;
			}

			if($count % 2 !== 0 || $full || $last){
				$newLine = 1;
			} else {
				$newLine = 0;
			}

			$this->SetFont('Arial', 'B', 9);
			$this->Cell($bounds[0], 4, utf8_decode($value['label']), 1, 0, 'L');
			$this->SetFont('Arial', '', 8);

			if(strpos(strtolower($value['label']), 'dirección') !== false
			|| strpos(strtolower($value['label']), 'razón social') !== false){
				$this->MultiCell(0, 4, $value['value'], 1, 'L', false);
			} else {
				$this->Cell(($full || $last)?0:$bounds[1], 4, utf8_decode($value['value']), 1, $newLine, 'L');
			}

			$count++;
		}
		if(count($hhData)>0)
		{
			$this->SetFont('Arial', 'B', 11);
			$this->Cell(array_sum($bounds), 8, utf8_decode('Datos del Equipo'), 0, 1, 'L');
			$count_arr = count($hhData);
			$full = false;
			$last = false;

			foreach($hhData as $key => $value){
				if($count-1 == $key){
					$last = true;
				}

				if($key % 2 !== 0 || $full || $last){
					$newLine = 1;
				} else {
					$newLine = 0;
				}
				$this->SetFont('Arial', 'B', 9);
				$this->Cell($bounds[0], 4, utf8_decode($value['label']), 1, 0, 'L');
				$this->SetFont('Arial', '', 8);
				$this->Cell(($full || $last)?0:$bounds[1], 4, utf8_decode($value['value']), 1, $newLine, 'L');
			}
		}
	}

	/**
	* Agrega una tabla con la lista de items y totales, con un máximo de 24 por hoja.
	*
	* @param array[] $objectLines
	* @param array[] $objectSummary
	* @param integer $xPos
	* @param integer $yPos
	*/
	function addTableLines($objectLines, $objectSummary, $xPos = 10, $yPos = 105){
		$bounds = [20, 95, 25, 25, 25];
		$countObjectLines = count($objectLines);
		$countObjectSummary = count($objectSummary);

		$this->SetY($yPos);

		$this->SetFont('Arial', 'B', 11);
		$this->Cell(array_sum($bounds), 8, utf8_decode('Presupuesto'), 0, 1, 'L');

		$this->SetFont('Arial', 'B', 9);

		$this->SetX($xPos);
		$this->Cell($bounds[0], 4, utf8_decode('Cantidad'), 1, 0, 'C');
		$this->Cell($bounds[1]+$bounds[2], 4, utf8_decode('Descripción'), 1, 0, 'L');
		$this->Cell($bounds[2], 4, utf8_decode('Precio Unit $'), 1, 0, 'R');
    //RTC 2017/03/13
    $this->Cell($bounds[4], 4, utf8_decode('Valor c/Dcto'), 1, 1, 'R');
    //$this->Cell($bounds[4], 4, utf8_decode('Dcto.'), 1, 1, 'R');
		$this->SetFont('Arial', utf8_decode(''), 8);

		foreach($objectLines as $key => $value){
			$this->Cell($bounds[0], 4, utf8_decode($value['quantity']), 1, 0, 'C');
			$this->Cell($bounds[1]+$bounds[2], 4, utf8_decode(substr($value['description'],0,60)), 1, 0, 'L');
			$this->Cell($bounds[2], 4, utf8_decode($value['unitValue']), 1, 0, 'R');
      $this->Cell($bounds[4], 4, utf8_decode($value['netValue']), 1, 1, 'R');
      //$this->Cell($bounds[4], 4, utf8_decode($value['discount']), 1, 1, 'R');
		}

		foreach($objectSummary as $key => $value){
			$this->Cell($bounds[0]+$bounds[1]+$bounds[2], 4, utf8_decode(''), 0, 0, 'L');

			$this->SetFont('Arial', 'B', 8);
			$this->Cell($bounds[3], 4, utf8_decode($value['label']), 1, 0, 'R');
			$this->SetFont('Arial', '', 8);
			$this->Cell($bounds[4], 4, utf8_decode($value['value']), 1, 1, 'R');
		}
	}



	/**
	* Agrega una tabla con la lista de items y totales, con un máximo de 24 por hoja.
	*
	* @param array[] $objectLines
	* @param array[] $objectSummary
	* @param integer $xPos
	* @param integer $yPos
	*/
	function addTableLines_insumos($objectLines, $objectSummary, $xPos = 10, $yPos = 105){
		$bounds = [18, 80, 24, 24, 24];
		$countObjectLines = count($objectLines);
		$countObjectSummary = count($objectSummary);

		$this->SetY($yPos);

		$this->SetFont('Arial', 'B', 11);
		$this->Cell(array_sum($bounds), 8, utf8_decode('Presupuesto Insumos'), 0, 1, 'L');

		$this->SetFont('Arial', 'B', 9);

		$this->SetX($xPos);
		$this->Cell($bounds[0], 4, utf8_decode('Cantidad'), 1, 0, 'C');
		$this->Cell($bounds[0], 4, utf8_decode('STOCK'), 1, 0, 'C');
		$this->Cell($bounds[0], 4, utf8_decode('CODIGO'), 1, 0, 'C');
		$this->Cell(52, 4, utf8_decode('Descripción'), 1, 0, 'L');
		
		
		$this->Cell($bounds[0], 4, utf8_decode('USD'), 1, 0, 'C');
		$this->Cell($bounds[2], 4, utf8_decode('PESOS'), 1, 0, 'R');
		$this->Cell($bounds[0], 4, utf8_decode('Descuento'), 1, 0, 'C');
		//RTC 2017/03/13
		$this->Cell($bounds[4], 4, utf8_decode('TOTAL'), 1, 1, 'R');
    //$this->Cell($bounds[4], 4, utf8_decode('Dcto.'), 1, 1, 'R');
		$this->SetFont('Arial', utf8_decode(''), 8);

		foreach($objectLines as $key => $value){
			$this->Cell($bounds[0], 4, utf8_decode($value['quantity']), 1, 0, 'C');
			if($value['stock']>0)
			{
				$stock='SI';
			}
			else
			{
				$stock='NO';
			}
			$this->Cell($bounds[0], 4, $stock, 1, 0, 'C');
			$this->Cell($bounds[0], 4, utf8_decode($value['codigo']), 1, 0, 'C');
			$this->Cell(52, 4, utf8_decode(substr($value['description'],0,30)), 1, 0, 'L');
			$this->Cell($bounds[0], 4, utf8_decode($value['dolar_value']), 1, 0, 'R');
			$this->Cell($bounds[2], 4, utf8_decode($value['unitValue']), 1, 0, 'R');
			$this->Cell($bounds[0], 4, utf8_decode($value['discount'] .' %'), 1, 0, 'C');
      $this->Cell($bounds[4], 4, utf8_decode($value['netValue']), 1, 1, 'R');
      //$this->Cell($bounds[4], 4, utf8_decode($value['discount']), 1, 1, 'R');
		}

		foreach($objectSummary as $key => $value){
			$this->Cell($bounds[0]+$bounds[0]+$bounds[0]+$bounds[0]+52+$bounds[0], 4, utf8_decode(''), 0, 0, 'L');

			$this->SetFont('Arial', 'B', 8);
			$this->Cell($bounds[3], 4, utf8_decode($value['label']), 1, 0, 'R');
			$this->SetFont('Arial', '', 8);
			$this->Cell($bounds[4], 4, utf8_decode($value['value']), 1, 1, 'R');
		}
	}
	/**
	* Agrega campo de comentarios
	*
	* @param string $comment
	* @param integer $xPos
	* @param integer $yPos
	*/
	function addComment($comment, $xPos = 10, $yPos = 200){
		$this->SetY($yPos);

		$this->SetFont('Arial', 'B', 8);
		$this->Cell(0, 4, utf8_decode('Observación'), 1, 1, 'L');
		$this->SetFont('Arial', '', 8);
		$this->MultiCell(0, 4, utf8_decode($comment), 1, 'L', false);
	}

	/**
	* Agrega lista de condiciones al pié de la página
	*
	* @param string[] $data
	* @param integer $xPos
	* @param integer $yPos
	*/
	function addConditions($data, $xPos = 10, $yPos = 215){
		$bounds = [0];

		$this->SetFont('Arial', '', 8);
		$this->SetY($yPos);

		foreach($data as $key => $value){
			$this->SetX($xPos);
			$this->Cell($bounds[0], 4, utf8_decode($value), 0, 1, 'L');
		}
	}
	function addConditions_V2($data, $xPos = 10, $yPos = 215,$a_inx,$font,$bold,$size){

		$bounds = [5, 0];

		$this->SetFont($font, $bold, $size);
		$this->SetY($yPos);
		$i=0;
		foreach($data as $key => $value){
			$this->SetX($xPos);
			$this->Cell($bounds[0], 4, utf8_decode($a_inx[$i]), 0, 0, 'L');
			$this->Cell($bounds[1], 4, utf8_decode($value), 0, 1, 'L');
			$i=$i+1;
		}
		$this->SetFont('Arial', '', 8);
	}
	/**
	* Agrega zona para completar y firmar al pié de la página
	*
	* @param integer $xPos
	* @param integer $yPos
	*/
	function addSing($xPos = 10, $yPos = 240){
		$bounds = [0, 0];

		$this->SetFont('Arial', '', 8);
		$this->SetY($yPos);
		$this->Cell($bounds[0], 4, utf8_decode('Declaro Presupuesto aprobado y acepto sus condiciones'), 0, 1, 'L');
		$this->SetY($yPos + 10);
		$this->Cell($bounds[1], 4, utf8_decode('Fecha ______/______/_________ Firma _________________________________ Timbre _________________________________'), 0, 1, 'L');
	}

	/**
	* Agrega la información de la ejecutiva
	*
	* @param array[] $data
	* @param integer $xPos
	* @param integer $yPos
	*/
	function addEjecutiveInformation($data, $xPos = 10, $yPos = 260){
		$bounds = [40, 0];

		$this->SetFont('Arial', '', 8);
		$this->SetY($yPos);

		foreach($data as $key => $value){
			$this->SetFont('Arial', 'B', 8);
			$this->Cell($bounds[0], 4, utf8_decode($value['label']), 0, 0, 'L');
			$this->SetFont('Arial', '', 8);
			$this->Cell($bounds[1], 4, utf8_decode($value['value']), 0, 1, 'L');
		}
	}


}

?>
