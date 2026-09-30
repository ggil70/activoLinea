<?php
/**
 * Genera el menu en el lado izquierdo de la secciÛn interna del portal
**/

if(!isset($es_movil)){
	$es_movil = false;
}

$secciones = array(        
	'aprobaciones.resumen'=> array(
		'nombre'=>'Aprobaciones Pendientes',
		'url'=>'/transacciones_pendientes',
		'id'=>36
	),
	'Transferencias Moneda Nacional'=>array(
		'transf.propias'=>array(
			'nombre'=>'Mismo Titular en Activo',
			'url'=>'/transferencias/propias',
			'id'=>100021,
			'movil'=>true
		),
		'transf.terceros'=>array(
			'nombre'=>'Tercero en Activo',
			'url'=>'/transferencias/terceros',
			'id'=>100022,
			'movil'=>true
		),
		'transf.otros'=>array(
			'nombre'=>'Mismo Titular Otro Banco',
			'url'=>'/transferencias/otros',
			'id'=>100023,
			'movil'=>true
		),
		'transf.terceros.otros'=>array(
			'nombre'=>'Tercero en Otro Banco',
			'url'=>'/transferencias/terceros_otros',
			'id'=>100024,
			'movil'=>true
		),
		'transf.simf'=>array(
			'nombre'=>'Consulta CrÈdito Inmediato',
			'url'=>'/transferencias/listado_simf',
			'id'=>101078,
			'movil'=>false
		)
	),
        //Moneda extanjera 
	'Transferencias Moneda Extranjera'=>array(
		'transfme.propias'=>array(
			'nombre'=>'Mismo Titular en Activo',
			'url'=>'/transferencia_Me/propias',
			'id'=>101070,
			'movil'=>true
		),

		'transfme.terceros'=>array(
			'nombre'=>'Terceros en Activo',
			'url'=>'/transferencia_Me/terceros',
			'id'=>101071,
			'movil'=>true
		)

	),
	'Pagos y Recargas '=>array(
		'Tarjetas de CrÈdito'=>array(

			'pagos.tdc.propias'=>array(
				'nombre'=>'Mismo Titular en Activo',
				'url'=>'/pagos_tdc/propias',
				'id'=>100025,
				'movil'=>true
			),
			'pagos.tdc.terceros'=>array(
				'nombre'=>'Tercero en Activo',
				'url'=>'/pagos_tdc/terceros',
				'id'=>100026,
				'movil'=>true
			),
			'pagos.tdc.otros'=>array(
				'nombre'=>'Mismo Titular Otro Banco',
				'url'=>'/pagos_tdc/otros',
				'id'=>100027,
				'movil'=>true
			),
			'pagos.tdc.terceros.otros'=>array(
				'nombre'=>'Tercero en Otro Banco',
				'url'=>'/pagos_tdc/terceros_otros',
				'id'=>100028,
				'movil'=>true
			)
		),	
		'pagos.servicios'=>array(
			'nombre'=>'Servicios Postpago',
			'url'=>'/pagos_servicios/pago_servicios',
			'id'=>100014
		),		
		'recarga.servicios'=>array(
			'nombre'=>'Recarga Prepago',
			'url'=>'/recarga_servicios/recarga_servicios',
			'id'=>100015
		),
        
        'Recarga Simpletv'=>array(
			'nombre'=>'Recarga Simpletv',
			'url'=>'/recarga_simpletv/recarga_simpletv',
			'id'=>102100
		),

        'Impuestos Nacionales/Aduanales'=>array(
			'pagos.seniat.propios'=>array(
				'nombre'=>'Mismo Titular',
				'url'=>'/pagos_seniat/compromisos',
				'id'=>100036
			),
			'pagos.seniat.terceros'=>array(
				'nombre'=>'Terceros',
				'url'=>'/pagos_seniat/terceros_listado',
				'id'=>100037
			)
		),
        'SAREN'=>array(
        	'pagos.saren'=>array(
        		'nombre'=>'Pagar Planilla',
        		'url'=>'/pagos_saren/pago',
        		'id'=>102098
			),
        ),
        'CORPOELEC'=>array(
        	'nombre'=>'Corpoelec',
    		'url'=>'/pagos_corpoelec/datos',
    		'id'=>101102
        )
	),
        'CrÈditos en Linea'=>array(
                'creditos_linea.avance.extracredito'=>array(
			'nombre'=>'ExtraCrÈdito Efectivo',
			'url'=>'/avance_efectivo/avance_extra',
			'id'=>100039
		),
                'creditos_linea.avance'=>array(
			'nombre'=>'Avance Efectivo',
			'url'=>'/avance_efectivo/avance',
			'id'=>100038
		),
	),
        'Tarjeta Prepago CASH'=>array(
            'prepagadas.solicitud'=>array(
			'nombre'=>'Solicitud',
			'url'=>'/tarjeta_prepagadas/presolicitud',
			'id'=>101090
		),
              'prepagas.activacion'=>array(
			'nombre'=>'ActivaciÛn',
			'url'=>'/tarjeta_prepagadas/activacion',
			'id'=>101091
		),
            'prepagas.cambioPIN'=>array(
			'nombre'=>'GeneraciÛn de Clave',
			'url'=>'/tarjeta_prepagadas/cambiopin',
			'id'=>101095
		    ),
		    'Recargas'=>array(
	            'prepagas.recargas'=>array(
				'nombre'=>'Mismo Titular en Activo',
				'url'=>'/tarjeta_prepagadas/recarga',
				'id'=>101092
			    ),
	            'prepagas.recargaterctpc'=>array(
	            'nombre'=>'Tercero en Activo',
				//'url'=>'/tarjeta_prepagadas/recargaterctpc',
				'url'=>'/tarjeta_prepagadas/recarga_tercero',
				'id'=>102099
			    ),
  		    ),

            'prepagas.consultas'=>array(
			'nombre'=>'Consulta Saldo',
			'url'=>'/tarjeta_prepagadas/seleccionar_consulta_saldo',
			'id'=>101093
		),
            'prepagas.movimientos'=>array(
			'nombre'=>'Movimientos',
			'url'=>'/tarjeta_prepagadas/movimientos',
			'id'=>101094
		),
            'prepagas.candadov'=>array(
			'nombre'=>'Candado Virtual',
			'url'=>'/tarjeta_prepagadas/candadov',
			'id'=>102097
		),


	),
	'Afiliaciones'=>array(
		'afiliaciones.afiliacion_tdc'=>array(
			'nombre'=>'Transferencias, Pagos y Recargas',
			'url'=>'/afiliaciones/listado',
			'id'=>array(13,14,100010,100011,100035)
		),
		'afiliaciones.envio_digital'=>array(
			'nombre'=>'EnvÌo Estado de Cuenta Digital',
			'url'=>'/afiliaciones/envio',
		),
		'afiliaciones.afiliacion_saldo_activo'=>array(
			'nombre'=>'Saldo Activo Plus',
			'url'=>'/afiliaciones/listado_saldo_activo',
                        'id'=>100020
		)
	),
	'Administracion Seguridad'=>array(
		'tarjetaTdc.activacion'=>array(
			'nombre'=>'ActivaciÛn de tarjeta de crÈdito',
			'url'=>'/tarjeta_credito_credicard/activacion',
			'id'=>101112	
		),
		'activoPay.api'=>array(
			'nombre'=>'Llave de acceso Activo Pay',
			'url'=>'/activo_pay/mostrar_key',
			'id'=>102103	
		),
		'cambio.pin'=>array(
			'nombre'=>'Cambiar Clave de Tarjeta de DÈbito',
			'url'=>'/seguridad/cambiar_pin',
			'id'=>100071
			
		),

		'cambio.clave'=>array(
			'nombre'=>'Cambiar Clave de Acceso Activo en LÌnea',
			'url'=>'/seguridad/cambiar_clave',
			'id'=>15
		),
		'cambio.datos'=>array(
			'nombre'=>'Cambiar Datos Contacto',
			'url'=>'/seguridad/cambiar_datos',
			'id'=>16
		),
		'cambio.preguntas'=>array(
			'nombre'=>'Cambiar Preguntas DesafÌo',
			'url'=>'/seguridad/cambiar_preguntas',
			'id'=>17
		),
		'cambio.imagen'=>array(
			'nombre'=>'Cambiar Imagen Antiphishing',
			'url'=>'/seguridad/cambiar_imagen',
			'id'=>18
		),
		'modificacion.limites'=>array(
			'nombre'=>'Cambiar LÌmites Transferencias',
			'url'=>'/seguridad/modificacion_limites',
			'id'=>19
		),
		'cambio.clave.atm'=>array(
			'nombre'=>'Cambio de Clave ATM',
			'url'=>'/seguridad/cambiar_clave_atm',
		),
		'empresa.crear'=>array(
			#solo a MASTER y MASTER/UNICO
			'nombre'=>'ConfiguraciÛn Usuarios',
			'url'=>'/empresa/listado', #INDO
			'id'=>37
		),
		'notificacion.viaje'=>array(			
			'nombre'=>'NotificaciÛn Viaje/Ausencia',
			'url'=>'/seguridad/notificacion_viaje',
			'id'=>100070
		),
		'contact.less'=>array(
			'nombre'=>'Tecnologia Contacless',
			'url'=>'/contact_less/consulta',
			'id'=>102104	
		),
		
	),
	'Cash Management' => array(
		'Pagos Moneda Nacional'=>array(
                    'Nomina'=>array(
			'cash.pagos.nomina'=>array(
                            'nombre'=>'Carga Manual',
                            'url'=>'/cash/pagos/n',
                            'id'=>array(39,100016),
                                'tipo'=> 'manual'
                            ),
			'cash.pagos.nomina.masiva'=>array(
                            'nombre'=>'Carga Masiva',
                            'url'=>'/cash/pago_masivo/n',
                            'id'=>array(39,100016),
                            'tipo'=> 'masiva'
                        )
                    ),
                    'Proveedores'=>array(
                    'cash.pagos.proveedores'=>array(
			'nombre'=>'Carga Manual',
			'url'=>'/cash/pagos/p',
                        'id'=>array(40,100017),
                            'tipo'=> 'manual'
                        ),
                    'cash.pagos.proveedores.masiva'=>array(
			'nombre'=>'Carga Masiva',
			'url'=>'/cash/pago_masivo/p',
                            'id'=>array(40,100017),
                            'tipo'=> 'masiva'
                            )
			)		
		),
                'Pagos Moneda Extranjera'=>array(
                    'Nomina'=>array(
                            'cash.pagos_me.nomina'=>array(
                            'nombre'=>'Carga Manual',
                            'url'=>'/cash/pagos_me/m', // m => tipo de pago nomina moneda Extranjera manual
                                    'id'=>array(101072),
                                'tipo'=> 'manual'
                            ),
                            'cash.pagos_me.nomina_masiva'=>array(
                                    'nombre'=>'Carga Masiva',
                                    'url'=>'/cash/pago_masivo_me/m',
                                    'id'=>array(101072),
                                    'tipo'=> 'masiva'
                            )
                    ),
                    'Proveedores'=>array(
                            'cash.pagos_p_me'=>array(
                                    'nombre'=>'Carga Manual',
                                    'url'=>'/cash/pagos_me/x',
                                    'id'=>array(101073),
                                    'tipo'=> 'manual'
                            ),
                            'cash.pagos_me.p_masiva'=>array(
                                    'nombre'=>'Carga Masiva',
                                    'url'=>'/cash/pago_masivo_me/x',
                                    'id'=>array(101073),
                                    'tipo'=> 'masiva'
                            )
                    )		                        
		), 
		'Afiliaciones'=>array(
			'Nomina'=>array(
				'cash.afiliaciones.nomina'=>array(
					'nombre'=>'Carga Manual',
					'url'=>'/cash/manejo_usuarios/n',
					'id'=>41
				),
				'cash.afiliaciones.nomina.masiva'=>array(
					'nombre'=>'Carga Masiva',
					'url'=>'/cash/carga_masiva_beneficiarios/n',
					'id'=>41
				)		
			),
			'Proveedores'=>array(
				'cash.afiliaciones.proveedores'=>array(
					'nombre'=>'Carga Manual',
					'url'=>'/cash/manejo_usuarios/p',
					'id'=>42
				),
				'cash.afiliaciones.proveedores.masiva'=>array(
					'nombre'=>'Carga Masiva',
					'url'=>'/cash/carga_masiva_beneficiarios/p',
					'id'=>42
				)
			)
		),
		'cash.seguimiento_gestion'=>array(
			'nombre'=>'Seguimiento de GestiÛn',
			'url'=>'/cash/seguimiento_gestion_filtros',
			'id'=>43
		),
		'Pago Express'=>array(
			'cash.pagos.proveedores.express'=>array(
				'nombre'=>'Carga Masiva Express',
				'url'=>'/cash/pago_masivo_express/p',
	                            'id'=>array(101107,101108),
	                            'tipo'=> 'masiva'
	        )
		)
	),
	'Solicitudes ' => array(
			'solicitar.cuenta'=>array(
				'nombre'=>'Apertura de Cuenta Cash',
				'url'=>'/solicitudes/solicitar_cuenta_cash',
				'id'=>101097
		),
		'Tarjetas de Coordenadas'=>array(
			'solicitar.tarjeta.coordenada'=>array(
				'nombre'=>'Solicitar',
				'url'=>'/solicitudes/solicitar_tarjeta_coordenada',
				'id'=>20
			),
			'anular.tarjeta.coordenada'=>array(
				'nombre'=>'Anular',
				'url'=>'/solicitudes/anular_tc_motivo',
				'id'=>21
			),		
		),
		'Suspensiones'=>array(
			'cheques.suspender'=>array(
				'nombre'=>'Suspender Cheques o Chequeras',
				'url'=>'/cheques',
				'id'=>22
			)
		),
		'Movil Activo'=>array(
			'movil.afiliar'=>array(
				'nombre'=>'AfiliaciÛn',
				'url'=>'/solicitudes/afiliacion_movil_activo/a',
				'id'=>100060
			),
                'movil.desafiliar'=>array(
				'nombre'=>'DesafiliaciÛn',
				'url'=>'/solicitudes/afiliacion_movil_activo/d',
				'id'=>100061
			)
		)

	),
        'Divisas'=>array(
		'comprar.divisas'=>array(
                    'nombre'=>'Quiero Comprar Divisas',
                    'url'=>'/mesa_cambio/compra',
                    'id'=>101074,
                    'movil'=>true
		),
		'vender.divisas'=>array(
                    'nombre'=>'Quiero Vender Divisas',
                    'url'=>'/mesa_cambio/venta',
                    'id'=>101075,
                    'movil'=>true
		),
		'consulta.operaciones'=>array(
                    'nombre'=>'Consulta de Solicitudes',
                    'url'=>'/mesa_cambio/listado',
                    'id'=>101076,
                    'movil'=>true
		)
	),
        'IntervenciÛn Cambiaria'=>array(
		'comprar.divisas.int'=>array(
                    'nombre'=>'Quiero Comprar Divisas',
                    'url'=>'/intervencion/compra',
                    'id'=>101088,
                    'movil'=>true
		),		
		'consulta.operaciones.int'=>array(
                    'nombre'=>'Consulta de Solicitudes',
                    'url'=>'/intervencion/listado',
                    'id'=>101089,
                    'movil'=>true
		)
	),
	'Otros servicios'=>array(
		'consulta.contrato'=>array(
			'nombre'=>'Consultar Contrato de Servicios',
			'url'=>'/otros_servicios/contrato',
			'id'=>23
		),	
		'asignacion.alias'=>array(
			'nombre'=>'Asignar Alias Productos',
			'url'=>'/otros_servicios/alias',
			'id'=>24
		),	
		'consultas.mensajes'=>array(
			'nombre'=>'Consulta de Mensajes',
			'url'=>'/consultas/mensajes',
			'id'=>47
		)
	)
);

$consultas = array();

$usuario = $this->Session->read('usuarios.datos');

if(
	(isset($instrumentos['SAV']) && $instrumentos['SAV']>0) ||
	(isset($instrumentos['DDA']) && $instrumentos['DDA']>0) ||
	(isset($instrumentos['NOW']) && $instrumentos['NOW']>0) ||
        (isset($instrumentos['MMK']) && $instrumentos['MMK']>0) ||   
	(isset($instrumentos['CC&']) && $instrumentos['CC&']>0) ||
	(isset($instrumentos['LNS']) && $instrumentos['LNS']>0) ||
        (isset($instrumentos['TDS']) && $instrumentos['TDS']>0) ||
	(isset($instrumentos['CDS']) && $instrumentos['CDS']>0)
){
	
	// Si es un juridico master no muestra el consolidado
	if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id']!=1){
		$consultas['consultas.integral']= array(
			'nombre'=>'Consolidado de Productos',
			'url'=>'/consultas/integral',
			'id'=>25,
			'movil'=>true
		);
	}
}

/*if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id']!=3){
		$secciones['aprobaciones.resumen']= array(
		'nombre'=>'Aprobaciones Pendientes',
		'url'=>'/transacciones_pendientes',
		'id'=>36
		);
	}*/


if(
	(isset($instrumentos['SAV']) && $instrumentos['SAV']>0) ||
	(isset($instrumentos['DDA']) && $instrumentos['DDA']>0) ||
        (isset($instrumentos['MMK']) && $instrumentos['MMK']>0) ||
	(isset($instrumentos['NOW']) && $instrumentos['NOW']>0)
){


	$consultas['consultas.cuentas']=array(
		'nombre'=>'Cuentas Bancarias',
		'url'=>'/cuentas/',
		'id'=>3,
		'movil'=>true
	);

	$consultas['consolidado.digital']=array(
		'nombre'=>'Detalle Productos y Servicios',
		'url'=>'/cuentas/consolidado_digital',
		'id'=>101110,
		'movil'=>true
	);

	
}

// Si el usuario posee tarjetas de credito
if($consultaTdcInstrumento === true || $consultaTdcInstrumentoObj === true){

	$consultas['consultas.tarjetas']=array(
		'nombre'=>'Tarjetas de CrÈdito',
		'url'=>'/tarjetas/',
		'id'=>4,
		'movil'=>true			
	);

    $consultas['consultas.extrafinanciamientos']=array(
        'nombre'=>'Extrafinanciamientos',
        'url'=>'/extrafinanciamientos/',
        'id'=>100057,
        'movil'=>true
    );
}else
{
    // Si no posee eliminamos estas opciones del menu.
    array_pop($secciones['Creditos en Linea']['creditos_linea.avance']);
    array_pop($secciones['Creditos en Linea']['creditos_linea.avance.extracredito']);
}
if(isset($instrumentos['LNS']) && $instrumentos['LNS']>0){
	$consultas['consultas.creditos']=array(
		'nombre'=>'CrÈdito',
		'url'=>'/creditos/',
		'id'=>5,
		'movil'=>true
	);
}

if((isset($instrumentos['CDS']) && $instrumentos['CDS']>0) || (isset($instrumentos['TDS']) && $instrumentos['TDS']>0)){
	$consultas['consultas.inversiones']=array(
		'nombre'=>'Inversiones',
		'url'=>'/inversiones/',
		'id'=>6,
		'movil'=>true
	);
}

$consultas['consultas.historico.transacciones']=array(
	'nombre'=>'HistÛrico de Transacciones',
	'url'=>'/consultas/historico_transacciones',
	'id'=>100008
);

$consultas['consultas.historico.conexiones']=array(
	'nombre'=>'HistÛrico de Conexiones',
	'url'=>'/consultas/historico_conexiones',
	'id'=>100009
);

//Si es un juridico aprobador o master no muestra transferencias, pagos/recargas, mesa de cambio intervencion
if($usuario['rol_juridico_id']==4 || $usuario['rol_juridico_id']==1){
    unset($secciones['Transferencias Moneda Nacional']);
    unset($secciones['Transferencias Moneda Extranjera']);
    unset($secciones['Pagos y Recargas']);
    unset($secciones['Afiliaciones']);
    unset($secciones['Mesa de Cambio']);
    unset($secciones['IntervenciÛn Cambiaria']);
    unset($secciones['Cash Management']['Pagos Moneda Nacional']);
    unset($secciones['Cash Management']['Pagos Moneda Extranjera']);    
}

if($usuario['tipo_cedula'] == 'P'){
     unset($secciones['Pagos']['Impuestos Nacionales/Aduanales']);
}

if($usuario['tipo_rif'] == 'G'){
     unset($secciones['Pagos']['Impuestos Nacionales/Aduanales']['pagos.seniat.terceros']);
}

$secciones = array_merge(array('Consultas'=>$consultas),$secciones);

$pagoActivo = array();

$pagoActivo =array(
            'pago_activo.afiliacion'=>array(
			'nombre'=>'AfiliaciÛn/ModificaciÛn/DesafiliaciÛn',
			'url'=>'/pago_activo/listado_pago_activo',
			'id'=>100064
		),            
            'pago_activo.pago'=>array(
                    'nombre'=>'Pagos',
                    'url'=>'/pago_activo/pagos',
                    'id'=>100065
            ),        
            'Consultas'=>array(
			'pago_activo.transacciones'=>array(
				'nombre'=>'Consulta de Movimientos',
				'url'=>'/pago_activo/detalle',
				'id'=>100066,
				'movil'=>false
			),
			'pago_activo.historico.transacciones'=>array(
				'nombre'=>'HistÛrico de Transacciones',
				'url'=>'/pago_activo/historico_transacciones',
				'id'=>100067,
				'movil'=>false
			),
			'pago_activo.historico.conexiones'=>array(
				'nombre'=>'HistÛrico Conexiones',
				'url'=>'/pago_activo/historico_conexiones',
				'id'=>100068,
				'movil'=>false
			)
		),
            'pago_activo.directorio'=>array(
                'nombre'=>'Directorio',
                'url'=>'/pago_activo/consulta_directorio',
                'id'=>100069
            ),
            'pago_activo.qr'=>array(
                'nombre'=>'Crear QR',
                'url'=>'/pago_activo/qr',
                'id'=>102101
            ),

	
	);

//Si es un juridico aprobador o master no muestra transferencias y pagos/recargas
if($usuario['rol_juridico_id']==4 || $usuario['rol_juridico_id']==1){
    unset($pagoActivo['pago_activo.afiliacion']);
    unset($pagoActivo['pago_activo.pago']);
    unset($pagoActivo['Consultas']);
}

$secciones = array_merge(array('Pago Activo'=>$pagoActivo),$secciones);

if(!isset($seccion_actual)){
	$seccion_actual = '';
}

function arma_menu($menu, $actual, $view, $es_movil=false){
	$res = '';
	global $transacciones;
	
	foreach($menu as $seccion => $opciones){
			
		if(isset($opciones['url'])){
			
			// Sino esta existe el key "id" no aparece la opcion en el menu 
			if(!isset($opciones['id'])) continue;
			
			// Validacion de la transaccion
			
			if(!validarTransaccion($opciones['id'],0)) {
				continue;
			}
			
			if($es_movil && (!isset($opciones['movil']) || !$opciones['movil'])){
				continue;
			}
			
			// Verifica si muestra la opcion de carga manual
			if(isset($opciones['tipo']) && $opciones['tipo']=='manual'){
				if(in_array(39,$opciones['id'])){
					if(verificaAfiliados('n')) continue;
				} else {
					if(verificaAfiliados('p')) continue;
				}
			}
			
			if($seccion == $actual){
				$res .= '<li class="seleccionado">';
			} else {
				$res .= '<li>';
			}
			
			$res .= $view->Html->link($opciones['nombre'], $opciones['url'], array('id'=>$opciones['id']));
                        $res .= '</li>';
			
		} else {
			
			$submenu = arma_menu($opciones, $actual, $view, $es_movil);
			
			if($submenu){
				
				if(strpos($submenu,'<li class="seleccionado">') === false){
					$res .= '<li>';
					// $res .= (esMovil()) ? '<p class="menu_titulo">'.$seccion.'</p>' : $seccion;
					$res .= $seccion;
					$res .= '<ul class="menu" style="display:none">';
				} else {
					$res .= '<li class="abierto">'.$seccion;
					$res .= '<ul class="menu">';
				}

				$res .= $submenu;

				$res .= '</ul></li>';
				
			}
		}
	}

	return $res;
}

echo '<ul class="menu">';

echo arma_menu($secciones, $seccion_actual,$this, $es_movil);

echo '</ul>';

echo '<div style="width:200px"></div>';

?>
<div class="basic-modal-content" id="procesandoC">
	<h3>Procesando...</h3>
        <p><?php echo(_m('procesando.transaccion','la consulta solicitada')); ?></p>
</div>
<div class="basic-modal-content" id="cierre_seniat">
        <?php 
        $horaApertura = _p('apertura.seniat');
        $horaCierre = _p('cierre.seniat'); 
        $parametros = array(cambiarFormatoHoraAMPM($horaApertura),cambiarFormatoHoraAMPM($horaCierre));
        ?>
	<h3>Pago Deshabilitado</h3>
	<p><?php echo _m('cierre.seniat', $parametros); ?></p>
</div>

<div class="basic-modal-content" id="cierre_recargas">
        <?php 
        $horaApertura = _p('apertura.recarga');
        $horaCierre = _p('cierre.recarga'); 
        $parametros = array(cambiarFormatoHoraAMPM($horaApertura),cambiarFormatoHoraAMPM($horaCierre));
        ?>
	<h3>Recarga Deshabilitada</h3>
	<p><?php echo _m('cierre.recargas', $parametros); ?></p>
</div>
<div class="basic-modal-content" id="cierre_corpoelec">
        <?php 
        $horaApertura = _p('apertura.corpoelec');
        $horaCierre = _p('cierre.corpoelec'); 
        $parametros = array(cambiarFormatoHoraAMPM($horaApertura),cambiarFormatoHoraAMPM($horaCierre));
        ?>
	<h3>Pago Deshabilitado</h3>
	<p><?php echo _m('cierre.corpoelec', $parametros); ?></p>
</div>

<script type="text/javascript">
$(function(){
	var swMenu = 0;
	var swMenuAF = 0;

	//Colocar la palabra Nuevo en la opcion de men√∫
        // if (!$('li:contains(Pagos y Recargas )').hasClass('abierto')){
        //     $('li:contains(Pagos y Recargas )').addClass('nuevo');
        // }

        // if (!$('li:contains(Recarga Simpletv)').hasClass('abierto')){
        //     $('li:contains(Recarga Simpletv)').addClass('nuevo');
        // }

        // if (!$('li:contains(SAREN)').hasClass('abierto')){
        //     $('li:contains(SAREN)').addClass('nuevo');
        // }

        // if (!$('li:contains(Tarjeta Prepago CASH)').hasClass('abierto')){
        //     $('li:contains(Tarjeta Prepago CASH)').addClass('nuevo');
        // }

        // if (!$('li:contains(Afiliaciones)').hasClass('abierto')){
        //     $('li:contains(Afiliaciones)').addClass('nuevo');
        // }
        // if (!$('li:contains(Solicitudes )').hasClass('abierto')){
        //     $('li:contains(Solicitudes )').addClass('nuevo');
        // }

        // if (!$('li:contains(CORPOELEC)').hasClass('abierto')){
        //     $('li a:contains(CORPOELEC)').addClass('nuevo');
        // }

	$('#100036').click(function(){
            var horaServidor = '<?php echo date("G:i"); ?>';
            var horaApertura= '<?php echo _p('apertura.seniat'); ?>';
            var horaCierre = '<?php echo _p('cierre.seniat'); ?>';
            
            if(toTime(horaServidor, "G:i") < toTime(horaApertura, "G:i") || toTime(horaServidor, "G:i") > toTime(horaCierre, "G:i")){
                $('#cierre_seniat').modal({minHeight:120,minWidth:500,close:true});
                return false;
            }else{
                $('#procesandoC').modal({minHeight:120,minWidth:400,close:false});
                
            }        
        });
 
	$('#100037').click(function(){
            var horaServidor = '<?php echo date("G:i"); ?>';
            var horaApertura= '<?php echo _p('apertura.seniat'); ?>';
            var horaCierre = '<?php echo _p('cierre.seniat'); ?>';
            
            if(toTime(horaServidor, "G:i") < toTime(horaApertura, "G:i") || toTime(horaServidor, "G:i") > toTime(horaCierre, "G:i")){
                $('#cierre_seniat').modal({minHeight:120,minWidth:500,close:true});
                return false;
            }       
        });
        
        $('#100015').click(function(){
            var horaServidor = '<?php echo date("G:i"); ?>';
            var horaApertura= '<?php echo _p('apertura.recarga'); ?>';
            var horaCierre = '<?php echo _p('cierre.recarga'); ?>';
            
            if(toTime(horaServidor, "G:i") < toTime(horaApertura, "G:i") || toTime(horaServidor, "G:i") > toTime(horaCierre, "G:i")){
                $('#cierre_recargas').modal({minHeight:120,minWidth:500,close:true});
                return false;
            }        
        });

        $('#101106').click(function(){
            var horaServidor = '<?php echo date("G:i"); ?>';
            var horaApertura= '<?php echo _p('apertura.corpoelec'); ?>';
            var horaCierre = '<?php echo _p('cierre.corpoelec'); ?>';
            
            if(toTime(horaServidor, "G:i") < toTime(horaApertura, "G:i") || toTime(horaServidor, "G:i") > toTime(horaCierre, "G:i")){
                $('#cierre_corpoelec').modal({minHeight:120,minWidth:500,close:true});
                return false;
            }       
        });
        
	$('ul.menu a').click(function(event){event.stopPropagation();});
		
	$('ul.menu > li').click(function(event){

		if($(this).find('ul').size() > 0){

                    //condicion para ocultar y mostrar el nuevo
                    // if(($(this).is(':contains("Pagos y Recargas ")'))){
                    //     if($(this).hasClass('nuevo')){
                    //         $(this).removeClass('nuevo');
                    //     }else{
                    //     	swMenu = 0;	
                    //    	    $(this).addClass('nuevo');
                    //     }
                    // }


                    // if(($(this).is(':contains("SAREN")'))){
                    //     if($(this).hasClass('nuevo')){
                    //     	if(swMenu == 1){
                    //     	   $(this).removeClass('nuevo');	
                    //     	}else{
                    //     	   $(this).addClass('nuevo');
                        	   	
                    //     	}	
                    //     }

                    //     else{
                    //     	if(swMenu == 0){
                    //     		swMenu = 1;	
                    //     	}else{
                    //    	    	$(this).addClass('nuevo');
                    //    	    }	
                    //     }
                    // }



                    // if(($(this).is(':contains("Solicitudes ")'))){

                    //     if($(this).hasClass('nuevo')){
                    //         $(this).removeClass('nuevo')
                    //     }else{
                    //         $(this).addClass('nuevo');
                    //     }
                    // }

                    // if(($(this).is(':contains("Tarjeta Prepago CASH")'))){

                    //     if($(this).hasClass('nuevo')){
                    //         $(this).removeClass('nuevo')
                    //     }else{
                    //         $(this).addClass('nuevo');
                    //     }
                    // }

                    // if(($(this).is(':contains("Afiliaciones")'))){
                    //     if($(this).hasClass('nuevo')){
                    //     	if(swMenuAF == 0){
                    //             $(this).removeClass('nuevo');
                    //     	}else{
                    //     		swMenuAF = 0;
                    //             $(this).addClass('nuevo');                                
                    //     	}
                    //     }else{
                    //     	swMenuAF = 1;
                    //     }
                    // }                    


                    if($(this).hasClass('abierto')){
                            $(this)
                                .removeClass('abierto')
                                .find('ul').hide();
                    } else {
                            if($(this).parents('li.abierto').size()==0){
                                $('ul.menu li.abierto')
                                .removeClass('abierto')
                                .find('ul').hide();
                            }

                            $(this)
                                .addClass('abierto')
                                .find('ul').first().show();
                    }
		}
		
		event.stopPropagation();
	})

	.css('cursor','pointer');
});	

function toTime(hora) {
	var tiempo = new Date();
        var horas = hora.split(":")[0];
        var minutos = hora.split(":")[1];
        tiempo.setHours(horas);
        tiempo.setMinutes(minutos);
        return tiempo;	
} 

</script>