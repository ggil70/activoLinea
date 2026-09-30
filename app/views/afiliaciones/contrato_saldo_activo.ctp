<?php
/**
 * Generador de planillas de afiliación de usuarios jurídicos
**/

App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

Class PlanillaPDF extends FPDF{

	function header(){		
				
		$this->ln();
		$this->ln();
		$this->ln();
		$this->ln();
		$this->ln();
	}
	
	function Footer(){
		
	}
	
	//Iniciación de variables
	var $B = 0;
	var $I = 0;
	var $U = 0;
	var $HREF = '';
        var $ALIGN='';

	function WriteHTML($html) {
		//Intérprete de HTML
		$html = str_replace("\n", ' ', $html);
		$a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);                                
        
		foreach ($a as $i => $e) {
			if ($i % 2 == 0) {
				//Text
				if($this->HREF)
					$this->PutLink($this->HREF,$e);
                                elseif($this->ALIGN == 'justify')
                                        $this->Multicell(0,5,$e,0,'J');
                                elseif($this->ALIGN == 'center')
                                        $this->Cell(0, 5, $e, 0, 1, 'C');
				else
					$this->Write(5,$e);          
			}
			else {
				//Etiqueta
				if ($e{0} == '/')
					$this->CloseTag(strtoupper(substr($e, 1)));
				else {
					//Extraer atributos
					$a2 = explode(' ', $e);
					$tag = strtoupper(array_shift($a2));
					$attr = array();
					foreach ($a2 as $v)
						if (ereg('^([^=]*)=["\']?([^"\']*)["\']?$', $v, $a3))
							$attr[strtoupper($a3[1])] = $a3[2];
					$this->OpenTag($tag, $attr);
				}
			}
		}
	}

	function OpenTag($tag, $attr) {
		//Etiqueta de apertura
                if ($tag == 'P')
                        $this->ALIGN = $attr['ALIGN'];
		if ($tag == 'B' or $tag == 'I' or $tag == 'U')
			$this->SetStyle($tag, true);
		if ($tag == 'A')
			$this->HREF = $attr['HREF'];
		if ($tag == 'BR')
			$this->Ln(2.5);                                
	}

	function CloseTag($tag) {
		//Etiqueta de cierre
                if ($tag == 'P')
                        $this->ALIGN='';
		if ($tag == 'B' or $tag == 'I' or $tag == 'U')
			$this->SetStyle($tag, false);
		if ($tag == 'A')
			$this->HREF = '';                
	}

	function SetStyle($tag, $enable) {
		//Modificar estilo y escoger la fuente correspondiente
		$this->$tag+=($enable ? 1 : -1);
		$style = '';
		foreach (array('B', 'I', 'U') as $s)
			if ($this->$s > 0)
				$style.=$s;
		$this->SetFont('', $style);
	}
	
	function PutLink($URL,$txt) {
		//Escribir un hiper-enlace
		$this->SetTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->Write(5,$txt,$URL);
		$this->SetStyle('U',false);
		$this->SetTextColor(0);
	}

}

$pdf = new PlanillaPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);

switch($datos['ESTADOCIVIL']){
    case 1: $edoCivil = 'soltero(a)';break;
    case 2: $edoCivil = 'casado(a)';break;
    case 3: $edoCivil = 'divorciado(a)';break;
    case 4: $edoCivil = 'viudo(a)';break;
    case 5: $edoCivil = 'concubinato(a)';break;
}

// TEXTO DEL CONTRATO
if (strlen(trim($cuentas['Afiliacion']['numero_tarjeta'])) == 16) {
$tar_cre = trim($cuentas['Afiliacion']['numero_tarjeta']);
$contrato = '<P ALIGN="justify">Yo '.desencriptar($usuario['nombre']).', de nacionalidad '.$datos['NACIONALIDAD'];
$contrato.= ', mayor de edad, de estado civil '.$edoCivil.', ';
$contrato.= 'titular de la cédula de identidad N° '.$usuario['tipo_cedula'].'-'.desencriptar($usuario['cedula']).', ';
$contrato.= 'por medio de la presente autorizo expresa, amplia y suficientemente a BANCO ACTIVO, C.A. BANCO UNIVERSAL, ';
$contrato.= 'a los fines de que en todas las oportunidades en que la Cuenta '.$tipoCuentaCA.' Nro. '.$nro_cta.' de ';
$contrato.= 'la cual soy titular en BANCO ACTIVO, C.A. BANCO UNIVERSAL no disponga de fondos suficientes para cubrir ';
$contrato.= 'los pagos con algunos de los instrumentos de movilización que el banco habilite para tal fin e igualmente con la ';
$contrato.= 'finalidad de generar dicha disponibilidad y dejar elementos de referencia y conciliación en las mismas, de acuerdo con ';
$contrato.= 'las especificaciones del servicio, realice en mi nombre y representación lo siguiente:';
$contrato.= '<br>';
$contrato.= '1.    Cargos a la Tarjeta de Crédito '.''.' distinguida con el Nro. '. $tar_cre .' emitida por BANCO ';
$contrato.= 'ACTIVO, C.A. BANCO UNIVERSAL y de la cual soy titular, por el monto faltante para cubrir el pago ';
$contrato.= 'y hasta por el saldo disponible en dicha tarjeta de crédito e igualmente acepto, los cargos por ';
$contrato.= 'concepto de las respectivas comisiones que proceden por esta operación, los cuales me obligo a ';
$contrato.= 'pagar, de conformidad al Aviso Oficial publicado por el Banco Central de Venezuela contentivo de ';
$contrato.= 'los límites máximos de las tarifas, comisiones y/o recargos que podrán cobrar las instituciones ';
$contrato.= 'bancarias, casas de cambio y los proveedores no bancarios de terminales de puntos de venta a sus clientes.';
$contrato.= '<br>';
$contrato.= '2.	   Cargos a la Cuenta en Moneda Extranjera distinguida con el Nro.';
$contrato.= ' '.''.', que mantengo en BANCO ACTIVO, C.A. BANCO UNIVERSAL, por el monto faltante, para cubrir el pago y hasta por ';
$contrato.= 'el saldo disponible en dicha cuenta. De igual forma, acepto que a los fines de disponer de los ';
$contrato.= 'fondos que mantengo en moneda extranjera en la referida cuenta, se deberá liquidar el equivalente ';
$contrato.= 'en moneda nacional a través del “Menudeo” y/o “Mesa de Cambio”, operaciones cambiarias éstas ';
$contrato.= 'que procederán de acuerdo a la cuantía de la transacción; y cuyas comisiones respectivas me ';
$contrato.= 'obligo a pagar, de conformidad al Aviso Oficial publicado por el Banco Central de Venezuela ';
$contrato.= 'contentivo de los límites máximos de las tarifas, comisiones y/o recargos que podrán cobrar las ';
$contrato.= 'instituciones bancarias, casas de cambio y los proveedores no bancarios de terminales de puntos ';
$contrato.= 'de venta a sus clientes.';
$contrato.= '<br>';
$contrato.= '3.    Un abono a la cuenta identificada en esta solicitud, por el monto equivalente al cargado a la Tarjeta ';
$contrato.= 'de Crédito y/o a la Cuenta en Moneda Extranjera, ambas antes identificadas, de acuerdo a lo ';
$contrato.= 'indicado en los numerales anteriores.';
$contrato.= '<br>';
$contrato.= '4.    Por último, reconozco que he seleccionado en el portal Activo En Línea, al cual previamente me ';
$contrato.= 'afilié, el instrumento donde preferentemente se deberán realizar los cargos en ejecución del ';
$contrato.= 'servicio al cual me afilio por la presente.';
$contrato.= '<br>';
$contrato.= 'En este mismo sentido acepto, que BANCO ACTIVO, C.A. BANCO UNIVERSAL responderá por cualquier ';
$contrato.= 'reclamo, querella, acción, pretensión, demanda, pérdida o daño resultante directa o indirectamente con la ';
$contrato.= 'ejecución del servicio de “SALDO ACTIVO PLUS” siempre que sea atribuible a éste, por lo que queda ';
$contrato.= 'expresamente entendido, que BANCO ACTIVO, C.A. BANCO UNIVERSAL no será responsable por el ';
$contrato.= 'incumplimiento en la prestación del servicio aquí descrito, ni estará obligado al pago de indemnización ';
$contrato.= 'alguna por concepto de los daños y perjuicios que eventualmente se deriven de la no ejecución de los ';
$contrato.= 'procesos cuando: a) la Tarjeta de Crédito y/o la Cuenta en Moneda Extranjera de la cual soy titular, no ';
$contrato.= 'disponga de saldo suficiente para efectuar el cargo, no esté activa, se encuentre bloqueada o condicionada.';
$contrato.= 'b) cuando exista retardo en el procesamiento, siempre que ello se deba a casos fortuitos, fuerza mayor y/o, ';
$contrato.= 'en general, a cualquier causa extraña no imputable a BANCO ACTIVO, C.A. BANCO UNIVERSAL, ';
$contrato.= 'mientras tales circunstancias persistan.';
$contrato.= '<br>';
$contrato.= 'Queda entendido, que regirán igualmente los términos y condiciones de uso contenidos en las ';
$contrato.= 'estipulaciones contractuales de los instrumentos financieros asociados a “SALDO ACTIVO PLUS”, que ';
$contrato.= 'mantiene vigente BANCO ACTIVO, C.A. BANCO UNIVERSAL, así como las modificaciones que estos ';
$contrato.= 'pudieran tener en el futuro. ';
$contrato.= '<br>';
$contrato.= 'Finalmente declaro, de conformidad con lo dispuesto en las Normas Relativas a la Protección de los ';
$contrato.= 'Usuarios y Usuarias de los Servicios Financieros vigentes, emanadas de la Superintendencia de las ';
$contrato.= 'Instituciones del Sector Bancario, que con suficiente antelación a mi afiliación al servicio “SALDO ACTIVO ';
$contrato.= 'PLUS”; BANCO ACTIVO, C.A. BANCO UNIVERSAL puso a mi disposición el contenido contractual del ';
$contrato.= 'mismo tenor del presente, el cual examiné y comprendí en todo su alcance, extensión, trascendencia y ';
$contrato.= 'consecuencias jurídicas de todas las disposiciones que lo conforman, tomando en consideración que: (i) la ';
$contrato.= 'afiliación al servicio “SALDO ACTIVO PLUS” dispuesta en Activo En Línea; y (ii) la ejecución de cualquier ';
$contrato.= 'transacción a las que se refiera el alcance del presente servicio, suponen la aceptación de los términos y ';
$contrato.= 'condiciones sin reparo u obligación alguna, por constituir las mismas, reflejo fiel y exacto de mi voluntad.';
$contrato.= 'De igual forma, declaro bajo fe de juramento que los datos aquí suministrados son ciertos, y autorizo a ';
$contrato.= 'BANCO ACTIVO, C.A. BANCO UNIVERSAL para que corrobore la fidelidad de la información ';
$contrato.= 'suministrada. Asimismo, declaro también bajo fe de juramento, que el dinero destinado al pago de las ';
$contrato.= 'obligaciones derivadas de la presente solicitud, tiene una procedencia y un destino lícito, provenientes bien ';
$contrato.= 'de la actividad económica, profesional; o de bienes, haberes o beneficios derivados de las actividades de ';
$contrato.= 'legítimo comercio e industrias ejecutadas, de conformidad a las Normas emanadas de la Superintendencia ';
$contrato.= 'de las Instituciones del Sector Bancario, relativas a la administración y fiscalización de los riesgos ';
$contrato.= 'relacionados con la Legitimación de Capitales, Financiamiento al Terrorismo y Financiamiento de la ';
$contrato.= 'Proliferación de Armas de Destrucción Masiva vigentes para el momento de la suscripción del presente; y ';
$contrato.= 'que dichas actividades no contravienen, ni incumplen ninguna de las disposiciones contenidas en las leyes ';
$contrato.= 'que regulen las materias relacionadas a las referidas Normas, o en cualquier otra Ley aplicable.';
$contrato.= '</P>';
}else{
$cta_usd = trim($cuentas['Afiliacion']['numero_tarjeta']);
$contrato = '<P ALIGN="justify">Yo '.desencriptar($usuario['nombre']).', de nacionalidad '.$datos['NACIONALIDAD'];
$contrato.= ', mayor de edad, de estado civil '.$edoCivil.', ';
$contrato.= 'titular de la cédula de identidad N° '.$usuario['tipo_cedula'].'-'.desencriptar($usuario['cedula']).', ';
$contrato.= 'por medio de la presente autorizo expresa, amplia y suficientemente a BANCO ACTIVO, C.A. BANCO UNIVERSAL, ';
$contrato.= 'a los fines de que en todas las oportunidades en que la Cuenta '.$tipoCuentaCA.' Nro. '.$nro_cta.' de ';
$contrato.= 'la cual soy titular en BANCO ACTIVO, C.A. BANCO UNIVERSAL no disponga de fondos suficientes para cubrir ';
$contrato.= 'los pagos con algunos de los instrumentos de movilización que el banco habilite para tal fin e igualmente con la ';
$contrato.= 'finalidad de generar dicha disponibilidad y dejar elementos de referencia y conciliación en las mismas, de acuerdo con ';
$contrato.= 'las especificaciones del servicio, realice en mi nombre y representación lo siguiente:';
$contrato.= '<br>';
$contrato.= '1.    Cargos a la Tarjeta de Crédito '.''.' distinguida con el Nro. '.$tar_cre.' emitida por BANCO ';
$contrato.= 'ACTIVO, C.A. BANCO UNIVERSAL y de la cual soy titular, por el monto faltante para cubrir el pago ';
$contrato.= 'y hasta por el saldo disponible en dicha tarjeta de crédito e igualmente acepto, los cargos por ';
$contrato.= 'concepto de las respectivas comisiones que proceden por esta operación, los cuales me obligo a ';
$contrato.= 'pagar, de conformidad al Aviso Oficial publicado por el Banco Central de Venezuela contentivo de ';
$contrato.= 'los límites máximos de las tarifas, comisiones y/o recargos que podrán cobrar las instituciones ';
$contrato.= 'bancarias, casas de cambio y los proveedores no bancarios de terminales de puntos de venta a sus clientes.';
$contrato.= '<br>';
$contrato.= '2.	   Cargos a la Cuenta en Moneda Extranjera distinguida con el Nro. ';
$contrato.= $cta_usd.', que mantengo en BANCO ACTIVO, C.A. BANCO UNIVERSAL, por el monto faltante, para cubrir el pago y hasta por ';
$contrato.= 'el saldo disponible en dicha cuenta. De igual forma, acepto que a los fines de disponer de los ';
$contrato.= 'fondos que mantengo en moneda extranjera en la referida cuenta, se deberá liquidar el equivalente ';
$contrato.= 'en moneda nacional a través del “Menudeo” y/o “Mesa de Cambio”, operaciones cambiarias éstas ';
$contrato.= 'que procederán de acuerdo a la cuantía de la transacción; y cuyas comisiones respectivas me ';
$contrato.= 'obligo a pagar, de conformidad al Aviso Oficial publicado por el Banco Central de Venezuela ';
$contrato.= 'contentivo de los límites máximos de las tarifas, comisiones y/o recargos que podrán cobrar las ';
$contrato.= 'instituciones bancarias, casas de cambio y los proveedores no bancarios de terminales de puntos ';
$contrato.= 'de venta a sus clientes.';
$contrato.= '<br>';
$contrato.= '3.    Un abono a la cuenta identificada en esta solicitud, por el monto equivalente al cargado a la Tarjeta ';
$contrato.= 'de Crédito y/o a la Cuenta en Moneda Extranjera, ambas antes identificadas, de acuerdo a lo ';
$contrato.= 'indicado en los numerales anteriores.';
$contrato.= '<br>';
$contrato.= '4.    Por último, reconozco que he seleccionado en el portal Activo En Línea, al cual previamente me ';
$contrato.= 'afilié, el instrumento donde preferentemente se deberán realizar los cargos en ejecución del ';
$contrato.= 'servicio al cual me afilio por la presente.';
$contrato.= '<br>';
$contrato.= 'En este mismo sentido acepto, que BANCO ACTIVO, C.A. BANCO UNIVERSAL responderá por cualquier ';
$contrato.= 'reclamo, querella, acción, pretensión, demanda, pérdida o daño resultante directa o indirectamente con la ';
$contrato.= 'ejecución del servicio de “SALDO ACTIVO PLUS” siempre que sea atribuible a éste, por lo que queda ';
$contrato.= 'expresamente entendido, que BANCO ACTIVO, C.A. BANCO UNIVERSAL no será responsable por el ';
$contrato.= 'incumplimiento en la prestación del servicio aquí descrito, ni estará obligado al pago de indemnización ';
$contrato.= 'alguna por concepto de los daños y perjuicios que eventualmente se deriven de la no ejecución de los ';
$contrato.= 'procesos cuando: a) la Tarjeta de Crédito y/o la Cuenta en Moneda Extranjera de la cual soy titular, no ';
$contrato.= 'disponga de saldo suficiente para efectuar el cargo, no esté activa, se encuentre bloqueada o condicionada.';
$contrato.= 'b) cuando exista retardo en el procesamiento, siempre que ello se deba a casos fortuitos, fuerza mayor y/o, ';
$contrato.= 'en general, a cualquier causa extraña no imputable a BANCO ACTIVO, C.A. BANCO UNIVERSAL, ';
$contrato.= 'mientras tales circunstancias persistan.';
$contrato.= '<br>';
$contrato.= 'Queda entendido, que regirán igualmente los términos y condiciones de uso contenidos en las ';
$contrato.= 'estipulaciones contractuales de los instrumentos financieros asociados a “SALDO ACTIVO PLUS”, que ';
$contrato.= 'mantiene vigente BANCO ACTIVO, C.A. BANCO UNIVERSAL, así como las modificaciones que estos ';
$contrato.= 'pudieran tener en el futuro. ';
$contrato.= '<br>';
$contrato.= 'Finalmente declaro, de conformidad con lo dispuesto en las Normas Relativas a la Protección de los ';
$contrato.= 'Usuarios y Usuarias de los Servicios Financieros vigentes, emanadas de la Superintendencia de las ';
$contrato.= 'Instituciones del Sector Bancario, que con suficiente antelación a mi afiliación al servicio “SALDO ACTIVO ';
$contrato.= 'PLUS”; BANCO ACTIVO, C.A. BANCO UNIVERSAL puso a mi disposición el contenido contractual del ';
$contrato.= 'mismo tenor del presente, el cual examiné y comprendí en todo su alcance, extensión, trascendencia y ';
$contrato.= 'consecuencias jurídicas de todas las disposiciones que lo conforman, tomando en consideración que: (i) la ';
$contrato.= 'afiliación al servicio “SALDO ACTIVO PLUS” dispuesta en Activo En Línea; y (ii) la ejecución de cualquier ';
$contrato.= 'transacción a las que se refiera el alcance del presente servicio, suponen la aceptación de los términos y ';
$contrato.= 'condiciones sin reparo u obligación alguna, por constituir las mismas, reflejo fiel y exacto de mi voluntad.';
$contrato.= 'De igual forma, declaro bajo fe de juramento que los datos aquí suministrados son ciertos, y autorizo a ';
$contrato.= 'BANCO ACTIVO, C.A. BANCO UNIVERSAL para que corrobore la fidelidad de la información ';
$contrato.= 'suministrada. Asimismo, declaro también bajo fe de juramento, que el dinero destinado al pago de las ';
$contrato.= 'obligaciones derivadas de la presente solicitud, tiene una procedencia y un destino lícito, provenientes bien ';
$contrato.= 'de la actividad económica, profesional; o de bienes, haberes o beneficios derivados de las actividades de ';
$contrato.= 'legítimo comercio e industrias ejecutadas, de conformidad a las Normas emanadas de la Superintendencia ';
$contrato.= 'de las Instituciones del Sector Bancario, relativas a la administración y fiscalización de los riesgos ';
$contrato.= 'relacionados con la Legitimación de Capitales, Financiamiento al Terrorismo y Financiamiento de la ';
$contrato.= 'Proliferación de Armas de Destrucción Masiva vigentes para el momento de la suscripción del presente; y ';
$contrato.= 'que dichas actividades no contravienen, ni incumplen ninguna de las disposiciones contenidas en las leyes ';
$contrato.= 'que regulen las materias relacionadas a las referidas Normas, o en cualquier otra Ley aplicable.';
$contrato.= '</P>';
}

//**************************************************************** TERCERA HOJA
$pdf->AddPage();
$pdf->setMargins(20,20,20);
$pdf->setAutoPageBreak(true,20);

$pdf->setFont('Arial','B',11);
$pdf->cell(0,6,"SOLICITUD DE AFILIACION AL SERVICIO SALDO ACTIVO PLUS",0,1,'C');
$pdf->cell(0,6,"PERSONA NATURAL",0,0,'C');

// $pdf->ln();
// $pdf->ln();

// $pdf->setFont('Arial','',9);
// $pdf->cell(0,6,$fecha,0,0,'R');

// $pdf->ln();
// $pdf->ln();

// $pdf->cell(0,6,'Señores:',0,1,'L');
// $pdf->cell(0,6,'BANCO ACTIVO, C.A. BANCO UNIVERSAL',0,1,'L');
// $pdf->cell(0,6,'Presente.-',0,1,'L');

$pdf->ln();
$pdf->ln();

$pdf->setFont('Arial','',9);
$pdf->WriteHTML($contrato);
$pdf->ln();

$pdf->output(date('YmdHis').'.pdf','D');

exit;


?>