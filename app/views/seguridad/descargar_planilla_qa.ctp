<?php
/**
 * Generador de planillas de afiliación de usuarios jurídicos
**/
App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

Class PlanillaQAPDF extends FPDF{

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
		$this->setY(-30);
	
		$this->line(10,$this->getY(),205,$this->getY());

		$this->setFont('Arial','',5);
		$this->cell(0,3,'MCE. FI.0379.V02',0,0,'R');
		$this->setFont('Arial','',7);
		$this->ln();
		$this->cell(0,3,'El uso de este Formulario es de carácter Obligatorio',0,0,'C');
		$this->ln();
		$this->cell(0,3,'para el procedimiento establecido',0,0,'C');
		$this->ln();
		$this->cell(0,3,'Información Propiedad de Banco Activo, C.A. Banco Universal',0,0,'C');
		$this->ln();
		$this->cell(0,3,'Todos los Derechos Reservados.',0,0,'C');
		$this->ln();
		$this->cell(0,3,'',0,0,'C');
		$this->ln();

		$this->line(30,$this->getY(),190,$this->getY());
	}
	
	//Iniciación de variables
	var $B = 0;
	var $I = 0;
	var $U = 0;
	var $HREF = '';


}

$pdf = new PlanillaQAPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

//**************************************************************** PRIMERA HOJA
$pdf->setFont('Arial','B',11);

$pdf->cell(0,6,"SOLICITUD DE ACTIVACIÓN DE USUARIO",0,0,'C');
$pdf->ln();
$pdf->cell(0,6,"POR OLVIDO DE PREGUNTAS Y RESPUESTAS DE DESAFIO",0,0,'C');
$pdf->ln();
$pdf->cell(0,6,"PERSONA JURIDICA",0,0,'C');

$pdf->ln();
$pdf->ln();
$pdf->ln();

$pdf->setFont('Arial','',9);
$pdf->Multicell(0,6,"Yo, ".str_repeat("_", 30)." de nacionalidad ".str_repeat("_", 15).", mayor de edad, de estado civil ".str_repeat("_", 15).", domiciliado en ".str_repeat("_", 35)." y titular de la cédula de identidad número ".str_repeat("_", 15)." , actuando en mi carácter de Representante Legal de la Empresa ".str_repeat("_", 40).", identificada con el RIF No. " . $estado['datasource']['tipo_rif']."-".desencriptar($estado['datasource']['rif']) . ", declaro:",0,'J');
$pdf->ln();

$pdf->SetLeftMargin(20);
$pdf->write(6,'1. Que se ha solicitado a través de la página de Internet Banking de Banco Activo, C.A. Banco Universal,  la recuperación de las Preguntas y Respuestas de Desafío del usuario, quien es la persona responsable del servicio ');
$pdf->setFont('Arial','B',9);
$pdf->write(6,'ACTIVO EN LÍNEA PERSONA JURÍDICA');
$pdf->setFont('Arial','',9);
$pdf->write(6,'. de la empresa que represento. En virtud de ello, se solicita la Activación del Usuario'.str_repeat("_", 15).' por Olvido de Preguntas y Respuestas de Desafío.');
$pdf->ln();
$pdf->ln();

$pdf->cell(0,5,'En '.str_repeat("_", 30).', a los '.str_repeat("_", 5).' días del mes de '.str_repeat("_", 15).' de 201'.str_repeat("_", 5).'.',0,0,'L');

$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->ln();

$pdf->cell(0,5,'Firma(s) del Representante Legal de la Empresa.',0,0,'C');
$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->ln();

$pdf->SetLeftMargin(10);
$pdf->ln();
$pdf->write(5,'Firma verificada por:');
$pdf->ln();
$pdf->write(5,'Cargo:');
$pdf->ln();
$pdf->write(5,'Cédula:');
$pdf->ln();

$pdf->output(date('YmdHis').'.pdf','D');

exit;

?>