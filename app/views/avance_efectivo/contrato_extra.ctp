<?php

/**
 * Generador de contrato de extracredito efectivo
 * */
 
App::Import('Vendor', 'FPDF', array('file' => 'fpdf16/fpdf.php'));

$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();

$pdf->setAutoPageBreak(true, 10);
$pdf->setMargins(20, 10, 20);

$usuario = $this->Session->read('usuarios.datos');


$pdf->AddPage();

$pdf->image(ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS . 'img' . DS . 'logo.jpg', 10, 10, 70);

$pdf->setTextColor(0, 0, 0);
$pdf->setXY(30, 50);
$pdf->setFont('Arial', '', 12);
$pdf->cell(0, 10, "CONTRATO EXTRACREDITO EFECTIVO", '', 0, 'C');
$pdf->ln();
$pdf->setXY(20, 70);
$pdf->setFont('Arial', '', 10);
$pdf->cell(0, 10, "Fecha:");
$pdf->setX(45);
$pdf->cell(10, 10, date('d-m-Y'));
$pdf->setX(70);
$pdf->cell(0, 10, "Numero de Tarjeta:");
$pdf->setX(105);
$pdf->cell(0, 10, $contrato_data['num_tarjeta']);


$pdf->ln();


$pdf->cell(0, 10, "1.- IDENTIFICACION DE EL TARJETAHABIENTE:");
$pdf->ln();

$pdf->cell(0, 10, "Nombres y Apellidos: " . desencriptar($usuario['nombre']));
$pdf->ln();

$pdf->cell(0, 10, "2.- CANTIDAD Y PLAZO DEL PRESTAMO:");
$pdf->ln();

$pdf->MultiCell(0, 6, "EL TARJETAHABIENTE declara que ha sido informado  previamente a la firma de este contrato, que EL BANCO le aprobó una Línea de Crédito paralela a la TARJETA DE CREDITO de la cual es titular, que no afectará su límite de crédito, de la cual y a través del servicio ACTIVO EN LINEA  ha procedido a solicitar un PRESTAMO EN EFECTIVO, y que el presente contrato confirma las condiciones de financiamiento que ha escogido a través de ese canal, establecidas de seguida:");
$pdf->MultiCell(0, 6, "EL TARJETAHABIENTE se obliga a pagar y a devolver a EL BANCO, la cantidad de  Bs. ".$contrato_data['monto'].", otorgada bajo la modalidad de PRESTAMO EN EFECTIVO, abonada en la cuenta señalada por EL TARJETAHABIENTE distinguida con el Nº ".$contrato_data['cuenta_abono'].", en el  plazo de ".$contrato_data['plazo']." meses consecutivos, contados a partir de la firma del presente documento, mediante el pago de ".$contrato_data['plazo']." cuotas mensuales y consecutivas, que comprenden tanto las cantidades correspondientes a la amortización del capital como intereses, calculados sobre saldos deudores. ");
$pdf->MultiCell(0, 6, "El BANCO cargará el monto de las cuotas mensuales directamente al pago mínimo de la TARJETA DE CREDITO de la cual EL TARJETAHABIENTE es titular, siendo el vencimiento de cada una de las cuotas en la fecha máxima de pago indicada en los Estados de Cuenta de la TARJETA DE CREDITO, a través de la cual cancelará el crédito.");
$pdf->MultiCell(0, 6, "Es entendido que independientemente de haber recibido los Estados de Cuenta, EL TARJETAHABIENTE se obliga a efectuar el pago de las cuotas mensuales y consecutivas.");
$pdf->MultiCell(0, 6, "EL TARJETAHABIENTE conviene y acepta de forma expresa, que de la cantidad dineraria que le será otorgada por EL BANCO en calidad de préstamo a interés, EL BANCO deducirá por concepto de comisión Flat un porcentaje del ".$contrato_data['comision_porc']."%.");
$pdf->MultiCell(0, 6, "3.- Dicho préstamo será liquidado en su totalidad en la cuenta que EL TARJETAHABIENTE mantiene en EL BANCO y que ha señalado precisamente.");
$pdf->MultiCell(0, 6, "4.- Que EL TARJETAHABIENTE ha leído con anterioridad a la fecha de firma de este instrumento, el contenido del mismo, estando conforme y aceptándolo en todas y cada una de sus partes, y suscribiéndolo voluntariamente. Asimismo declaro que conozco la fórmula de cálculo de la tasa de interés y las comisiones que se generan, todo lo cual me ha sido suministrado con antelación a la firma del presente, ello de conformidad con lo dispuesto en la normativa vigente aplicable a la materia. ");
$pdf->MultiCell(0, 6, "5.- Que la tasa de interés aplicable al presente crédito, será la tasa de referencia que EL BANCO determine para el producto denominado EXTRACREDITO EFECTIVO, las cuales serán publicadas en  las agencias, oficinas o sucursales del país, así como a través de la página Web de EL BANCO. Ha sido convenido que a partir del vencimiento de la primera mensualidad, la tasa de interés esté sometida al régimen de intereses variables, en tanto ello sea legalmente posible. En consecuencia, la tasa de interés podrá ser modificada y calculada por EL BANCO, una vez al mes, en igual fecha a la del otorgamiento del respectivo crédito, tomando como referencia la tasa máxima y mínima establecida por el BANCO CENTRAL DE VENEZUELA (BCV) o cualquier otra autoridad u organismo con competencia para ello. Sus modificaciones serán exhibidas de igual manera, en un lugar visible en cada una de las agencias, oficinas o sucursales del país, así como a través de la página Web de EL BANCO. En caso de mora en el pago de las obligaciones asumidas como TARJETAHABIENTE, conforme a este contrato, la tasa de interés aplicable será la que resulte de agregar a la tasa de interés vigente en el mes correspondiente tres puntos porcentuales (3%).");
$pdf->MultiCell(0, 6, "6.- Que el monto de cada una de las cuotas convenidas será determinada por EL BANCO en función del término concedido para el pago del crédito y tasa de interés aplicable. En razón de estar sometido el crédito al régimen de tasa de interés variable y ajustable, el monto de cada una de dichas cuotas variarán en cada oportunidad en que se modifique la tasa de interés aplicable o en caso que EL TARJETAHABIENTE realice algún pago parcial al crédito. EL TARJETAHABIENTE asume la obligación de informarse cada mes del monto de las variaciones de tasas de interés, y el monto de la cuota que debe pagar.");
$pdf->MultiCell(0, 6, "7.- EL TARJETAHABIENTE Reconoce y acepta que EL BANCO,  no  es responsable de los daños que pudieren presentar los bienes y/o servicios que adquiere, derivados de vicios ocultos,  defectos o deficiencias, y en caso de presentarse, los mismos serán reclamados directamente al establecimiento comercial, no siendo esto motivo de que EL TARJETAHABIENTE se exima de cumplirle a EL BANCO las obligaciones que  asume en este documento. De igual manera, EL TARJETAHABIENTE es responsable ante EL BANCO, por el consumo realizado, aún cuando los bienes y/o servicios que adquiere sean devueltos al establecimiento comercial, ante quien debe en todo caso realizar las acciones correspondientes, sin que pueda alegar en ningún momento el desconocimiento del préstamo. ");
$pdf->MultiCell(0, 6, "8.- Que EL TARJETAHABIENTE autoriza de forma voluntaria a EL BANCO, en la medida más amplia permitida por la Ley, a cargar o debitar de la cuenta indicada en el presente contrato, aquellas cantidades de dinero que adeudare por capital e intereses y otros cargos derivados del presente contrato o cualesquiera sumas que le adeudare derivadas de la utilización de la Línea de Crédito paralela otorgada. Es entendido que si se trata de una cuenta de ahorro o de inversión, incluyendo cuentas de nómina o de las cuentas que se le acreditara cantidades de dinero por pensiones y/o jubilaciones, deberá autorizar a EL BANCO a través de documento separado al presente. Así mismo, queda expresamente entendido que la autorización que se otorga a través del presente documento, en los casos de cuentas de ahorro y cuentas de nómina, podrá ser revocable por EL TARJETAHABIENTE, de conformidad con los previsto en los artículos 56 y 59 del Decreto con Rango, Valor y Fuerza de Ley de Reforma Parcial de la Ley de Instituciones del Sector Bancario y artículo 17 de la Ley de Tarjetas de Crédito, Debito, Prepagadas y demás Tarjetas de Financiamiento o Pago Electrónico.");
$pdf->MultiCell(0, 6, "9.- Que todo lo relacionado con el crédito aquí concedido, se reflejará en los correspondientes Estados de Cuenta de la TARJETA DE CREDITO. EL TARJETAHABIENTE conviene que en caso de una eventual acción judicial, se tenga como valido el Estado de Cuenta que EL BANCO presente tanto para la introducción de la causa como para la ejecución de alguna medida preventiva o ejecutiva, siempre y cuando no haya presentado reclamo alguno sobre el Estado de Cuenta, dentro del plazo de seis (6) meses contados a partir de la fecha de recepción del mismo.");
$pdf->MultiCell(0, 6, "10.- Que EL TARJETAHABIENTE podrá realizar la cancelación total o parcial del crédito otorgado, a través del formulario de solicitud correspondiente, consignándolo ante las agencias de EL BANCO, aceptando que la aplicación del pago, total o parcial se verá reflejada en el pago mínimo inmediato a la fecha de facturación de LA TARJETA DE CREDITO. En caso de amortización parcial, se entenderá que las cuotas se recalcularan y variaran en cuanto a su monto.");
$pdf->MultiCell(0, 6, "11.- Que en caso de que EL TARJETAHABIENTE dejare de tener tal condición o si por cualquier circunstancia el crédito no se pudiera cargar a la TARJETA DE CREDITO, EL TARJETAHABIENTE queda obligado a pagar la totalidad de las cantidades de dinero que para ese momento le adeude a EL BANCO en virtud del crédito concedido.  ");
$pdf->MultiCell(0, 6, "12.- Que EL BANCO tendrá  derecho para considerar la obligación de plazo vencido, líquida y exigible y en consecuencia, podrá proceder al cobro de todo cuanto EL TARJETAHABIENTE le adeude para ese momento, incluyendo, además, el capital, los intereses compensatorios y moratorios del préstamo aquí concedido y de todas  las obligaciones que para ese momento tenga contraída derivada de este préstamo: a) Cuando EL TARJETAHABIENTE no pagare cualesquiera de las obligaciones correspondientes a las cuotas financieras que está obligado a pagar, a los noventa (90) días siguientes a su vencimiento; b) Por el incumplimiento por parte de EL TARJETAHABIENTE de cualquier otra obligación prevista dentro de los términos de este contrato o que esté encuadrada dentro del Contrato de Tarjeta de Crédito Visa/ MasterCard de Banco Activo C. A. Banco Universal, mediante los cuales EL TARJETAHABIENTE, utilice el financiamiento rotativo, pues todas ellas se consideran igualmente principales. Ante las situaciones antes descritas, se procederá a cargar el monto total adeudado por la utilización de la Línea de Crédito paralela, al crédito rotativo.");
$pdf->MultiCell(0, 6, "13.- Que a la terminación de la relación contractual, por cualquier causa, de conformidad con lo establecido en el presente contrato y en el Contrato de Tarjeta de Crédito Visa/MasterCard de Banco Activo, C.A. Banco Universal, EL TARJETAHABIENTE no  podrá hacer uso de la TARJETA DE CREDITO, afectándose solo los consumos intentados realizar después de la fecha de terminación de la relación. Asimismo, EL TARJETAHABIENTE deberá  satisfacer la deuda que para la fecha de terminación de la relación contractual mantenga con EL BANCO con ocasión del uso de LA TARJETA, sin perjuicio para EL BANCO de poder exigir el pago del saldo total o parcial de su acreencia.");
$pdf->MultiCell(0, 6, "14- Que EL TARJETAHABIENTE  reconoce que el presente contrato se regirá en forma supletoria, por las disposiciones contenidas en el Contrato de Tarjeta de Crédito Visa/ MasterCard de Banco Activo C. A. Banco Universal, el cual está debidamente aprobado por la Superintendencia de la Instituciones del Sector Bancario, mediante oficios SIBF-II-GGR-GA-19866 de fecha 05 de octubre de 2010 y SIBF-DSB-II-GGTE-GEE-01144 de fecha de fecha 21 de enero de 2010 respectivamente, y que mantiene vigente EL BANCO, así como por las modificaciones que este pudiera tener en el futuro.");
$pdf->MultiCell(0, 6, "15.- Que en caso de robo, hurto, pérdida o cualquier hecho que conlleve al cambio de la numeración de LA TARJETA, EL TARJETAHABIENTE autoriza de manera irrevocable a EL BANCO para que haga extensiva a la nueva Tarjeta de Crédito que emita, todas las obligaciones que asume en este documento. ");
$pdf->MultiCell(0, 6, "16.- Que corre por exclusiva cuenta de EL TARJETAHABIENTE los gastos de cobranza judicial y/o extrajudicial, honorarios de abogados o cualquier otro gasto por incumplimiento.");
$pdf->MultiCell(0, 6, "17.- Que cualquier orden, notificación, aviso o comunicación que EL TARJETAHABIENTE haya de dirigir a EL BANCO con relación al préstamo recibido, lo hará  por escrito, por duplicado y por intermedio de las Agencias de EL BANCO. Cualquier orden, notificación, aviso o comunicación que haya de dirigir EL BANCO a EL TARJETAHABIENTE lo hará en la dirección que aparezca en los Registros de EL BANCO.");
$pdf->MultiCell(0, 6, "Para todos los efectos que se deriven de este Contrato, las partes eligen como domicilio especial al de EL TARJETAHABIENTE, y convienen expresamente en que todas las controversias que surjan con motivo del presente contrato serán resueltas a través de la jurisdicción ordinaria.");


$pdf->output('extracreditoEfectivo'.date('Ymdhis').substr($contrato_data['num_tarjeta'],-4).'.pdf', 'D');
exit;
?>

