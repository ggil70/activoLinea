<?php
/**
 * Generador de planillas de afiliación de usuarios jurídicos
**/

App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

Class PlanillaPDF extends FPDF{

	function header(){
		$this->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,50);
		
		$this->setTextColor(0,0,0);
		$this->setFont('Arial','',10);
		$this->cell(0,5,"RIF: J-08006622-7",0,0,'R');
		
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
$pdf->AddPage();

//**************************************************************** PRIMERA HOJA
$pdf->setFont('Arial','B',11);

$pdf->cell(0,6,"SOLICITUD DE VERIFICACIÓN DE",0,0,'C');
$pdf->ln();
$pdf->cell(0,6,"USUARIO PARA ACTIVO EN LÍNEA EMPRESAS",0,0,'C');

$pdf->ln();
$pdf->ln();
$pdf->ln();

$pdf->setFont('Arial','',8);
$pdf->Multicell(0,6,"   El(los) abajo firmante(s), mayor(es) de edad, actuando en mi(nuestro) carácter de Firma(s) Autorizada(s) de la Empresa ".$estado['Usuario']['razon'].", N° RIF " . $estado['Usuario']['tipoRif']."-".$estado['Usuario']['nroRif'] . ", por medio del presente documento declaro(amos):",0,'J');
$pdf->ln();

$pdf->SetLeftMargin(20);
$pdf->MultiCell(0,6,'1.	Que he(mos) solicitado a través de la página de Internet Banking de Banco Activo, C.A. Banco Universal, el servicio ACTIVO EN LÍNEA EMPRESAS, cuyos términos y condiciones conozco(emos)  y acepto(amos)  para la movilización de los productos a través de este servicio, el cual refrendo(amos) en contrato que se anexa a la presente solicitud.',0,'J');
$pdf->ln();

$perfil = $estado['Usuario']['perfil'] == 1 ? 'MASTER' : 'ÚNICO';

$pdf->MultiCell(0,6,'2.	Que se ha designado a ______________________________________, titular de la cédula de identidad N° __-______________ , como la persona responsable del servicio ACTIVO EN LÍNEA EMPRESAS, con el perfil '. $perfil .', según consta en la PLANILLA AUTOREGISTRO RESPONSABLE DEL SERVICIO ACTIVO EN LÍNEA EMPRESAS, que también se anexa a la presente solicitud.',0,'J');
$pdf->ln();

$pdf->cell(0,5,'En '.str_repeat("_", 30).', a los '.str_repeat("_", 5).' días del mes de '.str_repeat("_", 15).' de 20'.str_repeat("_", 5).'.',0,0,'L');

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'Firmas.png',20,139,175,70);
$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'Firmas_Banco.png',20,212,175,35);
$pdf->ln();
$pdf->ln();
$pdf->ln();

$pdf->setY(-30);

$pdf->line(10,$pdf->getY(),205,$pdf->getY());

$pdf->setFont('Arial','',5);
$pdf->cell(0,3,'MCE.FE.0382.V05',0,0,'R');

$pdf->setFont('Arial','',6);
$pdf->cell(0,3,'',0,0,'C');
$pdf->ln(2);
$pdf->cell(0,3,'El uso de este Formulario es de carácter Obligatorio',0,0,'C');
$pdf->ln(3);
$pdf->cell(0,3,'para el procedimiento establecido',0,0,'C');
$pdf->ln(3);
$pdf->cell(0,3,'Información Propiedad de Banco Activo, C.A. Banco Universal',0,0,'C');
$pdf->ln(3);
$pdf->cell(0,3,'Todos los Derechos Reservados.',0,0,'C');
$pdf->ln(3);
$pdf->cell(0,3,'',0,0,'C');
$pdf->ln(2);

$pdf->line(30,$pdf->getY(),190,$pdf->getY());



//**************************************************************** SEGUNDA HOJA

$pdf->AddPage();

$pdf->setFont('Arial','B',11);

$pdf->cell(0,6,"PLANILLA AUTOREGISTRO RESPONSABLE DEL",0,0,'C');
$pdf->ln();
$pdf->cell(0,6,"SERVICIO ACTIVO EN LÍNEA EMPRESAS",0,0,'C');

$pdf->ln(8);

$pdf->setFont('Arial','B',10);
$pdf->cell(0,5,"DATOS DE LA EMPRESA:",0,0,'L');
$pdf->ln();

$pdf->setFont('Arial','',8);
$pdf->cell(80,6,"Razón Social: ".$estado['Usuario']['razon'],0,0,'L');
$pdf->cell(0,6,"RIF: ".$estado['Usuario']['tipoRif'].'-'.$estado['Usuario']['nroRif'],0,0,'L');
$pdf->ln(6);

$pdf->setFont('Arial','B',10);
$pdf->cell(0,5,"DATOS DEL RESPONSABLE:",0,0,'L');
$pdf->ln();

$pdf->setFont('Arial','',8);
$pdf->cell(80,6,"Nombre y Apellido: ".$estado['Usuario']['nombre'],0,0,'L');
$pdf->cell(0,6,"Cédula de Identidad: ".$estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'],0,0,'L');
$pdf->ln();
$pdf->cell(50,6,"Cargo: ".$estado['Usuario']['cargo'],0,0,'L');
$pdf->cell(85,6,"Correo Electrónico: ".$estado['Usuario']['emailPri'],0,0,'L');
$pdf->cell(0,6,"Teléfono Celular: ".$estado['Usuario']['codigoCelular'].$estado['Usuario']['nroCelular'],0,0,'L');
$pdf->ln(8);
$pdf->setFont('Arial','B',8);
$pdf->cell(0,5,"PERFIL:",0,0,'L');
$pdf->ln();

$pdf->SetLeftMargin(20);
$pdf->setFont('Arial','B',8);

if($estado['Usuario']['perfil']=='1'){
	$pdf->cell(5,5,'X',1,0,'C');
} else {
	$pdf->cell(5,5,' ',1,0,'C');
}

$pdf->cell(0,5,'Master:',0,0,'L');
$pdf->ln();

$pdf->SetLeftMargin(25);
$pdf->setFont('Arial','',8);
$pdf->MultiCell(0,6,'Este perfil permite al usuario designado tener el rol de administrador de la seguridad del Servicio de Internet Banking, el cual contempla: la creación de los distintos usuarios a los cuales se les delegara las funciones específicas que tendrá cada uno de acuerdo a su perfil, los límites de montos y el esquema de aprobación que van a manejar para las transacciones financieras.',0,'J');

$pdf->SetLeftMargin(20);
$pdf->ln(3);
$pdf->setFont('Arial','B',8);
if($estado['Usuario']['perfil']=='2'){
	$pdf->cell(5,5,'X',1,0,'C');
} else {
	$pdf->cell(5,5,' ',1,0,'C');
}
$pdf->cell(0,5,'Master Único:',0,0,'L');
$pdf->ln();

$pdf->SetLeftMargin(25);
$pdf->setFont('Arial','',8);
$pdf->MultiCell(0,6,'Este perfil permite al usuario designado tener autonomía para realizar cualquier operación disponible en el Servicio de Internet Banking. No permite delegar funciones en terceras personas y si la empresa cuenta con usuarios delegados, todos quedarán eliminados cuando se concrete el proceso de verificación del banco.',0,'J');
$pdf->ln(1);

$pdf->setFont('Arial','B',8);
$pdf->Write(5,'NOTA:');
$pdf->setFont('Arial','',8);
$pdf->Write(5,' El cambio de perfil de un usuario Master a Master Único, conlleva la eliminación de todos los usuarios que han sido delegados.');

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'Firmas.png',20,141,175,70);
$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'Firmas_Banco.png',20,212,175,35);
$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->SetLeftMargin(10);

$pdf->setY(-30);

$pdf->line(10,$pdf->getY(),205,$pdf->getY());

$pdf->setFont('Arial','',5);
$pdf->cell(0,3,'MCE.FE0383.V03',0,0,'R');

$pdf->setFont('Arial','',6);
$pdf->cell(0,3,'',0,0,'C');
$pdf->ln(2);
$pdf->cell(0,3,'El uso de este Formulario es de carácter Obligatorio',0,0,'C');
$pdf->ln(3);
$pdf->cell(0,3,'para el procedimiento establecido',0,0,'C');
$pdf->ln(3);
$pdf->cell(0,3,'Información Propiedad de Banco Activo, C.A. Banco Universal',0,0,'C');
$pdf->ln(3);
$pdf->cell(0,3,'Todos los Derechos Reservados.',0,0,'C');
$pdf->ln(3);
$pdf->cell(0,3,'',0,0,'C');
$pdf->ln(2);

$pdf->line(30,$pdf->getY(),190,$pdf->getY());


// TEXTO DEL CONTRATO
$contrato = '<P ALIGN="justify">Yo, ALEJANDRO ENRIQUE MONTENEGRO DIAZ, de nacionalidad venezolana, mayor de edad, civilmente hábil, de estado civil casado, de este domicilio y titular de la Cédula de Identidad Nº V-11.307.248, actuando en este acto en mi carácter de Vicepresidente de la Junta Directiva de “BANCO ACTIVO, C.A. BANCO UNIVERSAL”, domiciliado en Caracas, originalmente inscrito ante el Registro Mercantil de la Circunscripción Judicial del Estado Anzoátegui, el 11 de abril de 1978, bajo el Nº 73, Tomo A, bajo la denominación social de Banco Hipotecario Oriental C.A., modificado posteriormente su domicilio legal al actual, tal y como se evidencia de Acta de Asamblea General Extraordinaria de Accionistas, celebrada en fecha 11 de marzo de 2005, inscrita por ante el Registro Mercantil Primero de la Circunscripción Judicial del Estado Anzoátegui, el 16 de marzo de 2005 , bajo el Nº 69, Tomo A-09, la cual se inscribió también por ante el Registro Mercantil Cuarto de la Circunscripción Judicial del Distrito Capital y Estado Miranda, el 21 de abril de 2005 , bajo el Nº 25, Tomo 31-A-Cto; e igualmente modificada su denominación social a la actual, así como modificados y refundidos en un solo texto sus Estatutos Sociales, como consecuencia del proceso de transformación de BANCO ACTIVO, C.A. BANCO COMERCIAL a BANCO UNIVERSAL, tal y como se evidencia de Acta de Asamblea General Extraordinaria de Accionistas, celebrada el 30 de septiembre de 2008, inscrita por ante el mismo Registro Mercantil Cuarto de la Circunscripción Judicial del Distrito Capital y Estado Miranda, el 19 de Febrero de 2009 , inserta bajo el Nº 47, Tomo 24-A-Cto; la cual fue autorizada según Resolución Nº 344.08 de fecha 19 de diciembre de 2008, emitida por la Superintendencia de las Instituciones del Sector Bancario (antes Superintendencia de Bancos y Otras Instituciones Financieras), publicada en Gaceta Oficial de la República Bolivariana de Venezuela Nº 39.100 de fecha 16 de Enero de 2009, y cuyos Estatutos Sociales fueron modificados por última vez, mediante Acta de Asamblea General Extraordinaria de Accionistas celebrada en fecha 19 de Agosto de 2015, e inscrita en el Registro Mercantil Cuarto de la Circunscripción Judicial del Distrito Capital y Estado Miranda, en fecha 04 de Noviembre de 2015, bajo el Nº 13, Tomo 292-A, inscrito en el Registro de Información Fiscal bajo el Nº J-08006622-7; carácter el mío que se desprende de Asamblea General Extraordinaria de Accionistas de fecha 23 de marzo de 2020, aprobada la designación por la Superintendencia de las Instituciones del Sector Bancario por medio del oficio Nro. SIB-II-GGR-GA-02894, de fecha 15 de junio de 2020, declaro: Que el servicio ACTIVO EN LÍNEA EMPRESA que más adelante se describe ofrecido por mi representada a cualquier cliente del mismo, en adelante y para todos los efectos de este documento denominado EL CLIENTE se regirá por los términos, modalidades y condiciones establecidas en las siguientes cláusulas: 
<BR>
<U>PRIMERA:</U> A los fines de este documento se establecen las siguientes definiciones, algunas de las cuales podrán ser presentadas indistintamente en singular o plural según corresponda en relación con el texto en el cual aparezcan: 
<BR>
1) BANCO ACTIVO: Es BANCO ACTIVO, C.A. BANCO UNIVERSAL, sociedad mercantil ya identificada.
<BR>
2) EL CLIENTE: Es toda persona jurídica de carácter público o privado que tenga productos en BANCO ACTIVO y que utilice el Servicio ACTIVO EN LÍNEA EMPRESA para realizar operaciones de acuerdo con lo previsto en el presente documento.
<BR>
3) ACTIVO EN LÍNEA EMPRESA: Es un servicio descrito en la cláusula segunda de este documento.
<BR>
4) PERSONA AUTORIZADA: Significa el o las personas naturales, Representante Legal de EL CLIENTE o empleado de este, suficientemente facultada por EL CLIENTE para acceder al servicio ACTIVO EN LÍNEA EMPRESA, a los fines de que actúen por cuenta y orden de EL CLIENTE para generar, aprobar o no operaciones, recibir mensajes de datos respecto de dichas operaciones. Estas personas para poder utilizar el servicio ACTIVO EN LÍNEA EMPRESA deben registrarse como un USUARIO del servicio. 
<BR>
5) USUARIO: Es la identificación con la cual se registra en ACTIVO EN LÍNEA EMPRESA, toda PERSONA AUTORIZADA, al cual le ha sido designado una determinada responsabilidad de uso de una o varias de las funcionalidades disponibles en el servicio. 
<BR>
6) ESQUEMA DE FUNCIONAMIENTO: Es la modalidad que EL CLIENTE decide utilizar para manejar el servicio ACTIVO EN LÍNEA EMPRESA. Las modalidades de uso disponibles para el servicio, son: DELEGACION MULTIPLE y DELEGACION UNICA.
<BR>
7) DELEGACION MULTIPLE: Es un ESQUEMA DE FUNCIONAMIENTO del servicio ACTIVO EN LÍNEA EMPRESA que le permite a EL CLIENTE delegar en varios USUARIOS con PERFIL MASTER, OPERADOR, APROBADOR y OPERADOR/APROBADOR, la utilización de las distintas funcionalidades disponibles en el servicio ACTIVO EN LÍNEA EMPRESA. Para ello, EL CLIENTE designa como responsable del servicio al USUARIO con PERFIL MASTER.
<BR>
8) DELEGACION UNICA: Es un ESQUEMA DE FUNCIONAMIENTO del servicio ACTIVO EN LÍNEA EMPRESA que le permite a EL CLIENTE delegar en un solo USUARIO, la utilización de todas las funcionalidades disponibles en el servicio ACTIVO EN LÍNEA EMPRESA. Para ello, EL CLIENTE designa como responsable del servicio a un USUARIO que tendrá el PERFIL denominado MASTER UNICO. 
<BR>
9) PERFIL: Son los privilegios mínimos que se le asigna a cada USUARIO que utiliza el servicio ACTIVO EN LÍNEA EMPRESA, de acuerdo al ESQUEMA DE FUNCIONAMIENTO que escoja EL CLIENTE. 
<BR>
10) MASTER: Es el PERFIL que le asigna EL CLIENTE al USUARIO que tendrá toda la responsabilidad de la administración de la seguridad y de los distintos USUARIOS que utilizaran el servicio ACTIVO EN LÍNEA EMPRESA. 
<BR>
11) MASTER UNICO: Es el PERFIL que le asigna EL CLIENTE al USUARIO que tendrá todos los privilegios requeridos para utilizar, sin restricción alguna, todas las funcionalidades disponibles en el servicio ACTIVO EN LÍNEA EMPRESA. 
<BR>
12) OPERADOR: Es el PERFIL designado al USUARIO que tendrá dentro de sus responsabilidades mínimas, la función de preparar y registrar las distintas TRANSACCIONES FINANCIERAS hasta por el monto que determine el USUARIO con PERFIL MASTER, las cuales se ejecutaran a través del servicio ACTIVO EN LÍNEA EMPRESA luego de ser aprobadas por uno o dos USUARIOS que tengan un PERFIL denominado APROBADOR o OPERADOR/APROBADOR. 
<BR>
13) APROBADOR: Es el PERFIL designado al USUARIO que tendrá dentro de sus responsabilidades mínimas, la función de aprobar las distintas TRANSACCIONES FINANCIERAS registradas en el servicio ACTIVO EN LÍNEA EMPRESA por un USUARIO que tenga el PERFIL de OPERADOR u OPERADOR/APROBADOR. Los USUARIOS definidos con este tipo de PERFIL no tienen la facultad de preparar y registrar ninguna TRANSACCION FINANCIERA. 
<BR>
14) OPERADOR/APROBADOR: Es el PERFIL designado al USUARIO que tendrá dentro de sus responsabilidades mínimas, la función de fungir como un USUARIO con el PERFIL de OPERADOR o como un USUARIO con el PERFIL de APROBADOR. En ningún caso este USUARIO podrá aprobar TRANSACCIONES FINANCIERAS registradas por él. 
<BR>
15) TRANSACCIONES FINANCIERAS: Son aquellas operaciones, que el USUARIO puede realizar a través del servicio ACTIVO EN LÍNEA EMPRESA y que involucran movimientos de carácter financiero, tales como transferencias y pagos, a cuentas propias o de terceros en BANCO ACTIVO o cualquier otro Banco del sistema financiero nacional e internacional, cuando ello aplique. 
<BR>
16) TRANSACCIONES NO FINANCIERAS: Son todas aquellas operaciones que el USUARIO realiza a través del servicio ACTIVO EN LÍNEA EMPRESA, que no son de carácter financiero. Se mencionan como tales; consultas, afiliaciones, actualizaciones, solicitudes, administración de seguridad, considerando las mismas de carácter enunciativo y no taxativo. 
<BR>
17) CLAVE DE ACCESO: Es un código alfanumérico, secreto, confidencial e intransferible, creada por el USUARIO, al momento de registrarse al servicio ACTIVO EN LÍNEA EMPRESA, como USUARIO. 
<BR>
18) FIRMA ELECTRONICA: Información creada o utilizada por el signatario BANCO ACTIVO y/o EL CLIENTE asociada al mensaje de datos, que permite atribuirle su autoría bajo el contexto en el cual ha sido utilizada, conforme a lo establecido en el Decreto con Fuerza de Ley Nº 1.204 de fecha 10 de febrero de 2001, de Mensajes de Datos y Firmas Electrónicas, publicada en la Gaceta Oficial de la República Bolivariana de Venezuela Nº 37.148 de fecha 28 de febrero de 2011. 
<BR>
19) SERVICIO ESPECIALIZADO: Es un servicio que presta BANCO ACTIVO a través de ACTIVO EN LÍNEA EMPRESA y otros canales previstos por BANCO ACTIVO, con características particulares definidas en las condiciones que lo regulan, como: Pago de Nómina, Pago a Proveedores, otros que incorpore BANCO ACTIVO. 
<U>SEGUNDA:</U> BANCO ACTIVO ha implementado un servicio denominado ACTIVO EN LÍNEA EMPRESA, mediante el cual EL CLIENTE podrá, a través de dispositivos electrónicos por intermedio de la Red Mundial de Información denominada Internet (World Wide Web), recibir información, realizar TRANSACCIONES FINANCIERAS y TRANSACCIONES NO FINANCIERAS, que se encuentren disponibles en el servicio. 

<U>Parágrafo único:</U> EL CLIENTE para utilizar otros SERVICIOS ESPECIALIZADOS a través del servicio ACTIVO EN LÍNEA EMPRESA deberá suscribir los respectivos anexos que formarán parte integrante del presente contrato. 

<U>TERCERA:</U> EL CLIENTE podrá acceder al uso del servicio ACTIVO EN LÍNEA EMPRESA a través de la página de Internet creada a tales fines por BANCO ACTIVO. 

<U>CUARTA:</U> Todas las transacciones realizadas por EL CLIENTE a través del servicio ACTIVO EN LÍNEA EMPRESA, serán procesadas bajo los términos y condiciones del presente contrato, o las que a tal efecto indique BANCO ACTIVO en cada oportunidad, a través del medio que disponga para tal fin. 

<U>QUINTA:</U> Los equipos, sistemas, programas de navegación por Internet y cualesquiera otros programas requeridos para poder disfrutar del servicio ACTIVO EN LÍNEA EMPRESA, serán adquiridos o utilizados por EL CLIENTE a su costo y riesgo, y deberán tener las características técnicas necesarias para permitir al acceso a Internet. BANCO ACTIVO NO SERÁ RESPONSABLE EN NINGÚN CASO POR EL FUNCIONAMIENTO, IDONEIDAD, CAPACIDAD Y COMPATIBILIDAD DE LOS EQUIPOS, SISTEMAS Y PROGRAMAS ADQUIRIDOS O UTILIZADOS POR EL CLIENTE A LOS EFECTOS DE DISFRUTAR EL SERVICIO ACTIVO EN LÍNEA EMPRESA.

<U>SEXTA:</U> EL CLIENTE accederá al uso del servicio ACTIVO EN LÍNEA EMPRESA a través del USUARIO y la CLAVE DE ACCESO, de acuerdo a las especificaciones de ingreso que posee el mismo. Es entendido que para determinadas funciones, operaciones o transacciones, el servicio podrá requerir un segundo factor de autenticación, por el riesgo que las mismas conllevan, pudiendo asimismo BANCO ACTIVO requerir el cumplimiento de determinados requisitos y condiciones por parte de EL CLIENTE. Las CLAVES DE ACCESO y los otros factores de autenticación, tendrán carácter personal, confidencial e intransferible, y BANCO ACTIVO no tendrá conocimiento alguno de los mismos, por lo cual será responsabilidad de EL CLIENTE QUE EL USUARIO GARANTICE LA GUARDA, CUIDO Y CUSTODIA DE SU CLAVE DE ACCESO Y LOS OTROS FACTORES DE AUTENTICACIÓN, TOMANDO POR ANALOGIA, LO ESTABLECIDO EN EL ARTÍCULO 26 DE LA LEY DE TARJETAS DE CRÉDITO, DEBITO, PREPAGADAS Y DEMÁS TARJETAS DE FINANCIAMIENTO O PAGO ELECTRÓNICO, QUE TIENE COMO DEBER RESGUARDAR LA TARJETA, CON LA DEBIDA DILIGENCIA.

<U>Parágrafo único:</U> LAS OPERACIONES EFECTUADAS A TRAVES DEL SERVICIO ACTIVO EN LÍNEA EMPRESA, SERÁN CONSIDERADAS COMO REALIZADAS POR EL CLIENTE.

<U>SEPTIMA:</U> El USUARIO con PERFIL MASTER a través del propio servicio ACTIVO EN LÍNEA EMPRESA podrá autorizar, a su libre elección, a las personas que tuviese a bien para acceder al servicio ACTIVO EN LÍNEA EMPRESA, registrándolos a tal fin y creando el USUARIO correspondiente mediante el suministro de los datos personales y de contacto de las PERSONAS AUTORIZADAS, definiendo el PERFIL y funciones adicionales de acceso de cada una de ellas, es decir, las funcionalidades del servicio y las respectivas opciones específicas a las que cada una de dichas PERSONAS AUTORIZADAS podrán tener acceso. Las mencionadas autorizaciones serán responsabilidad del USUARIO con PERFIL MASTER, no teniendo BANCO ACTIVO ningún conocimiento sobre las mismas, considerándose como emanadas de EL CLIENTE. 

<U>OCTAVA:</U> A través del servicio ACTIVO EN LÍNEA EMPRESA, EL CLIENTE podrá visualizar los productos disponibles a través del mismo, así como ver los registros y/o estados de cuenta de las transacciones de cada uno de ellos, limitar o suprimir su acceso a determinadas funciones u opciones del servicio ACTIVO EN LÍNEA EMPRESA, en los casos en que ello fuere técnicamente posible en función de las posibilidades del servicio y con las limitaciones que BANCO ACTIVO tenga establecidas, siguiendo los procedimientos y normas que BANCO ACTIVO señale a tales fines. Igualmente BANCO ACTIVO podrá limitar, restringir o suprimir en forma general o particular el acceso de EL CLIENTE a determinadas funciones u opciones, cuando las operaciones que pretenda realizar EL CLIENTE a través del servicio no se ajusten o contravengan en cualquier forma lo establecido en el presente documento, en las normas y procedimientos internos de BANCO ACTIVO, o en cualesquiera otras disposiciones contractuales o legales. 

<U>NOVENA:</U> EL CLIENTE se obliga al correcto uso del servicio ACTIVO EN LÍNEA EMPRESA y de todas las TRANSACCIONES FINANCIERAS y TRANSACCIONES NO FINANCIERAS que se ejecuten a través del mismo, así como de cualquier otra operación vinculada directa o indirectamente con la administración del servicio, siguiendo todas las instrucciones contenidas en el mismo y asumiendo toda la responsabilidad por cualquier circunstancia derivada del uso o manejo incorrecto o inadecuado del servicio. 

<U>DÉCIMA:</U> Es entendido que el servicio ACTIVO EN LÍNEA EMPRESA, es una herramienta que facilita a EL CLIENTE la información y manejo de los productos financieros que mantiene con BANCO ACTIVO, permitiendo la realización de TRANSACCIONES FINANCIERAS y TRANSACCIONES NO FINANCIERAS disponibles en el servicio para hacerlas de forma sencilla, ágil y segura. 

<U>DECIMA PRIMERA:</U> BANCO ACTIVO queda exento de responsabilidad en el caso de que no sean procesadas cualquier TRANSACCION FINANCIERA y/o TRANSACCION NO FINANCIERA efectuadas a través del servicio ACTIVO EN LÍNEA EMPRESA, si las mismas no se realizan o envían de conformidad con lo establecido en las instrucciones contenidas en el propio servicio, en el presente documento, en las normas y procedimientos internos de BANCO ACTIVO aplicables al servicio objeto de este documento, y en cualesquiera otras regulaciones que fueren aplicables. BANCO ACTIVO no se hace responsable por la interrupción imprevista del servicio ACTIVO EN LÍNEA EMPRESA, por dificultades en su funcionamiento, o por retardo en el tiempo de procesamiento de cualesquiera operaciones o consultas realizadas o instrucciones, solicitudes y comunicaciones de cualquier género causadas por fallas en el servicio eléctrico, en el servicio telefónico o de cualquier otra naturaleza, congestionamiento en la Internet, demora en el procesamiento de las operaciones por parte de los sistemas de otros bancos o por cualesquiera otras causas ajenas a la voluntad de BANCO ACTIVO, por hechos fortuitos, de fuerza mayor o de la naturaleza, recalcando que BANCO ACTIVO, hará todas las gestiones que estén a su alcance para garantizar el buen funcionamiento del servicio, para ello cuenta con todos los sistemas requeridos para tal fin. 

<U>DÉCIMA SEGUNDA:</U> En caso de que EL CLIENTE presentare algún reclamo o solicitud de aclaratoria del servicio ACTIVO EN LÍNEA EMPRESA, será atendido por los siguientes canales de servicios: El servicio de atención telefónica, a través del número 0500-ACTIVAT, esto es, 0-5002284828; Las Oficinas Comerciales y del portal de BANCO ACTIVO (www.bancoactivo.com), tomando el requerimiento por el sistema de reclamos implementado por BANCO ACTIVO, generando un número de reclamo o solicitud que debe ser procesado por las áreas involucradas y garantizando una respuesta EL CLIENTE, en un lapso que no excederá de veinte (20) días continuos, siguientes a la formulación del reclamo o consulta. 

<U>DÉCIMA TERCERA:</U> BANCO ACTIVO se reserva el derecho de ampliar, restringir o suspender temporalmente el Servicio ACTIVO EN LÍNEA EMPRESA en cualquier momento, con la participación oportuna a EL CLIENTE, por razones de orden técnico, como mantenimiento, o por cualquier otro motivo que a juicio de BANCO ACTIVO haga necesaria o conveniente dicha suspensión temporal, salvo por causa fortuita o de fuerza mayor. 

<U>DÉCIMA CUARTA:</U> Las tarifas y/o comisiones que deberá pagar EL CLIENTE como remuneración por la prestación del servicio ACTIVO EN LÍNEA EMPRESA, o por cada una de las operaciones que se realicen a través de este, así como la forma y oportunidad del pago y las demás obligaciones conexas y derivadas de las anteriores, se encuentran en el Tarifario de BANCO ACTIVO, de acuerdo con las regulaciones definidas por el Banco Central de Venezuela y podrán ser conocidas por EL CLIENTE a través de información disponibles en las Oficinas de Atención al Público de BANCO ACTIVO, en el propio servicio o por cualquier otro medio que BANCO ACTIVO determinase a tal fin, o de acuerdo a la normativa legal que rija la materia. 

<U>Parágrafo único:</U> EL CLIENTE se obliga a pagar a BANCO ACTIVO la remuneración que éste estableciere según lo antes expresado,  autorizando expresamente a BANCO ACTIVO a cargar en cualquier  cuenta o depósito que mantuviere en dicha Institución Financiera,  sean a la vista, a plazo, de ahorro o de inversión; el débito o cargo de las cantidades correspondientes al pago de las comisiones de BANCO ACTIVO asociadas a las transacciones del  servicio ACTIVO EN LÍNEA EMPRESA, así como cualquier otro gasto derivado del mismo. EL CLIENTE, podrá en cualquier momento revocar la autorización a que se hace referencia, notificándolo por escrito a BANCO ACTIVO, con treinta (30) días de anticipación. 

<U>DÉCIMA QUINTA:</U> Las 	TRANSACCIONES 	FINANCIERAS y TRANSACCIONES 	NO FINANCIERAS efectuadas a través del servicio ACTIVO EN LÍNEA EMPRESA, serán procesadas y ejecutadas según la naturaleza de cada una de ellas, en los plazos que estableciere BANCO ACTIVO a tales fines. En tal sentido, no serán procesadas las transacciones que excedan los límites establecidos por EL CLIENTE y por BANCO ACTIVO o que excedan del saldo disponible de la cuenta de EL CLIENTE.

<U>DÉCIMA SEXTA:</U> La transmisión de datos por medio del servicio ACTIVO EN LÍNEA EMPRESA está debidamente codificada mediante la tecnología de encriptación, para suministrar al servicio la mayor seguridad posible, así como a las consultas, operaciones y transacciones que por su intermedio se realizaren y a las solicitudes e instrucciones que se enviaren. EL SERVICIO ACTIVO EN LÍNEA EMPRESA Y EL USO DEL MISMO SON OPCIONALES PARA EL CLIENTE Y EN MODO ALGUNO OBLIGATORIOS O NECESARIOS. BANCO ACTIVO, GARANTIZA LA PRIVACIDAD Y LA CONFIDENCIALIDAD DE LOS DATOS E INFORMACIÓN IMPLICADA EN LAS TRANSACCIONES REALIZADAS, DE FORMA TAL QUE LA INFORMACIÓN INTERCAMBIADA NO SEA ACCESIBLE A TERCEROS NO AUTORIZADOS, DE ACUERDO A LO ESTABLECIDO EN LAS LEYES QUE RIGEN LA MATERIA. BANCO ACTIVO, NO SERÁ RESPONSABLE POR LOS DAÑOS OCASIONADOS A LOS EQUIPOS O PROGRAMAS POR LA POSIBLE CONTAMINACIÓN DE VIRUS INFORMÁTICOS, A LOS CUALES BANCO ACTIVO NO TENGA ACCESO. 

<U>DECIMA SEPTIMA:</U> EL CLIENTE asume cualquier responsabilidad que pudiera derivarse del uso del servicio ACTIVO EN LÍNEA EMPRESA, así como cualesquiera daños que pudiesen originarse, incluso a BANCO ACTIVO, o a terceros, bien sea por la divulgación de cualquiera informaciones de carácter confidencial obtenidas a través del uso del servicio ACTIVO EN LÍNEA EMPRESA o por cualquier otro motivo. 

<U>DÉCIMA OCTAVA:</U> EL CLIENTE, acepta y declara que la FIRMA ELECTRONICA puede emplearse  como  un equivalente funcional de las firmas  manuscritas y de otros mecanismos de autenticación empleados en soporte de papel que sirvan, entre otras finalidades, para: 

1).-Identificar a EL CLIENTE.
<BR> 
2).-Proporcionar certidumbre en cuanto a la participación de EL CLIENTE en una operación y/o transacción regulada por el presente  contrato.
<BR> 
3).- Vincular a EL CLIENTE con el contenido de un  mensaje de datos. 
<BR>
4).- Constituir Testimonio de la intención de EL CLIENTE de adherirse al presente contrato o alguna modificación del mismo si fuere el caso. 
<BR>
5).- Constituir testimonio de intención  de EL CLIENTE de respaldar el contenido de un texto. 
<BR>
6).- Constituir testimonio  del hecho de que EL CLIENTE estuviere en un lugar determinado en un momento dado. 

<U>DECIMA NOVENA:</U> BANCO ACTIVO procurará que los sistemas y/o plataformas utilizados para el servicio ACTIVO EN LÍNEA EMPRESA cumplan con todos los estándares de calidad y seguridad existentes en el mercado, en lo concerniente a la confidencialidad, autenticidad, integridad y seguridad de la información. 

<U>VIGÉSIMA:</U> BANCO ACTIVO podrá modificar el presente contrato, siempre y cuando tales modificaciones consten por escrito y sean debidamente notificadas a EL CLIENTE mediante publicación en su página web o de forma personal a través de medios electrónicos, mensajes de texto (SMS), dirección de correo electrónico de EL CLIENTE según los datos de números y correo electrónico que aparezca en los registros que tiene BANCO ACTIVO, en un plazo no menor de treinta (30) días continuos de antelación a la entrada en vigencia del nuevo contrato, ello de acuerdo a lo establecido en la Ley que rige la materia. 

<U>Parágrafo único:</U> Las modificaciones efectuadas sobre el contrato, entraran en vigencia a los treinta (30) días continuos siguientes a su notificación, por lo que si EL CLIENTE desea continuar con el servicio ACTIVO EN LÍNEA EMPRESA podrá hacerlo en los nuevos términos y condiciones previstos en el nuevo contrato. Se entenderá que ha aceptado las nuevas condiciones si en el transcurso de los 30 días continuos siguientes a la notificación realiza cualquier operación y/o transacción distinta a la cancelación del servicio. 

<U>VIGÉSIMA PRIMERA:</U> El presente contrato se dará por terminado o rescindido por las siguientes causas:

1.	Cuando BANCO ACTIVO efectuase modificaciones al contrato y EL CLIENTE no aceptase las nuevas condiciones del mismo y no haga uso del servicio, dentro del plazo establecido en el parágrafo único de la cláusula Vigésima de este contrato. 
<BR>
2.	Cuando EL CLIENTE solicite la desafiliación del servicio ACTIVO EN LÍNEA EMPRESA.
<BR>
3.	Cuando cualquier de las partes así lo decidan, previa notificación escrita realizada por cualquiera de ellas a la otra con treinta (30) días de anticipación, por lo menos a la fecha efectiva de la desafiliación del servicio ACTIVO EN LÍNEA EMPRESA. 

<U>VIGÉSIMA SEGUNDA:</U> EL CLIENTE declara expresamente que con ocasión a la aceptación del presente contrato, lo ha leído y comprendido previamente. Igualmente declara que todos los datos suministrados por él a BANCO ACTIVO, son ciertos y en consecuencia podrán ser verificables y comprobables en cualquier momento. Así mismo declara que forman parte integrante del presente contrato, los formularios o planillas emitidos por BANCO ACTIVO aplicables a este servicio.

<U>VIGÉSIMA TERCERA:</U> Para todos los efectos y consecuencias derivadas del presente, las partes eligen como domicilio especial al de EL CLIENTE y convienen en que todas las controversias que surjan con motivo de su ejecución, sean resueltas mediante arbitraje de conformidad con el Reglamento del Centro Empresarial de Conciliación y Arbitraje (CEDCA).

<U>VIGÉSIMA CUARTA:</U> Las presentes Condiciones Generales del servicio ACTIVO EN LÍNEA EMPRESA, sustituye en todas y cada una de sus partes a las contenidas en cualesquiera documentos, condiciones generales o contratos previos que hubiese suscrito BANCO ACTIVO con EL CLIENTE, con el objeto de regular el servicio ACTIVO EN LÍNEA EMPRESA.
</P>
<BR>
<P ALIGN="center">ANEXO A</P>
<BR>
<P ALIGN="center">SERVICIO PAGO DE NOMINA</P>

<P ALIGN="justify">
<div class = "mensaje">
     El presente anexo tiene como finalidad primordial establecer los términos y demás condiciones que regulan el SERVICIO PAGO DE NOMINA que BANCO ACTIVO ofrecerá a EL CLIENTE, a objeto de que este último realice de manera oportuna los pagos de nómina a sus TRABAJADORES. 

<U>PRIMERA:</U> A los fines de este documento se establecen las siguientes definiciones, algunas de las cuales podrán ser presentadas indistintamente en singular o plural según corresponda en relación con el texto en el cual aparezcan: 

1.	LAS PARTES: BANCO ACTIVO y EL CLIENTE.
<BR>
2.	SERVICIO PAGO DE NOMINA: Es un SERVICIO ESPECIALIZADO que ofrece BANCO ACTIVO a EL CLIENTE para efectuar la gestión automática de un conjunto de AFILIACIONES DE BENEFICIARIOS DE NOMINA y ÓRDENES DE PAGOS DE NOMINA, a través de los medios de transmisión que a tal efecto BANCO ACTIVO disponga para tal fin.
<BR> 
3.	AFILIACION DE BENEFICIARIO DE NOMINA: Es una instrucción de afiliación de un TRABAJADOR emitida por EL CLIENTE a BANCO ACTIVO, con la finalidad de que este último realice en una determinada fecha, el registro de la identificación, nombre, datos de contacto y cuenta bancaria donde le serán acreditadas las cantidades de dinero indicadas en las ORDENES DE PAGOS DE NOMINA.
<BR>
4.	ORDEN DE PAGO DE NOMINA: Es una instrucción de pago de nómina emitida por EL CLIENTE a BANCO ACTIVO, con la finalidad de que este último realice en una determinada FECHA VALOR, un debito en LA CUENTA que EL CLIENTE expresamente indique en cada oportunidad, las cantidades de dinero señaladas por EL CLIENTE y las acredite en la cuenta bancaria que su TRABAJADOR mantiene abierta en BANCO ACTIVO, o incluso, las transfiera a un BANCO RECEPTOR para que este último las acredite en la cuenta bancaria que el TRABAJADOR mantiene abierta en el BANCO RECEPTOR.
<BR>
5.	LA CUENTA: es la cuenta bancaria que EL CLIENTE mantiene en BANCO ACTIVO, de la cual expresamente autoriza para debitar las cantidades de dinero que serán acreditas en las cuentas bancarias de sus TRABAJADORES, así como las distintas comisiones y recargos previstos en el tarifario de BANCO ACTIVO.
<BR> 
6.	FECHA VALOR: Es el día hábil bancario previsto para la materialización de los distintos pagos de nómina que EL CLIENTE deba efectuar a sus TRABAJADORES.
<BR>
7.	BANCO RECEPTOR: Es una Institución Financiera perteneciente al Sistema Financiero Venezolano, distinta a BANCO ACTIVO, receptor de las transferencias de fondos enviadas por BANCO ACTIVO que debe acreditar en las cuentas bancarias que los TRABAJADORES mantienen en esa Institución Financiera.
<BR>
8.	TRABAJADOR: Es una persona natural que trabaja para EL CLIENTE y en consecuencia el beneficiario de una ORDEN DE PAGO DE NOMINA.
<BR> 
9.	ARCHIVO DE BENEFICIARIOS DE NOMINA: Es la información inteligible almacenada en formato electrónico o similar que contiene una o varias AFILIACIONES DE BENEFICIARIOS DE NOMINA emitidas por EL CLIENTE.
<BR>
10.	ARCHIVO DE PAGOS DE NOMINA: Es la información inteligible almacenada en formato electrónico o similar que contiene las ORDENES DE PAGOS DE NOMINA emitidas por EL CLIENTE.
<BR>
11.	NOTIFICACIÓN: Significa la información enviada por BANCO ACTIVO a EL CLIENTE a través del servicio ACTIVO EN LINEA EMPRESAS indicando que unas determinadas AFILIACIONES DE BENEFICIARIOS DE NOMINA u ORDENES DE PAGOS DE NOMINA han sido recibidas o ejecutadas.
<BR> 
12.	HORA DE CIERRE: Significa la hora límite establecida por BANCO ACTIVO para ejecutar las ORDENES DE PAGOS DE NOMINA. 

<U>SEGUNDO:</U> Queda a completa protestad de EL CLIENTE, la utilización o no del presente servicio. Entendiéndose, que el uso del SERVICIO PAGO DE NOMINA por parte de EL CLIENTE, conlleva la aceptación de las condiciones establecidas en el presente anexo. 

<U>TERCERO:</U> El ARCHIVO DE BENEFICIARIOS DE NOMINA y el ARCHIVO DE PAGOS DE NOMINA a ser enviado por EL CLIENTE a BANCO ACTIVO, deben estar elaborados bajo especificaciones establecidas por BANCO ACTIVO, para que le permita a éste la precisa identificación de las AFILIACIONES DE BENEFICIARIOS DE NOMINA y ÓRDENES DE PAGOS DE 	NOMINA BANCO ACTIVO no se responsabiliza por los errores en los datos contenidos en los archivos. EL CLIENTE garantiza a BANCO ACTIVO, la veracidad de la información suministrada en el ARCHIVO DE BENEFICIARIOS DE NOMINA y el ARCHIVO DE PAGOS DE NOMINA, y se compromete a indemnizar a BANCO ACTIVO por los daños y perjuicios que pueda causarle por la falta de veracidad de la misma. Los archivos antes indicados, se entenderán como recibido por BANCO ACTIVO una vez el servicio ACTIVO EN LINEA EMPRESAS emita el correspondiente mensaje de NOTIFICACION. 

<U>CUARTO:</U> LAS PARTES acuerdan que el medio de transmisión que se utilizará para el envío del ARCHIVO DE BENEFICIARIOS DE NOMINA, el ARCHIVO DE PAGOS DE NOMINA y la recepción de los resultados de las gestiones realizadas por BANCO ACTIVO es el servicio ACTIVO EN LINEA EMPRESAS. En caso de contingencia, previo acuerdo entre LAS PARTES, EL CLIENTE podrá enviar el ARCHIVO DE BENEFICIARIOS DE NOMINA y el ARCHIVO DE PAGOS DE NOMINA a BANCO ACTIVO mediante los mecanismos que a tal efecto este último disponga para estos casos, los cuales le serán informados a EL CLIENTE en su debida oportunidad. BANCO ACTIVO siempre colocará a disposición de EL CLIENTE los resultados de las gestiones realizadas en el servicio ACTIVO EN LINEA EMPRESAS.

<U>QUINTO:</U> EL CLIENTE conviene expresamente que enviará a BANCO ACTIVO el ARCHIVO DE PAGOS DE NOMINA con por los menos un (1) día hábil bancario de anticipación a la FECHA VALOR, en el horario establecido por BANCO ACTIVO. EL CLIENTE declara expresamente que libera de cualquier responsabilidad a BANCO ACTIVO si por causas de fuerza mayor o caso fortuito no pudiere procesar las ORDENES DE PAGOS DE NOMINA impartidas el mismo día en que han de ser ejecutadas. 

<U>SEXTA:</U> La firma de este anexo no contiene implícita la apertura u otorgamiento de facilidades de crédito o de financiamiento a EL CLIENTE. Por lo tanto, EL CLIENTE se obliga a mantener en LA CUENTA, los fondos o recursos suficientes disponibles que le permitan a BANCO ACTIVO ejecutar las ORDENES DE PAGOS DE NOMINA en la FECHA VALOR. En consecuencia BANCO ACTIVO no asumirá ningún tipo de responsabilidad ni frente a EL CLIENTE ni ante sus TRABAJADORES si en la FECHA VALOR no existieren cantidades de dinero suficientes y disponibles que le permitan efectuar las gestiones pertinentes.

<U>SEPTIMA:</U> BANCO ACTIVO por la prestación del SERVICIO PAGO DE NOMINA a través del servicio ACTIVO EN LINEA EMPRESA cobrará a EL CLIENTE las comisiones y recargos previstos en el tarifario, las cuales EL CLIENTE declara conocer. Las comisiones y recargos han sido fijadas conforme a lo establecido por el BANCO CENTRAL DE VENEZUELA (BCV). EL CLIENTE autoriza expresamente a BANCO ACTIVO, para cargar en cualquiera de las cuentas abiertas en BANCO ACTIVO, los montos correspondientes a tales comisiones y recargos. BANCO ACTIVO podrá modificar el tarifario con arreglo a lo establecido en las disposiciones legales vigentes para esa fecha, a cuyos fines deberá informar a EL CLIENTE mediante la publicación del mismo.

<U>OCTAVA:</U> EL CLIENTE se compromete a informar a sus TRABAJADORES, que la cuenta en la que recibirán el abono por concepto de pago de nómina, debe ser unipersonal, es decir, debe tener un solo titular, sin autorizado o cotitulares. El CLIENTE utilizará el SERVICIO PAGO DE NOMINA exclusivamente para efectuar a sus TRABAJADORES los pagos de los conceptos que en virtud del carácter que les corresponda a los mismos. Por consiguiente, queda prohibido y será causa suficiente para dar por terminada la relación contractual, la realización por medio de dicho servicio, el pago a proveedores, contratistas o terceros. 

<U>NOVENA:</U> BANCO ACTIVO no asume ningún tipo de responsabilidad ni frente a EL CLIENTE ni ante sus TRABAJADORES; en consecuencia BANCO ACTIVO no estará obligado ni sujeto al pago de indemnización alguna por concepto de los daños y perjuicios que eventualmente se deriven de:

1.	La no ejecución de las ORDENES DE PAGOS DE NOMINA imprecisas, contradictorias o incompletas que le hubiere dirigido EL CLIENTE o de aquellas que no estén ajustadas a las especificaciones de BANCO ACTIVO, según los parámetros establecidos en el servicio ACTIVO EN LINEA EMPRESAS.
<BR> 
2.	El incumplimiento parcial o total de las obligaciones que se encuentren a cargo de EL CLIENTE en virtud del contrato del servicio ACTIVO EN LINEA EMPRESAS y del presente anexo, cuando tal incumplimiento obedezca a la ocurrencia de cualquier caso fortuito, razones de fuerza mayor o a cualquier otra causa extraña no imputable a BANCO ACTIVO. Se consideran razones de fuerza mayor a las perturbaciones civiles, terremotos, inundaciones, incendios, guerras, acciones de gobierno por parte de las autoridades civiles o militares, motines, huelgas, hechos de la naturaleza u otros eventos, en el entendido que la anterior mención tiene fines eminentemente enunciativos y de modo alguno limitativo o taxativo. 
<BR>
3.	El incumplimiento de las obligaciones que se encuentren a cargo de BANCO ACTIVO cuando el mismo se derive o fuere consecuencia del incumplimiento de las obligaciones contraídas por EL CLIENTE al contrato del servicio ACTIVO EN LINEA EMPRESAS y el presente anexo.
<BR> 
4.	Fallas producidas en los equipos de computación de BANCO ACTIVO, en los sistemas o plataformas electrónicas o en los medios de trasmisión de la data suministra por terceros o en el suministro de energía eléctrica. 
<BR>
5.	Además es expresamente entendido que BANCO ACTIVO no asume ningún tipo de responsabilidad por el contenido o alcance de las AFILIACIONES DE BENEFICIARIOS DE NOMINA y ÓRDENES DE PAGOS DE NOMINA que reciba de EL CLIENTE ya que la formulación de las mismas constituye responsabilidad exclusiva y excluyente de EL CLIENTE, al igual que las eventuales consecuencias que se deriven de créditos efectuados a cuentas bancarias de personas que no fueren TRABAJADORES o por créditos por montos superiores o inferiores a los que les pudieran corresponder. 
<BR>
6.	Si el abono efectuado en cuentas abiertas en otras instituciones bancarias, no fuesen procesadas por el BANCO RECEPTOR.
<BR> 
7.	Por los daños ocasionados a EL CLIENTE por parte de terceros que a través del uso de sistemas computarizados y conocimientos tecnológicos, decodifiquen o alteren la base de datos contenida en el ARCHIVO DE BENEFICIARIOS DE NOMINA y el ARCHIVO DE PAGOS DE NOMINA o accedan ilegalmente al sistema. 
<BR>
8.	Por los daños ocasionados a los equipos de EL CLIENTE por la posible contaminación de virus informáticos.
<BR> 
9.	Por las consecuencias que puedan derivarse del conocimiento y eventual uso de la CLAVE DE ACCESO por parte de personas diferentes a EL CLIENTE o USUARIO.
<BR> 
10.	EL CLIENTE reconoce y acepta que es y será el único responsable por la veracidad, idoneidad y contenido de las ORDEN DE PAGO DE NOMINA que le imparta a BANCO ACTIVO. En consecuencia, cualquier acto, falta descuido, error u omisión que esté relacionado con la calidad, oportunidad y suficiencia de las órdenes de pago impartidas por EL CLIENTE a BANCO ACTIVO será imputable a EL CLIENTE, en el entendido que bajo ningún supuesto BANCO ACTIVO tendrá la responsabilidad de calificar la pertinencia o legitimidad de las mismas. Asimismo, EL CLIENTE reconoce y acepta que la responsabilidad de BANCO ACTIVO, estará limitada única y exclusivamente a ejecutar en la FECHA VALOR las ORDEN DE PAGO DE NOMINA que le hubiere impartido EL CLIENTE; por consiguiente, BANCO ACTIVO no asume responsabilidad alguna ni frente a EL CLIENTE ni frente a sus TRABAJADORES, si el BANCO RECEPTOR no acreditare en la FECHA VALOR o en una fecha posterior a ella, en las cuentas bancarias de dichos TRABAJADORES, las cantidades de dinero que le hubieren sido transferidas a favor o en beneficio de aquellos.
<BR> 
11.	Por la suspensión del servicio o desafiliación de EL CLIENTE.
<BR> 
12.	EL CLIENTE debe reconocer y reintegrar a BANCO ACTIVO, por cuenta de sus TRABAJADORES, cualquier importe que a consecuencia de una falla del sistema o cualquier otra circunstancia, debidamente comprobada, se evidencie que se le ha realizado un abono indebido por error a los TRABAJADORES de EL CLIENTE, al compararlo con lo indicado en el ARCHIVO DE BENEFICIARIOS DE NOMINA y el ARCHIVO DE PAGOS DE NOMINA correspondiente a la FECHA VALOR aplicada, y en el cual se han agotado las gestiones pertinentes ante el o los TRABAJADORES para obtener dicho reintegro, sin que el mismo lo haya efectuado en el lapso de quince (15) días hábiles contados desde la fecha en la que se le solicitó el reintegro respectivo. 

<U>DECIMA:</U> Cualquiera de las partes se reserva el derecho de terminar la relación contractual, previo aviso entre las partes, con por los menos treinta (30) días de anticipación. La terminación de la relación contractual no dará lugar al pago de indemnización alguna por parte de cualquiera de las partes. 

<U>DECIMO PRIMERA:</U> BANCO ACTIVO podrá modificar el presente anexo, siempre y cuando tales modificaciones consten por escrito y sean debidamente notificadas a EL CLIENTE mediante publicación en su página web o de forma personal a través de cualquiera de estos medios: medios electrónicos, mensajes de texto (SMS), dirección de correo electrónico de EL CLIENTE según los datos de números y correo electrónico que aparezca en los registros que tiene BANCO ACTIVO, en un plazo no menor de treinta (30) días continuos de antelación a la entrada en vigencia del nuevo contrato, ello de acuerdo a lo establecido en la Ley que rige la materia.

<U>Parágrafo único:</U> Las modificaciones efectuadas sobre el anexo, entraran en vigencia a los treinta (30) días continuos siguientes a su notificación, por lo que si EL CLIENTE desea continuar con el SERVICIO PAGO DE NOMINA, podrá hacerlo en los nuevos términos y condiciones previstos en el nuevo anexo. Se entenderá que ha aceptado las nuevas condiciones si en el transcurso de los 30 días continuos siguientes a la notificación realiza cualquier operación y/o transacción distinta a la cancelación del servicio. 

<U>DECIMO SEGUNDA:</U> EL CLIENTE declara expresamente que con ocasión a la aceptación del presente anexo, lo ha leído y comprendido previamente. Igualmente declara que todos los datos suministrados por él a BANCO ACTIVO, son ciertos y en consecuencia podrán ser verificables y comprobables en cualquier momento. Así mismo declara que forman parte integrante del presente anexo, los formularios o planillas emitidos por BANCO ACTIVO aplicables a este servicio. 

<U>DECIMO TERCERO:</U> Para todos los efectos y consecuencias derivadas del presente anexo, las partes eligen como domicilio especial al de EL CLIENTE y convienen en que todas las controversias que surjan con motivo de la ejecución del mismo, sean resueltas mediante arbitraje de conformidad con el Reglamento del Centro Empresarial de Conciliación y Arbitraje (CEDCA).   Aceptado por las partes a la fecha de su suscripción, que constará en los registros que a tales efectos lleva BANCO ACTIVO. 
</div>
</P>
<P ALIGN="center">ANEXO B</p>
<BR>
<P ALIGN="center">SERVICIO PAGO A PROVEEDORES</P>

<P ALIGN="justify">

<div class = "mensaje">

 El presente anexo tiene como finalidad primordial establecer los términos y demás condiciones que regulan el SERVICIO PAGO A PROVEEDORES que BANCO ACTIVO ofrecerá a EL CLIENTE. 
<BR>
<U>PRIMERA:</U> A los fines de este documento se establecen las siguientes definiciones, algunas de las cuales podrán ser presentadas indistintamente en singular o plural según corresponda en relación con el texto en el cual aparezcan: 

1.	LAS PARTES: BANCO ACTIVO y EL CLIENTE.
<BR>
2.	SERVICIO PAGO A PROVEEDORES: Es un servicio a través del cual EL CLIENTE imparte a BANCO ACTIVO, un conjunto de ÓRDENES DE PAGOS, con la finalidad de que BANCO ACTIVO debite de la cuenta bancaria que EL CLIENTE expresamente indique en lo adelante identificada como LA CUENTA, las cantidades de dinero por EL CLIENTE señaladas y las acredite en las cuentas bancarias de sus PROVEEDORES abiertas en BANCO ACTIVO o incluso, las transfiera a otras instituciones.
<BR>
3.	AFILIACION DE BENEFICIARIO DE PAGO: Es una instrucción de afiliación de un PROVEEDOR emitida por EL CLIENTE a BANCO ACTIVO, con la finalidad de que este último realice en una determinada fecha, el registro de la identificación, nombre, datos de contacto y cuenta bancaria donde le serán acreditadas las cantidades de dinero indicadas en las ORDENES DE PAGOS A PROVEEDORES.
<BR>
4.	ORDEN DE PAGO A PROVEEDORES: Es una instrucción de pago a proveedores emitida por EL CLIENTE a BANCO ACTIVO, con la finalidad de que este último realice en una determinada FECHA VALOR, un debito en LA CUENTA que EL CLIENTE expresamente indique en cada oportunidad, las cantidades de dinero señaladas por EL CLIENTE y las que acredite en la cuenta bancaria que su PROVEEDOR mantiene abierta en BANCO ACTIVO, o incluso, las transfiera a un BANCO RECEPTOR para que este último las acredite en la cuenta bancaria que el PROVEEDOR mantiene abierta en el BANCO RECEPTOR.
<BR>
5.	LA CUENTA: es la cuenta bancaria que EL CLIENTE mantiene en BANCO ACTIVO, de la cual expresamente autoriza para debitar las cantidades de dinero que serán acreditas en las cuentas bancarias de sus PROVEEDORES, así como las distintas comisiones y recargos previstos en el tarifario de BANCO ACTIVO.
<BR>
6.	FECHA VALOR: Es el día hábil bancario previsto para la materialización de los distintos pagos a proveedores que EL CLIENTE deba efectuar a sus PROVEEDORES.
<BR>
7.	BANCO RECEPTOR: Es una Institución Financiera perteneciente al Sistema Financiero Venezolano, distinta a BANCO ACTIVO, receptor de las transferencias de fondos enviadas por BANCO ACTIVO que debe acreditar en las cuentas bancarias que los PROVEEDORES mantienen en esa Institución Financiera.
<BR>
8.	PROVEEDORES: Es una persona natural o jurídica distinta a BANCO ACTIVO, que le presta servicios a EL CLIENTE y en consecuencia el beneficiario de una ORDEN DE PAGO A PROVEEDORES.
<BR>
9.	ARCHIVO DE BENEFICIARIOS DE PAGO: Es la información inteligible almacenada en formato electrónico o similar que contiene una o varias AFILIACIONES DE BENEFICIARIOS DE PAGO emitidas por EL CLIENTE.
<BR> 
10.	ARCHIVO DE PAGOS A PROVEEDORES: Es la información inteligible almacenada en formato electrónico o similar que contiene las ORDENES DE PAGOS A PROVEEDORES emitidas por EL CLIENTE.
<BR>
11.	NOTIFICACIÓN: Significa la información enviada por BANCO ACTIVO a EL CLIENTE a través del servicio ACTIVO EN LINEA EMPRESAS indicando que unas determinadas AFILIACIONES DE BENEFICIARIOS DE PAGO u ORDENES DE PAGOS A PROVEEDORES han sido recibidas o ejecutadas.
<BR> 
12.	HORA DE CIERRE: Significa la hora límite establecida por BANCO ACTIVO para ejecutar las ORDENES DE PAGOS A PROVEEDORES. 

<U>SEGUNDA:</U> Queda a completa protestad de EL CLIENTE, la utilización o no del presente servicio. Entendiéndose, que el uso del SERVICIO PAGO A PROVEEDORES por parte de EL CLIENTE, conlleva la aceptación de las condiciones establecidas en el presente anexo. 

<U>TERCERA:</U> EL ARCHIVO DE BENEFICIARIOS DE PAGO y el ARCHIVO DE PAGOS A PROVEEDORES a ser enviado por EL CLIENTE a BANCO ACTIVO, deben estar elaborados bajo especificaciones establecidas por BANCO ACTIVO, para que le permita a éste la precisa identificación de las AFILIACIONES DE BENEFICIARIOS DE PAGO y ÓRDENES DE PAGOS A PROVEEDORES. BANCO ACTIVO no se responsabiliza por los errores en los datos contenidos en los archivos. EL CLIENTE garantiza a BANCO ACTIVO, la veracidad de la información suministrada en el ARCHIVO DE BENEFICIARIOS DE PAGO y el ARCHIVO DE PAGOS A PROVEEDORES, y se compromete a indemnizar a BANCO ACTIVO por los daños y perjuicios que pueda causarle por la falta de veracidad de la misma. Los archivos antes indicados, se entenderán como recibido por BANCO ACTIVO una vez el servicio ACTIVO EN LINEA EMPRESAS emita el correspondiente mensaje de NOTIFICACION. 

<U>CUARTA:</U> LAS PARTES acuerdan que el medio de transmisión que se utilizará para el envío del ARCHIVO DE BENEFICIARIOS DE PAGO y el ARCHIVO DE PAGOS A PROVEEDORES y la recepción de los resultados de las gestiones realizadas por BANCO ACTIVO es el servicio ACTIVO EN LINEA EMPRESAS. En caso de contingencia, previo acuerdo entre LAS PARTES, EL CLIENTE podrá enviar el ARCHIVO DE BENEFICIARIOS DE PAGO y el ARCHIVO DE PAGOS A PROVEEDORES a BANCO ACTIVO mediante los mecanismos que a tal efecto este último disponga para estos casos, los cuales le serán informados a EL CLIENTE en su debida oportunidad. BANCO ACTIVO siempre colocará a disposición de EL CLIENTE los resultados de las gestiones realizadas en el servicio ACTIVO EN LINEA EMPRESAS. 

<U>QUINTA:</U> EL CLIENTE conviene expresamente que enviará a BANCO ACTIVO el ARCHIVO DE PAGOS A PROVEEDORES con por los menos un (1) día hábil bancario de anticipación a la FECHA VALOR, en el horario establecido por BANCO ACTIVO. EL CLIENTE declara expresamente que libera de cualquier responsabilidad a BANCO ACTIVO si por causas de fuerza mayor o caso fortuito no pudiere procesar las ORDENES DE PAGOS A PROVEEDORES impartidas el mismo día en que han de ser ejecutadas. 

<U>SEXTA:</U> La firma de este anexo no contiene implícita la apertura u otorgamiento de facilidades de crédito o de financiamiento a EL CLIENTE. Por lo tanto, EL CLIENTE se obliga a mantener en LA CUENTA, los fondos o recursos suficientes disponibles que le permitan a BANCO ACTIVO ejecutar las ORDENES DE PAGOS A PROVEEDORES en la FECHA VALOR. En consecuencia BANCO ACTIVO no asumirá ningún tipo de responsabilidad ni frente a EL CLIENTE ni ante sus PROVEEDORES si en la FECHA VALOR no existieren cantidades de dinero suficientes y disponibles que le permitan efectuar las gestiones pertinentes.

<U>SEPTIMA:</U> BANCO ACTIVO por la prestación del SERVICIO PAGO A PROVEEDORES a través del servicio ACTIVO EN LINEA EMPRESA cobrará a EL CLIENTE las comisiones y recargos previstos en el tarifario, las cuales EL CLIENTE declara conocer. Las comisiones y recargos han sido fijadas conforme a lo establecido por el BANCO CENTRAL DE VENEZUELA (BCV). EL CLIENTE autoriza expresamente a BANCO ACTIVO, para cargar en cualquiera de las cuentas abiertas en BANCO ACTIVO, los montos correspondientes a tales comisiones y recargos. BANCO ACTIVO podrá modificar el tarifario con arreglo a lo establecido en las disposiciones legales vigentes para esa fecha, a cuyos fines deberá informar a EL CLIENTE mediante la publicación del mismo.

<U>OCTAVA:</U> EL CLIENTE se compromete a informar a sus PROVEEDORES, que la cuenta en la que recibirán el abono por concepto de pago de nómina, debe ser unipersonal, es decir, debe tener un solo titular, sin autorizado o cotitulares. El CLIENTE utilizará el SERVICIO PAGO A PROVEEDORES exclusivamente para efectuar a sus PROVEEDORES los pagos de los conceptos que en virtud del carácter que les corresponda a los mismos. Por consiguiente, queda prohibido y será causa suficiente para dar por terminada la relación contractual, la realización por medio de dicho servicio, otros pagos a terceros o a trabajadores. 

<U>NOVENO:</U> BANCO ACTIVO no asume ningún tipo de responsabilidad ni frente a EL CLIENTE ni ante sus PROVEEDORES; en consecuencia BANCO ACTIVO no estará obligado ni sujeto al pago de indemnización alguna por concepto de los daños y perjuicios que eventualmente se deriven de: 

1.	La no ejecución de las ORDENES DE PAGOS A PROVEEDORES imprecisas, contradictorias o incompletas que le hubiere dirigido EL CLIENTE o de aquellas que no estén ajustadas a las especificaciones de BANCO ACTIVO, según los parámetros establecidos en el servicio ACTIVO EN LINEA EMPRESAS. 
<BR>
2.	El incumplimiento parcial o total de las obligaciones que se encuentren a cargo de EL CLIENTE en virtud del contrato del servicio ACTIVO EN LINEA EMPRESAS y del presente anexo, cuando tal incumplimiento obedezca a la ocurrencia de cualquier caso fortuito, razones de fuerza mayor o a cualquier otra causa extraña no imputable a BANCO ACTIVO. Se consideran razones de fuerza mayor a las perturbaciones civiles, terremotos, inundaciones, incendios, guerras, acciones de gobierno por parte de las autoridades civiles o militares, motines, huelgas, hechos de la naturaleza u otros eventos, en el entendido que la anterior mención tiene fines eminentemente enunciativos y de modo alguno limitativo o taxativo. 
<BR>
3.	El incumplimiento de las obligaciones que se encuentren a cargo de BANCO ACTIVO cuando el mismo se derive o fuere consecuencia del incumplimiento de las obligaciones contraídas por EL CLIENTE al contrato del servicio ACTIVO EN LINEA EMPRESAS y el presente anexo.
<BR> 
4.	Fallas producidas en los equipos de computación de BANCO ACTIVO, en los sistemas o plataformas electrónicas o en los medios de transmisión de la data suministra por terceros o en el suministro de energía eléctrica. 
<BR>
5.	Además es expresamente entendido que BANCO ACTIVO no asume ningún tipo de responsabilidad por el contenido o alcance de las AFILIACIONES DE BENEFICIARIOS DE PAGO y ÓRDENES DE PAGOS A PROVEEDORES que reciba de EL CLIENTE ya que la formulación de las mismas constituye responsabilidad exclusiva y excluyente de EL CLIENTE, al igual que las eventuales consecuencias que se deriven de créditos efectuados a cuentas bancarias de personas que no fueren PROVEEDORES o por créditos por montos superiores o inferiores a los que les pudieran corresponder.
<BR> 
6.	Si el abono efectuado en cuentas abiertas en otras instituciones bancarias, no fuesen procesadas por el BANCO RECEPTOR. 
<BR>
7.	Por los daños ocasionados a EL CLIENTE por parte de terceros que a través del uso de sistemas computarizados y conocimientos tecnológicos, decodifiquen o alteren la base de datos contenida en el ARCHIVO DE BENEFICIARIOS DE PAGO y el ARCHIVO DE PAGOS A PROVEEDORES o accedan ilegalmente al sistema. 
<BR>
8.	Por los daños ocasionados a los equipos de EL CLIENTE por la posible contaminación de virus informáticos. 
<BR>
9.	Por las consecuencias que puedan derivarse del conocimiento y eventual uso de la CLAVE DE ACCESO por parte de personas diferentes a EL CLIENTE o USUARIO.
<BR> 
10.	EL CLIENTE reconoce y acepta que es y será el único responsable por la veracidad, idoneidad y contenido de las ORDEN DE PAGO A PROVEEDORES que le imparta a BANCO ACTIVO. En consecuencia, cualquier acto, falta descuido, error u omisión que esté relacionado con la calidad, oportunidad y suficiencia de las órdenes de pago impartidas por EL CLIENTE a BANCO ACTIVO será imputable a EL CLIENTE, en el entendido que bajo ningún supuesto BANCO ACTIVO tendrá la responsabilidad de calificar la pertinencia o legitimidad de las mismas. Asimismo, EL CLIENTE reconoce y acepta que la responsabilidad de BANCO ACTIVO, estará limitada única y exclusivamente a ejecutar en la FECHA VALOR las ORDEN DE PAGO A PROVEEDORES que le hubiere impartido EL CLIENTE; por consiguiente, BANCO ACTIVO no asume responsabilidad alguna ni frente a EL CLIENTE ni frente a sus PROVEEDORES, si el BANCO RECEPTOR no acreditare en la FECHA VALOR o en una fecha posterior a ella, en las cuentas bancarias de dichos PROVEEDORES, las cantidades de dinero que le hubieren sido transferidas a favor o en beneficio de aquellos. 
<BR>
11.	Por la suspensión del servicio o desafiliación de EL CLIENTE. 
<BR>
12. EL CLIENTE debe reconocer y reintegrar a BANCO ACTIVO, por cuenta de sus PROVEEDORES, cualquier importe que a consecuencia de una falla del sistema o cualquier otra circunstancia, debidamente comprobada, se evidencie que se le ha realizado un abono indebido por error a los PROVEEDORES de EL CLIENTE, al compararlo con lo indicado en el ARCHIVO DE BENEFICIARIOS DE PAGO y el ARCHIVO DE PAGOS A PROVEEDORES correspondiente a la FECHA VALOR aplicada. y en el cual se han agotado las gestiones pertinentes ante el o los PROVEEDORES para obtener dicho reintegro, sin que el mismo lo haya efectuado en el lapso de quince (15) días hábiles contados desde la fecha en la que se le solicitó el reintegro respectivo.

<U>DECIMA:</U> Cualquiera de las partes se reserva el derecho de terminar la relación contractual, previo aviso entre las partes, con por los menos treinta (30) días de anticipación. La terminación de la relación contractual no dará lugar al pago de indemnización alguna por parte de cualquiera de las partes. 

<U>DECIMA PRIMERO:</U> BANCO ACTIVO podrá modificar el presente anexo, siempre y cuando tales modificaciones consten por escrito y sean debidamente notificadas a EL CLIENTE mediante publicación en su página web o de forma personal a través de cualquiera de estos medios: medios electrónicos, mensajes de texto (SMS), dirección de correo electrónico de EL CLIENTE según los datos de números y correo electrónico que aparezca en los registros que tiene BANCO ACTIVO, en un plazo no menor de treinta (30) días continuos de antelación a la entrada en vigencia del nuevo contrato, ello de acuerdo a lo establecido en la Ley que rige la materia.

<U>Parágrafo único:</U> Las modificaciones efectuadas sobre el anexo, entraran en vigencia a los treinta (30) días continuos siguientes a su notificación, por lo que si EL CLIENTE desea continuar con el SERVICIO PAGO DE NOMINA PROVEEDORES, podrá hacerlo en los nuevos términos y condiciones previstos en el nuevo anexo. Se entenderá que ha aceptado las nuevas condiciones si en el transcurso de los 30 días continuos siguientes a la notificación realiza cualquier operación y/o transacción distinta a la cancelación del servicio. 

<U>DECIMA SEGUNDA:</U> EL CLIENTE declara expresamente que con ocasión a la aceptación del presente anexo, lo ha leído y comprendido previamente. Igualmente declara que todos los datos suministrados por él a BANCO ACTIVO, son ciertos y en consecuencia podrán ser verificables y comprobables en cualquier momento. Así mismo declara que forman parte integrante del presente anexo, los formularios o planillas emitidos por BANCO ACTIVO aplicables a este servicio.  

<U>DECIMA TERCERA:</U> Para todos los efectos y consecuencias derivadas del presente anexo, las partes eligen como domicilio especial al de EL CLIENTE y convienen en que todas las controversias que surjan con motivo de la ejecución del mismo, sean resueltas mediante arbitraje de conformidad con el Reglamento del Centro Empresarial de Conciliación y Arbitraje (CEDCA).    

 Aceptado por las partes a la fecha de su suscripción, que constará en los registros que a tales efectos lleva BANCO ACTIVO.</P>

<BR>
<P ALIGN="center">ANEXO C - PERSONA JURIDICA</p>
<BR>
<P ALIGN="center">SERVICIO DE PAGO EN LINEA</P>

<P ALIGN="justify">

<div class = "mensaje">
El presente anexo tiene como finalidad primordial establecer los términos y demás condiciones que regulan el SERVICIO DE PAGO EN LINEA que BANCO ACTIVO ofrecerá a EL CLIENTE.   
<U>PRIMERA:</U> A los fines de este documento se establecen las siguientes definiciones, algunas de las cuales podrán ser presentadas indistintamente en singular o plural según corresponda en relación con el texto en el cual aparezcan: 
<BR>
1.	LAS PARTES: BANCO ACTIVO y EL CLIENTE.
<BR>
1.1.	BANCO ACTIVO: Institución financiera denominada Banco Activo, C.A. Banco Universal, suficientemente identificada en el contrato ACTIVO EN LINEA EMPRESAS  y quien tiene suscrito con personas jurídicas de carácter privado y público que incluye las  Instituciones Gubernamentales y demás Entes Públicos, contratos de servicios de recepción de fondos y pago de bienes y servicios, según corresponda.
<BR> 
1.2.	EL CLIENTE: Es toda persona jurídica de carácter público que incluye las Instituciones Gubernamentales y demás Entes Públicos  o privado; que tenga productos y/o servicios financieros en BANCO ACTIVO y que utilice el servicio ACTIVO EN LINEA EMPRESA para realizar operaciones de acuerdo con lo previsto en el presente ANEXO C.
<BR>
2.	EL  BENEFICIARIO: Es la persona jurídica de carácter público que incluye Instituciones Gubernamentales y Entes Públicos,  o privados distinta a BANCO ACTIVO, que le presta servicios a EL CLIENTE, a quien éste último, le adeuda cantidades de dinero por servicios, bienes y/o Impuestos Nacionales y Aduanales; y que  en su calidad de acreedor es el receptor del pago(s) de la(s) cantidad(es) de dinero adeudada(s) por EL CLIENTE.
<BR>
3.	SERVICIO DE PAGO EN LINEA: Es un servicio  que ofrece BANCO ACTIVO a EL CLIENTE, el cual le permite a éste,  el pago a EL BENEFICIARIO a través de  ACTIVO EN LINEA EMPRESAS de bienes y servicios, así como el pago de los distintos Impuestos Nacionales y Aduanales que adeude EL CLIENTE.
<BR> 
4.	LA  CUENTA: Es la cuenta bancaria que EL CLIENTE mantiene en BANCO ACTIVO,  de la cual EL CLIENTE realizará los cargos o débitos de las cantidades de dinero para el pago de los bienes o servicios; así como del pago de los distintos Impuestos Nacionales y Aduanales. 
<BR>
5.	AFILIACIONES DE BENEFICIARIOS DE PAGO: Es el registro que debe realizar EL CLIENTE a través del servicio ACTIVO EN LINEA EMPRESA, contentivo de los datos y demás características necesarias para realizar el pago de las cantidad(es) adeudada(s) por EL CLIENTE a EL BENEFICIARIO. 
<BR>
6.	NOTIFICACIÓN: Es el proceso mediante  el  cual EL  BANCO,  informa a EL CLIENTE, la(s)  afiliación(es) y/o  pago(s), mediante   correo electrónico y/o SMS, del SERVICIO DE PAGO EN LINEA, efectuado a  través  de  ACTIVO EN LINEA EMPRESA. 
<BR>     
7.	HORARIO DE ATENCION: Es el tiempo hábil bancario previsto para la materialización del pago de bienes y servicios, así como para recaudar los distintos Impuestos Nacionales y Aduanales.

<U>SEGUNDA:</U> Queda a completa  potestad de EL CLIENTE, la utilización o no del presente servicio. Entendiéndose, que el uso del SERVICIO DE PAGO EN LINEA por parte de EL CLIENTE, conlleva la aceptación de las condiciones establecidas en el presente ANEXO C. 
<U>TERCERA:</U> La firma de este anexo no contiene implícita la apertura u otorgamiento de facilidades de crédito o de financiamiento a EL CLIENTE. Por lo tanto, EL CLIENTE se obliga a mantener en LA CUENTA, las cantidades de dinero o recursos suficientes disponibles que le permitan ejecutar el pago de bienes y servicios, así como para efectuar el pago de los distintos Impuestos Nacionales y Aduanales. En consecuencia BANCO ACTIVO no asumirá ningún tipo de responsabilidad ni frente a EL CLIENTE ni ante EL BENEFICIARIO. 
<U>CUARTA:</U> BANCO ACTIVO no asume responsabilidad ni frente a EL CLIENTE ni ante  EL BENEFICIARIO, y por ende no estará obligado a efectuar ninguna prestación o a realizar el pago de indemnización alguna, derivadas de los hechos u ocurrencias  que eventualmente se deriven de: 
<BR>
1.	El incumplimiento de las obligaciones que se encuentren a cargo de BANCO ACTIVO cuando el mismo se derive o fuere consecuencia del incumplimiento de las obligaciones contraídas por EL CLIENTE.
<BR> 
2.	Fallas producidas en los equipos de computación de BANCO ACTIVO, en los sistemas o plataformas electrónicas o en los medios de transmisión de la data suministrada por el(los) prestadores de servicio(s).
<BR> 
3.	Además es expresamente entendido que BANCO ACTIVO no asume ningún tipo de responsabilidad por el contenido o alcance de las AFILIACIONES DE BENEFICIARIOS DE PAGO que reciba de EL CLIENTE ya que la formulación de las mismas constituye responsabilidad exclusiva y excluyente de EL CLIENTE, al igual que las eventuales consecuencias que se deriven de créditos efectuados a cuentas bancarias de personas que no fueren EL BENEFICIARIO.
<BR> 
4.	Por los daños ocasionados a EL CLIENTE por parte de terceros que a través del uso de sistemas computarizados y conocimientos tecnológicos, decodifiquen o alteren la base de datos contenida en la(s) afiliaciones de pago y accedan ilegalmente al sistema.
<BR>
5.	Por los daños ocasionados a los equipos de EL CLIENTE por la posible contaminación de virus informáticos. 
<BR>
6.	Por las consecuencias que puedan derivarse del conocimiento y eventual uso de la CLAVE DE ACCESO por parte de personas diferentes a EL CLIENTE.
<BR>
7.	Por la suspensión del servicio o desafiliación de EL CLIENTE del servicio ACTIVO EN LINEA EMPRESAS. 
<BR>
8.	Por no acreditar el pago realizado por EL CLIENTE, cuando los datos proporcionados en la afiliación realizada sean erróneos y/o incompletos.
<BR>
9.	Por las fallas que presenten los sistemas de EL BENEFICIARIO, derivadas de la falta de mantenimiento de los equipos y/o programas y/o plataformas de éste, fallas en el servicio eléctrico o por razones de orden técnico, las derivadas de la falta de actualización de dichos equipos y/o programas y/o plataformas o de cualquier otra causa imputable a EL BENEFICIARIO.
<BR>
10.	Por el no procesamiento de las transacciones electrónicas que contravengan en cualquier forma, lo establecido en el presente CONTRATO, en las Normas y Procedimientos internos de BANCO ACTIVO o en cualquier otra disposición contractual o legal. en particular, no serán procesadas las transacciones electrónicas que: i) excedan los límites de aprobación establecidos por BANCO ACTIVO para dicha operación; II) excedan de los saldos disponibles de las cuentas de EL CLIENTE; III) correspondan a cuentas con restricciones en su movilización; y IV) se requiera el suministro de información por parte del CLIENTE y/o la remisión de documentación adicional. en los supuestos antes mencionados.
<BR>
11.	Por retrasos, deficiencias o imposibilidad en cuanto al procesamiento de las transacciones electrónicas debido a circunstancias que estén fuera del control de BANCO ACTIVO.
<BR>
12.	Por fraudes o uso indebido o irregular del SERVICIO PAGO EN LINEA, en el entendido que EL CLIENTE accederá al servicio a través del USUARIO, CLAVE DE ACCESO, y los otros factores de autenticación, que son de carácter personal, confidencial e intransferible.

<U>PARAGRAFO UNICO:</U> En todo caso, la responsabilidad de cada una de LAS PARTES, estará limitada a la cantidad pagada por EL CLIENTE en relación al servicio.  

<U>QUINTA:</U> BANCO ACTIVO se compromete a poner el SERVICIO DE PAGO EN LINEA a que se refiere el   presente  Anexo,  a disposición de EL CLIENTE de forma continua e ininterrumpida, no obstante por motivo de afectaciones de orden técnico o de mantenimiento, se reserva el derecho de suspender temporalmente la prestación del servicio, en cuyo caso procederá a notificar a EL CLIENTE a través de aviso en su página web, SMS o correo electrónico.

<U>SEXTA:</U> BANCO  ACTIVO notificará a EL BENEFICIARIO, sobre los reclamos efectuados por EL CLIENTE en relación al SERVICIO DE PAGO EN LINEA, correspondientes a errores en el procesamiento de los pagos efectuados.   
Al respecto, para el caso de Reintegros solicitados por EL CLIENTE a BANCO  ACTIVO, éste último notificará de tal circunstancia a EL BENEFICIARIO a los fines de que se pronuncie sobre la procedencia o no del mismo, dentro de los plazos legales para dar respuesta a EL CLIENTE establecidos en la normativa legal dictada por los organismos competentes. En el supuesto que el reclamo se resolviere a favor de EL CLIENTE, BANCO ACTIVO procederá a efectuar el reintegro.

<U>SEPTIMA:</U> EL CLIENTE autoriza expresamente a BANCO ACTIVO a cargar en cualquier  cuenta o depósito que mantuviere en dicha Institución Financiera,  sean a la vista, a plazo, de ahorro o de inversión; el débito o cargo de las cantidades correspondientes al pago de las comisiones de BANCO ACTIVO asociadas a las transacciones del  SERVICIO DE PAGO EN LINEA, así como cualquier otro gasto derivado del mismo. EL CLIENTE, podrá en cualquier momento revocar la autorización a que se hace referencia, notificándolo por escrito a BANCO ACTIVO, con treinta (30) días de anticipación. 

<U>OCTAVA:</U> BANCO ACTIVO podrá modificar el presente anexo, siempre y cuando tales modificaciones consten por escrito y sean debidamente notificadas a EL CLIENTE mediante publicación en su página web o de forma personal a través de medios electrónicos, (SMS), dirección de correo electrónico de EL CLIENTE según los datos de números y correo electrónico  que aparezca en los registros que tiene BANCO ACTIVO. 

<U>PARAGRAFO UNICO:</U> Las modificaciones efectuadas sobre el anexo, entraran en vigencia a los treinta (30) días continuos siguientes a su notificación, por lo que si EL CLIENTE desea continuar con el SERVICIO DE PAGO EN LINEA, podrá hacerlo en los nuevos términos y condiciones previstos en el nuevo anexo. Se entenderá que ha aceptado  las  nuevas condiciones si en el transcurso de los 30 días continuos siguientes  a la notificación  realiza cualquier operación y/o transacción distinta  a la cancelación del servicio.

<U>NOVENA:</U> EL CLIENTE declara que ha leído, analizado y comprendido totalmente el presente documento Anexo C y que forma parte integrante del Contrato Activo en Línea Empresa, por lo que manifiesta expresamente aceptación y conformidad con todos y cada uno de los términos y condiciones del mismo. Igualmente declara que todos los datos suministrados por él a BANCO ACTIVO, son ciertos y en consecuencia podrán ser verificables y comprobables en cualquier momento. 

<U>DECIMA:</U> Para todos los efectos y consecuencias derivadas del presente anexo, las partes eligen como domicilio especial al de EL CLIENTE y convienen en que todas las controversias que surjan con motivo de la ejecución del mismo, sean resueltas mediante arbitraje de conformidad con el Reglamento del Centro Empresarial de Conciliación y Arbitraje (CEDCA).
';

//**************************************************************** TERCERA HOJA
$pdf->AddPage();
$pdf->setMargins(20,20,20);
$pdf->setAutoPageBreak(true,20);

$pdf->setFont('Arial','B',11);
$pdf->cell(0,6,"CONDICIONES GENERALES DEL SERVICIO ACTIVO EN LÍNEA EMPRESA",0,0,'C');
$pdf->ln();
$pdf->ln();

$pdf->setFont('Arial','',9);
$pdf->WriteHTML($contrato);
$pdf->ln();

$pdf->output(date('YmdHis').'.pdf','D');

exit;

?>