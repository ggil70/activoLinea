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
$contrato = '<P ALIGN="justify">Entre, BANCO ACTIVO, C.A. BANCO UNIVERSAL, domiciliado en Caracas, originalmente inscrito ante el Registro Mercantil de la Circunscripción Judicial del Estado Anzoátegui, el 11 de abril de 1978, bajo el Nº 73, Tomo A, bajo la denominación social de Banco Hipotecario Oriental C.A., modificado posteriormente su domicilio legal al actual, tal y como se evidencia de Acta de Asamblea General Extraordinaria de Accionistas, celebrada en fecha 11 de marzo de 2005, inscrita por ante el Registro Mercantil Primero de la Circunscripción Judicial del Estado Anzoátegui, el 16 de marzo de 2005 , bajo el Nº 69, Tomo A-09, la cual se inscribió también por ante el Registro Mercantil Cuarto de la Circunscripción Judicial del Distrito Capital y Estado Miranda, el 21 de abril de 2005, bajo el Nº 25, Tomo 31-A-Cto; e igualmente modificada su denominación social a la actual, así como modificados y refundidos en un solo texto sus Estatutos Sociales, como consecuencia del proceso de transformación de BANCO ACTIVO, C.A. BANCO COMERCIAL a BANCO UNIVERSAL, tal y como se evidencia de Acta de Asamblea General Extraordinaria de Accionistas, celebrada el 30 de septiembre de 2008, inscrita por ante el mismo Registro Mercantil Cuarto de la Circunscripción Judicial del Distrito Capital y Estado Miranda, el 19 de Febrero de 2009, inserta bajo el Nº 47, Tomo 24-A-Cto; la cual fue autorizada según Resolución Nº 344.08 de fecha 19 de diciembre de 2008, emitida por la Superintendencia de las Instituciones del Sector Bancario (antes Superintendencia de Bancos y Otras Instituciones Financieras), publicada en Gaceta Oficial de la República Bolivariana de Venezuela Nº 39.100 de fecha 16 de Enero de 2009, y cuyos Estatutos Sociales fueron modificados por última vez, mediante Acta de Asamblea General Extraordinaria de Accionistas celebrada en fecha 19 de Agosto de 2015, e inscrita en el Registro Mercantil Cuarto de la Circunscripción Judicial del Distrito Capital y Estado Miranda, en fecha 04 de Noviembre de 2015, bajo el Nº 13, Tomo 292-A, inscrito en el Registro de Información Fiscal bajo el Nº J-08006622-7, quien en lo adelante y a los únicos efectos del presente contrato se denominará EL BANCO, y por la otra, EL CLIENTE, quien es la persona natural cuya identificación reposa íntegramente en el Formulario de Afiliación correspondiente, se ha convenido en celebrar el presente CONTRATO DE CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, que formaliza legalmente la relación jurídica entre EL BANCO y EL CLIENTE, el cual se rige por las disposiciones contenidas en este documento, por lo dispuesto en los Convenios Cambiarios, Resoluciones, Providencias y Circulares que se dicten al efecto; por la Ley del Banco Central de Venezuela; por el Decreto con Rango, Valor y Fuerza de Ley de Instituciones del Sector Bancario; por la Ley de Tarjetas de Crédito, Débito, Prepagadas y demás Tarjetas de Financiamientos o Pago Electrónico, y demás normas referidas a esta materia,  en todos y cada uno de los artículos que le resulten aplicables. Queda entendido entre las partes, que el presente Contrato se perfeccionará cuando EL CLIENTE lo firme, dejando constancia al mismo tiempo que previamente lo ha tenido impreso, y lo ha leído para el conocimiento de sus términos y condiciones.</P><br><br><br><br>';

$contrato.= '<P ALIGN="center">CAPITULO I</P>';
$contrato.= '<P ALIGN="center">DEFINICIONES</P><br><br>';

$contrato.= '<P ALIGN="justify">PRIMERA: DEFINICIONES: Para una mejor interpretación y ejecución del contenido del presente Contrato, las palabras que se señalan a continuación, escritas en letras mayúsculas o minúsculas, en singular o plural, tendrán el significado que se determina seguidamente: <br>';

$contrato.= 'EL BANCO: Hace alusión a BANCO ACTIVO, C. A. BANCO UNIVERSAL, antes identificado, el cual ha sido autorizado para recibir depósitos en moneda extranjera.<br>';

$contrato.= 'CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL: Cuenta creada con el objeto de recibir fondos en moneda extranjera provenientes de operaciones de carácter lícito, sean estas de fuentes del exterior o del propio sistema financiero nacional e inclusive por depósito de efectivo, sin más limitación de aquellas que deriven de la política de prevención de legitimación de capitales y financiamiento al terrorismo.<br>';

$contrato.= 'EL CLIENTE: Se refiere a:<br>';

$contrato.= 'Cualesquiera personas naturales, mayores de edad, residenciadas en el territorio nacional. Identificada (s) suficientemente en el encabezado de este documento, y que habiendo cumplido previamente los requisitos exigidos por EL BANCO ha solicitado la apertura de la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL para hacer uso de los beneficios y/o servicios que se ofrecen a través de la misma, sometiéndose y aceptando las condiciones de movilización que la rigen de acuerdo a las disposiciones legales pertinentes.<br>';

$contrato.= 'OPERACIONES DE CARÁCTER LICITO: Las operaciones de carácter lícito de las cuales provienen los fondos de las personas naturales mayores de edad residenciadas en el territorio nacional, para la apertura de la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, son aquellas que tienen su origen en el giro ordinario de su actividad y no provienen de relación alguna con dinero, capitales, bienes, haberes, valores o títulos producto de las actividades o acciones a las que se refiere la Ley Orgánica Contra la Delincuencia Organizada y Financiamiento al Terrorismo, así como tampoco provienen de personas u organismos que se encuentren sujetos a investigaciones, sean sospechosos de estar incursos en procesos sancionatorios de cualquier índole dentro o fuera del país, en delitos previstos en las leyes mencionadas, en investigaciones, ni en ilícitos administrativos.<br>';

$contrato.= 'TARJETA DE DÉBITO: Es el plástico emitido a solicitud de EL CLIENTE  bajo la marca y denominación que EL BANCO acuerde, para permitir a EL CLIENTE la movilización de los fondos contra su CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL.<br>';

$contrato.= 'PARAGRAFO UNICO: Las partes que suscriben el presente contrato, aceptan que la emisión de la TARJETA DE DÉBITO se realizará únicamente en caso de solicitud expresa de EL CLIENTE, formalizada a través del Formulario respectivo que el Banco le suministre para ello. En caso de no haber solicitud previa y expresa por parte de EL CLIENTE, se considerará que no le son aplicables, aquellas cláusulas del presente contrato, que hagan referencia a los términos y condiciones de uso de la TARJETA DE DEBITO, por cuanto y en consecuencia, no se ha procedido a su emisión.<br>';

$contrato.= 'CAJEROS AUTOMÁTICOS: Son los dispositivos electrónicos que a nivel internacional o fuera del territorio de la República Bolivariana de Venezuela, a través de los cuales EL CLIENTE puede efectuar retiros en efectivo contra los haberes de su CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL.<br>';

$contrato.= 'ESTADO DE CUENTA: Es el documento elaborado por EL BANCO contentivo de la descripción de los créditos y débitos efectuados, comisiones cobradas y cualquiera otra transacción o movimiento realizado por EL CLIENTE contra su CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, el cual podrá solicitar a través de cualquier agencia de EL BANCO.<br>';

$contrato.= 'CENTRO DE ATENCIÓN TELEFONICA: Es el Centro de Servicio Telefónico donde se atienden los requerimientos y consultas solicitadas por EL CLIENTE, pudiendo realizar éste(a) a través de dicho medio: consultas de saldos, reporte de extravío, robo o hurto de su tarjeta de débito, entre otros, siempre que EL CLIENTE proporcione los datos e información requeridos por EL BANCO.<br>';

$contrato.= '</P><br><br><br><br>';

$contrato.= '<P ALIGN="center">CAPITULO II</P>';
$contrato.= '<P ALIGN="center">CONDICIONES GENERALES</P><br><br>';

$contrato.= '<P ALIGN="justify">SEGUNDA: ALCANCE DEL CONTRATO. Las condiciones generales y particulares, previstas en el presente Contrato, rigen para todos los beneficios y/o servicios asociados a la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL.<br>';

$contrato.= 'TERCERA: EXIGENCIA DE CAPACIDAD. La CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL podrá ser abierta por las personas capaces para ello,  las cuales han sido identificadas como EL CLIENTE en la cláusula Primera del presente contrato. Sin embargo en el momento de la celebración del contrato de CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, EL BANCO podrá exigir la información que considere suficiente y necesaria para la identificación de EL CLIENTE.<br>';

$contrato.= 'CUARTA: DEPÓSITOS Y MEDIOS DE MOVILIZACIÓN DE LA CUENTA. La CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL pondrá recibir depósitos en efectivo en moneda extranjera por taquilla y podrá ser movilizada por  EL CLIENTE, mediante cheques del banco depositario girado contra sus corresponsales en el exterior, así como mediante instrucciones de débito para pagos de gastos de consumo y retiros efectuados con tarjetas en el exterior, así mismo, retiros de efectivo con cargo a la cuenta, en las taquillas que a tal efecto habilite EL BANCO y conforme a los términos dictados por el Banco Central de Venezuela (BCV), previa disponibilidad de la divisa correspondiente.<br>';

$contrato.= 'QUINTA: OPERACIONES CAMBIARIAS. Las transacciones que realice EL CLIENTE dentro del territorio nacional mediante la TARJETA DE DÉBITO para pagos de gastos de compras o consumos a través de puntos de venta a nivel nacional en bolívares, se realizarán contra su Cuenta en Moneda Extranjera mantenida en EL BANCO y liquidadas en moneda nacional, mediante la realización de las operaciones cambiarias correspondientes, en estricto acatamiento a la normativa que las rige.<br>';

$contrato.= 'PARÁGRAFO PRIMERO: EL CLIENTE acepta expresamente que las operaciones de compra y venta de divisas por montos iguales o menores a la cantidad de doscientos cincuenta dólares de los Estados Unidos de América (USD 250,00), serán canalizadas únicamente mediante el mecanismo del menudeo, el cual estará disponible todos los días del año, aplicándose el tipo de cambio referencial publicado por el Banco Central de Venezuela a través de su página web. Por lo que queda expresamente entendido, que si las operación excede la cantidad antes mencionada, será canalizada a través del mecanismo de mesa de cambio, en cuyo caso EL CLIENTE tendrá la posibilidad de presentar sus cotizaciones de oferta o demanda, sin restricción alguna, salvo los procesos de debida diligencia que EL BANCO implementó en materia de Legitimación de Capitales, Financiamiento al Terrorismo y Financiamiento a la Proliferación de Armas de Destrucción Masiva. No obstante, en caso de que EL BANCO implemente productos y servicios asociados a LA CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL para agilizar y/o automatizar los pagos con cargo a las mismas que no conlleve un pacto o una negociación entre EL CLIENTE y EL BANCO, se aplicará el último tipo de cambio publicado por el Banco Central de Venezuela a través de su página web.<br>';

$contrato.= 'PARÁGRAFO SEGUNDO: EL CLIENTE acepta que el plazo a partir del cual podrá movilizar los fondos contra la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, podrá variar en ejecución a las directrices o instrucciones que en esta materia pueda dictar, el Banco Central de Venezuela, de conformidad con las Resoluciones o Leyes que al efecto se impongan; y al cumplimiento a satisfacción de EL BANCO de la normativa correspondiente a la administración y fiscalización de los riesgos relacionados con la Legitimación de Capitales, Financiamiento al Terrorismo y Financiamiento de la Proliferación de Armas de Destrucción Masiva vigente.<br>';

$contrato.= 'SEXTA: MOVILIZACIÓN RESTRINGIDA. La CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL únicamente podrá ser movilizada por EL CLIENTE, o por sus representantes legítimos o mandatarios constituidos. Para que los representantes o mandatarios puedan movilizar la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, sus firmas deberán estar incluidas en los registros de firmas autorizadas que al efecto lleva EL BANCO. En el caso de mandatos o poderes otorgados de conformidad con la Ley, cuando las firmas de los mandatarios o apoderados, no aparezcan debidamente registradas en los registros de EL BANCO, éste se reserva el derecho de verificar la autenticidad y legitimidad del representante, sin que de tal circunstancia pueda derivarse responsabilidad alguna para EL BANCO en razón del tiempo necesario que éste se tome para hacer la verificación correspondiente e incluso por la negativa de aceptar tal representación o mandato.<br>';

$contrato.= '</P><br><br><br><br>';

$contrato.= '<P ALIGN="center">CAPITULO III</P>';
$contrato.= '<P ALIGN="center">CONDICIONES PARTICULARES</P><br><br>';

$contrato.= '<P ALIGN="justify">SEPTIMA: DE LA TARJETA DE DÉBITO. EL CLIENTE  podrá solicitar expresamente a EL BANCO el servicio de TARJETA DE DÉBITO  para acceder, movilizar y utilizar su CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL a través de cajeros automáticos y puntos de venta, en el extranjero o fuera del territorio de la República Bolivariana de Venezuela y/o en territorio nacional  para el pago de consumos a través de puntos de venta a nivel nacional, cuyas operaciones serán liquidadas en moneda nacional mediante la realización de las operaciones cambiarias correspondientes, en estricto acatamiento a la normativa que las rige; así como instrumento de movilización de operaciones cambiarias,  en consecuencia acepta y reconoce que si no solicita de manera expresa, mediante los formularios y documentos que a tal efecto ha dispuesto EL BANCO para ello y por separado como servicio asociado a la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, no podrá hacer uso de ese instrumento de movilización, ya que de conformidad con las Normas relativas a la Protección de los Usuarios y Usuarias de los Servicios Financieros y a la Ley de Tarjetas de Crédito, Débito, Prepagadas y demás Tarjetas de Financiamientos o Pago Electrónico, no es un servicio generado de manera automática, sino de expresa solicitud por parte de EL CLIENTE. La utilización de este servicio se regirá por los términos y condiciones detallados a continuación:<br>';

$contrato.= '7.1 EL BANCO, al momento de recibir de parte de EL CLIENTE, la solicitud para la emisión y entrega de la TARJETA DE DÉBITO, le hace entrega del FOLLETO EXPLICATIVO, donde se indica la información detallada del servicio que se ofrece y de los beneficios de la TARJETA DE DÉBITO, dejando constancia de la entrega en recibo suscrito por EL CLIENTE.<br>';

$contrato.= '7.2 La TARJETA DE DÉBITO será de uso en el extranjero o fuera del territorio de la República Bolivariana de Venezuela y estará limitada a la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, a la suficiencia de fondos y se rige por lo establecido en este contrato.<br>';

$contrato.= '7.3 EL BANCO debitará contra la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, los retiros en efectivo, los pagos realizados a través de puntos de venta y las comisiones asociadas a ello, efectuados mediante el uso de la TARJETA DE DÉBITO.<br>';

$contrato.= '7.4 EL BANCO proveerá una tarjeta plástica con chip que permitirá el acceso a la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, de conformidad con lo indicado en el presente contrato.<br>';

$contrato.= '7.5 EL CLIENTE mantendrá el uso de la TARJETA DE DÉBITO, en forma personal y confidencial y se obliga a seguir las sugerencias e instrucciones de EL BANCO en cuanto al manejo de la misma, a fin de evitar su uso indebido.<br>';

$contrato.= '7.6 Toda operación realizada a través del uso de la TARJETA DE DÉBITO se entiende efectuada por  EL CLIENTE o autorizada por éste. En consecuencia, serán de la exclusiva responsabilidad de EL CLIENTE todas las transacciones que se deriven de tales  operaciones, a menos que se demuestre que hubo hurto, robo, extravío, fraude, clonación o pérdida de la tarjeta y/o de su clave secreta, por parte de personas diferentes a EL CLIENTE y por lo tanto no habrá pago, por ser indebido, de acuerdo a lo indicado en el artículo 12 de la resolución No. 339.08 de fecha 18 de diciembre de 2008, publicada en la Gaceta Oficial de la República Bolivariana de Venezuela No. 39.112 del 03 de febrero de 2009, contentiva de las Normas que Regulan los Procesos Administrativos  Relacionados a la Emisión y Uso de las Tarjetas de Crédito, Debito, Prepagadas y demás Tarjetas de Financiamiento o Pago Electrónico; y cuando EL CLIENTE no realice reparos o reclamos relativos a estas operaciones, los cuales deberá informar a EL BANCO, de inmediato, en cuyo caso EL BANCO, realizará la correspondiente investigación de los hechos denunciados. EL BANCO, deberá al momento de recibir dicha notificación dar a EL CLIENTE, el número de registro o de gestión bajo el cual quedó registrada la denuncia, indicándole al mismo tiempo el lapso de la gestión administrativa para dar respuesta.<br>';

$contrato.= '7.7 LA TARJETA DE DÉBITO es intransferible y propiedad de EL BANCO.<br>';

$contrato.= '7.8 EL CLIENTE reconoce que en caso que a EL BANCO, se le imposibilite emitir Tarjetas de Débito para la movilización de este tipo de cuenta en el extranjero o fuera de la República Bolivariana de Venezuela, por causas de fuerza mayor, lo exonera de responsabilidad, así acepta que mientras dure tal circunstancia la movilización de dicha cuenta será mediante los medios disponibles.<br>';

$contrato.= '7.9 EL CLIENTE podrá utilizar la TARJETA DE DÉBITO  en el exterior, en puntos de venta y para el retiro de efectivo en cajeros automáticos, previa autorización de EL BANCO, cumpliendo EL CLIENTE con la normativa vigente y/o la que aplique en el futuro emitidas por el organismo rector Banco Central de Venezuela. EL CLIENTE conforme  a lo dispuesto en la normativa aplicable,  deberá notificar  a EL BANCO  del viaje internacional, indicando el lugar y el período por el cual se usará la TARJETA DE DÉBITO, para proceder a su activación para este tipo de operaciones.<br>';

$contrato.= 'OCTAVA: LÍMITES DE LA RESPONSABILIDAD RESPECTO A LA TARJETA DE DÉBITO. EL CLIENTE asume frente a EL BANCO, una responsabilidad absoluta e irrestricta por lo que respecta al uso de la TARJETA DE DÉBITO y de la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, responsabilizándose por la absoluta discreción y reserva de ésta.<br>';

$contrato.= 'NOVENA: CARÁCTER PERSONAL DEL USO DE LA TARJETA DE DÉBITO. Motivado a que EL CLIENTE es el responsable de la TARJETA DE DÉBITO, se considera y así lo acepta, que todo retiro o pago realizado contra la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL ha sido efectuado por él mismo. A todo evento, el ESTADO DE CUENTA emitido por EL BANCO hará plena prueba de las transacciones efectuadas por EL CLIENTE, con excepción de los reparos realizados a dicho ESTADO DE CUENTA.<br>';

$contrato.= 'DÉCIMA: OBSERVACIONES A LOS ESTADOS DE CUENTA. Si EL CLIENTE, tiene observaciones que formular a los saldos o registros indicados en el estado de cuenta, deberá hacerlas llegar a EL  BANCO por escrito, en forma detallada y razonada, dentro de los seis (6) meses siguientes a la fecha de recepción del estado de cuenta. Dentro del referido plazo de seis (6) meses siguientes a la fecha de terminación del respectivo mes, tanto EL CLIENTE, como EL BANCO, podrán, bajo pena de caducidad, impugnar el respectivo estado de cuenta por errores de cálculo o de escritura, por omisiones o duplicaciones, vencido el plazo antes indicado sin que EL BANCO, haya recibido ni las observaciones ni la conformidad de EL CLIENTE o sin que se haya impugnado los saldos o registros indicados en el estado de cuenta, se tendrá por reconocido en la forma presentada, sus saldos deudores o acreedores serán definitivos.<br>';

$contrato.= 'DECIMA PRIMERA: DECLARACIÓN DE CONOCIMIENTO PREVIO DEL CONTRATO. EL CLIENTE declara que de conformidad con lo dispuesto en las  Normas relativas a la Protección de los Usuarios y Usuarias de los Servicios Financieros, dictadas por la Superintendencia de las Instituciones del Sector Bancario, mediante Resolución Nº 063.15 de fecha 12 de junio de 2015, publicadas en la Gaceta Oficial de la República Bolivariana de Venezuela No. 40.809 de fecha 14 de diciembre de 2015,   EL BANCO le hizo entrega a EL CLIENTE con anterioridad, de un ejemplar del presente contrato y EL CLIENTE dispuso de tiempo suficiente para examinar su contenido y comprender el exacto alcance, trascendencia y consecuencias jurídicas del mismo, las cuales acepta sin reparo u objeción, por constituir las mismas reflejo fiel y exacto de su voluntad.<br>';

$contrato.= 'DECIMA SEGUNDA: MODIFICACIÓN DE LA INFORMACIÓN. EL CLIENTE se obliga a indicar a EL BANCO los cambios de dirección o de teléfono, así como de cualquier modificación en la información suministrada a éste con anterioridad, y en tanto dicha notificación no conste como recibida por EL BANCO, se tendrá como válida la información que reposa en los registros de EL BANCO. <br>';

$contrato.= 'DECIMA TERCERA: NATURALEZA DEL CONTRATO. EL CLIENTE acepta que el presente contrato ha sido celebrado “Intuito personae”, en consecuencia EL BANCO ha tomado como elementos fundamentales las condiciones, cualidades, y demás particularidades de EL CLIENTE, en consecuencia se le prohíbe a EL CLIENTE cederlo.<br>';

$contrato.= 'DECIMA CUARTA: MODIFICACIONES POR DISPOSICIONES REGULATORIAS. EL BANCO en base a las reformas en materia cambiaria que disponga el Banco Central de Venezuela, podrá modificar o adicionar los términos y condiciones del presente contrato, quedando EL CLIENTE, en todo caso, sujeto a lo dispuesto en la normativa cambiaria y demás leyes aplicables.<br>';

$contrato.= 'DECIMA QUINTA: NOTIFICACIONES. Cualquier orden, notificación, aviso o comunicación que EL CLIENTE deba dirigir a EL BANCO con relación a la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL deberá realizarla por escrito.<br>';

$contrato.= 'DECIMA SEXTA: AUTORIZACIÓN DE CARGO EN CUENTA. EL BANCO podrá cobrar las tarifas y las comisiones por las operaciones que se realicen contra la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, de acuerdo al Aviso Oficial publicado por el Banco Central de Venezuela contentivo de los límites máximos de las tarifas, comisiones y/o recargos que podrán cobrar las instituciones bancarias, casas de cambio y los proveedores no bancarios de terminales de puntos de venta a sus clientes.<br>';

$contrato.= 'En este sentido, EL CLIENTE autoriza a cobrar y a cargar contra la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL las tarifas, comisiones y/o recargos de acuerdo al Aviso Oficial publicado por el Banco Central de Venezuela contentivo de los límites máximos  de las tarifas, comisiones y/o recargos que podrán cobrar las instituciones bancarias, casas de cambio y los proveedores no bancarios de terminales de puntos de venta a sus clientes y  serán del conocimiento de EL CLIENTE  a través de toda la información y medios que EL BANCO disponga, inclusive  mediante anuncio en su página pública, que permitan a EL CLIENTE su debido conocimiento.<br>';

$contrato.= 'DECIMA SEPTIMA: TERMINACIÓN CONTRACTUAL. Se consideran causas de suspensión o terminación de este contrato, las siguientes: a) El incumplimiento por parte de EL CLIENTE de cualquiera de las obligaciones previstas en este Contrato; b) El fraude cometido por EL CLIENTE en contra de EL BANCO; c) El suministro por parte de EL CLIENTE de información falsa, insuficiente o incompleta a EL BANCO, así como el incumplimiento de la normativa correspondiente  a la administración y fiscalización de los riesgos relacionados con la Legitimación de Capitales, Financiamiento al Terrorismo y Financiamiento de la Proliferación de Armas de Destrucción Masiva vigente; d) La solicitud de terminación por parte de EL CLIENTE hecha por escrito a EL BANCO; e) La muerte de EL CLIENTE, lo cual debe ser informado a EL BANCO, por sus herederos o causahabientes, consignando a tal fin la documentación legal requerida por EL BANCO; f) La cancelación de la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL; g) por hacerse imposible el cumplimiento o fin del presente contrato.<br>';

$contrato.= 'DECIMA OCTAVA: AJUSTE CONTRACTUAL AUTOMÁTICO. En caso de que se sancione alguna norma o precepto legal en el futuro en materia cambiaria, las partes han convenido en que el presente documento se considerará de pleno derecho adecuado a dicha norma o precepto y a ellas se ajustará la ejecución y cumplimiento del mismo por ambas partes. Sin embargo, las partes convienen en que sus disposiciones son de interpretación restrictivas en cuanto al análisis del contenido de las mismas.<br>';

$contrato.= 'DECIMA NOVENA: DECLARACIÓN DE ORIGEN DE LOS FONDOS. EL CLIENTE bajo fe de juramento declara, que el origen de los fondos movilizados a través de la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, son provenientes de las actividades comerciales y/o profesionales lícitas que realiza, por lo tanto exime y releva completa y absolutamente a EL BANCO de toda responsabilidad que se derive o pudiera derivarse, directa o indirectamente por cualesquiera investigaciones que los órganos competentes realicen y cuyo objeto se relacione con el origen de los fondos depositados y/o movilizados en la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL. Así mismo, declara que todos los datos suministrados a EL BANCO son veraces, verificables y comprobables, en el entendido de que de igual forma conoce las sanciones aplicables de acuerdo a lo establecido en el Decreto con Rango, Valor y Fuerza de Ley de Instituciones del Sector Bancario.<br>';

$contrato.= 'VIGÉSIMA: CONFIDENCIALIDAD. La información suministrada por EL CLIENTE a EL BANCO, así como la derivada de la relación comercial con éste y del manejo de la CUENTA EN MONEDA EXTRANJERA EN EL SISTEMA FINANCIERO NACIONAL, tendrán carácter confidencial. No obstante, EL CLIENTE expresamente autoriza a EL BANCO a intercambiar su información personal y de la Cuenta con otras instituciones financieras y agencias centralizadoras de información de riesgos y crédito, mediante cualquier medio de información impreso o electrónico, ya sea que este provenga de EL CLIENTE o sea producto de su relación con EL BANCO o con terceros. Asimismo declara que está en conocimiento de que EL BANCO debe revelar su información cuando es requerido por órganos del Estado de conformidad con el Decreto con Rango, Valor y Fuerza de Ley de Instituciones del Sector Bancario.<br>';

$contrato.= 'VIGESIMA PRIMERA: AUTORIZACIÓN DE VERIFICACIÓN DE LA INFORMACIÓN. EL CLIENTE declara que todos los datos suministrados por él a EL BANCO, son ciertos, y en consecuencia podrán ser verificables y comprobables en cualquier momento.<br>';

$contrato.= 'VIGESIMA SEGUNDA: DOMICILIO. Para todos los efectos y derivados de este Contrato, se elige como domicilio especial al domicilio de EL CLIENTE.<br>';

$contrato.= 'En Caracas, a la fecha de la solicitud contenida en el formulario de afiliación, el cual se acompaña con el presente para que forme integridad.</P><br>';

//$check = ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'check_sa.png';
//**************************************************************** TERCERA HOJA
$pdf->AddPage();
$pdf->setMargins(20,20,20);
$pdf->setAutoPageBreak(true,20);

$pdf->setFont('Arial','B',11);
$pdf->cell(0,6,"CONTRATO DE CUENTA EN MONEDA EXTRANJERA PERSONA NATURAL",0,1,'C');

$pdf->ln();

$pdf->setFont('Arial','',9);
$pdf->cell(0,6,$fecha,0,0,'R');

$pdf->ln();

$pdf->setFont('Arial','',9);
$pdf->WriteHTML($contrato);


$pdf->output(date('YmdHis').'.pdf','D');

exit;


?>