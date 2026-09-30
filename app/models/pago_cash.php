<?php
/**
 * Modelo para el manejo de los pagos de nómina y proveedores.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los pagos de nómina y proveedores.
 * 
 */
class PagoCash extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = false;
	
	
	/**
	 * Listado de los pagos guardados por el usuario.
	 * 
	 * @param int $seleccion
	 * @param int $usuario_id
	 * @return array
	 */
	function listar($seleccion, $usuario_id){
		$this->TransaccionPendiente = ClassRegistry::init('TransaccionPendiente');
		
		$data = '';
		
		if($result = $this->TransaccionPendiente->find(
			'all',
			array(
				'fields'=>array('id','lote','cuenta_origen AS cuentaDebitar','fecha_hora_valor as fecha_valor','descripcion','monto as monto_total'),
				'conditions'=>array('TransaccionPendiente.id'=>$seleccion, 'usuario_id'=>$usuario_id)
			)
		)){
			$data = array();
			
			foreach ($result as $x){
				$temp['PagoCash'] = array_merge($x['TransaccionPendiente'],$x[0]);
				$temp['PagoCash'] = desencriptarCampos($temp['PagoCash'], array('descripcion','cuentaDebitar'));
				
				$datetime = strtotime($temp['PagoCash']['fecha_valor']);
				$temp['PagoCash']['fecha_valor'] = array(
					'day'=>date('d', $datetime),
					'month'=>date('m', $datetime),
					'year'=>date('Y', $datetime),
				);
				
				$data[] = $temp;
			}
			
		}
		
		return $data;
	}
	
	
	/**
	 * Busca la data de un pago guardado por el usuario
	 * 
	 * @param int $id
	 * @param int $usuario_id
	 * @return array
	 */
	function buscar($id,$usuario_id){
		$this->TransaccionPendiente = ClassRegistry::init('TransaccionPendiente');
		
		$data = '';
		
		if($result = $this->TransaccionPendiente->find(
			'first',
			array(
				'fields'=>array('id','lote','cuenta_origen AS cuentaDebitar','fecha_hora_valor as fecha_valor','descripcion','monto as monto_total'),
				'conditions'=>array('TransaccionPendiente.id'=>$id, 'usuario_id'=>$usuario_id)
			)
		)){
			
			$data['PagoCash'] = array_merge($result['TransaccionPendiente'],$result[0]);
			
			$data['PagoCash']['monto_total'] = formatea_monto_fromWs($data['PagoCash']['monto_total']);
			
			$data['PagoCash'] = desencriptarCampos($data['PagoCash'], array('descripcion','cuentaDebitar'));
			
			$datetime = substr($data['PagoCash']['fecha_valor'], 0, 11);
			$dateday = substr($datetime, 4, 2);
			$datemonth = monthNumber(substr($datetime, 0, 3));
			$dateyear = substr($datetime, 7, 4);
			
			$data['PagoCash']['fecha_valor'] = array(
				'day'=> $dateday,
				'month'=> $datemonth,
				'year'=> $dateyear,
			);
			
			$data['PagoCash']['vencida'] = !$this->validarFecha(array('fecha_valor'=>$data['PagoCash']['fecha_valor']));
			
			$data['Beneficiarios'] = $result['Beneficiarios'];
		}
		
		return $data;
	}
	
        /**
	 * Busca la data de un pago guardado Moneda Extranjera por el usuario
	 * 
	 * @param int $id
	 * @param int $usuario_id
	 * @return array
	 */
	function buscar_me($id,$usuario_id){
            $this->TransaccionPendiente = ClassRegistry::init('TransaccionPendiente');
		
            $data = '';
		
            if($result = $this->TransaccionPendiente->find(
                'first',
                array(
                    'fields'=>array('id','lote','cuenta_origen AS cuentaDebitar','fecha_hora_valor as fecha_valor','descripcion','monto as monto_total'),
                    'conditions'=>array('TransaccionPendiente.id'=>$id, 'usuario_id'=>$usuario_id)
                )
            )){
			
                $data['PagoCash'] = array_merge($result['TransaccionPendiente'],$result[0]);
			
                $data['PagoCash']['monto_total'] = formatea_monto_fromWs($data['PagoCash']['monto_total']);
			
                $data['PagoCash'] = desencriptarCampos($data['PagoCash'], array('descripcion','cuentaDebitar'));
 			
                $datetime = strtotime($data['PagoCash']['fecha_valor']);

                $data['PagoCash']['fecha_valor'] = array(
                    'day'=>date('d', $datetime),
                    'month'=>date('m', $datetime),
                    'year'=>date('Y', $datetime),
                );
						
                    $data['PagoCash']['vencida'] = !$this->validarFecha(array('fecha_valor'=>$data['PagoCash']['fecha_valor']));
			
                    $data['Beneficiarios'] = $result['Beneficiarios'];
            }
		
            return $data;
	}

        //js 06/10/2020
	
	/**
	 * Verifica la fecha valor
	 * 
	 * @param array $check [fecha_valor]=>[day],[month],[year]
	 * @return boolean
	 */
	function validarFecha($check){
		if(isset($check['fecha_valor'])){
			$value = array_values($check);
			$value = $value[0];
		}
		else {
			return false;
		}
		$fecha = $value['year'] . '-' . $value['month'] . '-' . $value['day'];

		// Valida la fecha
		if(!Validation::date($fecha, 'ymd')) return false;
		
		$timevalue = strtotime($fecha);
		
		// verifica que no sea menor a la actual
		if( $timevalue < mktime(0,0,0) ) return false;
		
		// Verifica si es un dia habil
		//if( date('w',$timevalue)==0 || date('w',$timevalue)==6 ) return false;
				
		// verifica que sea un dia feriado
		//$this->FechaFeriados = ClassRegistry::init('FechaFeriados');
		
		//if($this->FechaFeriados->find( 'count', array('conditions'=>(array('fecha'=>$fecha))) )) return false;
		
		return true;
	}
	
	/**
	 * Validador de montot total. Desglosa para mismo banco y otros bancos
	 * 
	 * @param array $check
	 * @return boolean
	 */
	function limiteMontoTotal($check){
            
		setlocale(LC_ALL,'es_VE.UTF-8'); //PARA LINUX
		//setlocale(LC_ALL,'esn'); //PARA WINDOWS
		
		set_time_limit(0);
                
		$error_it = false;
		$error_e = false;
	
		if(
			$this->data[$this->name]['monto_total_e']!="" &&
			isset($this->data[$this->name]['monto_limite_usuario_e']) &&
			$this->data[$this->name]['monto_limite_usuario_e']!="" // "" => Ilimitado
		){
		   $monto_formato = formatea_monto_fromWs($this->data[$this->name]['monto_total_e']);
                   $monto = formatea_monto_toWs($monto_formato);
		   $monto_maximo_e = formatea_monto_toWs($this->data[$this->name]['monto_limite_usuario_e']);		
                   
		   $error_e = $monto > $monto_maximo_e;
		}     		

		if(
			$this->data[$this->name]['monto_total_it']!="" &&
			isset($this->data[$this->name]['monto_limite_usuario_it']) &&
			$this->data[$this->name]['monto_limite_usuario_it']!="" // "" => Ilimitado
		){
		   $monto_formato = formatea_monto_fromWs($this->data[$this->name]['monto_total_it']);
                   $monto = formatea_monto_toWs($monto_formato);
		   $monto_maximo_it = formatea_monto_toWs($this->data[$this->name]['monto_limite_usuario_it']);		
                   
		   $error_it = $monto > $monto_maximo_it;
		}
		
		if($error_it && $monto_maximo_it==0){
			$this->validationErrors['monto_total'] = 'nopermitidaIt';
		} elseif($error_e && $monto_maximo_e==0){
			$this->validationErrors['monto_total'] = 'nopermitidaE';			
		} elseif($error_it && $error_e){
			$this->validationErrors['monto_total'] = 'montoLimiteAmbos';
		} elseif($error_it){
			$this->validationErrors['monto_total'] = 'montoLimiteIt';
		} elseif($error_e){
			$this->validationErrors['monto_total'] = 'montoLimiteE';
		}
		
		return true;		
	}       

	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'id' => array(
			'numero'=> array(
				'rule'=>'numeric'
			),		
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		
		'accion' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('crear','eliminar'))
			),
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		
		'cuentaDebitar' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			),
			'numero'=> array(
				'rule'=>'numeric'
			),
		),
		'lote' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			),
			'numero'=> array(
				'rule'=>'numeric'
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',9)
			)
		),
		'seleccion' => array(
			'requerido'=> array(
				'rule'=>  array('multiple'),
			)
		),
		
		'descripcion' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'archivo' => array(
			'extension'=>array(
				'rule' => array( 'extension', array('txt') ),
				'required'=>true
			),
                         'upload-file' => array(
                         'rule' => array('uploadFile'),
                         'message' => 'Estimado Cliente, el nombre del archivo no debe contener caracteres especiales, acentos ni espacios en blanco'
                        )

		),
		'monto_total' => array(
			'limiteMontoTotal'=>array(
				'rule'=>'limiteMontoTotal'
			),
			'numerico'=>array(
				'rule'=>'montoEstricto'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
		),
		'cantidad_registros' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			),
			'numero'=> array(
				'rule'=>'numeric'
			),
		),
		'fecha_valor' => array(
			'fecha_valor'=>array(
				'rule'=>'validarFecha'
			)
		),
	);
	
}

function monthNumber($num){
				$meses = array("JAN" => "01", "ENE" => "01", "FEB" => "02","MAR" => "03", "APR" => "04", "ABR" => "04", "MAY" => "05", "JUN" => "06", "JUL" => "07", "AUG" => "08", 
"AGO" => "08", "SEP" => "09", "OCT" => "10", "NOV" => "11", "DEC" => "12", "DIC" => "12");
								foreach ($meses as $key => $value) {
					if ($key == strtoupper($num)) {
						$mesN = $value;
					}
					
				}
				return $mesN;
			}
?>