<?php
/**
 * Modelo para el manejo de la información de las notificaciones de ausencia.
 * 
 * @author EMSYS
 */


/**
 * Manejo de la información de las notificaciones de ausencia.
 * 
 */
class NotificacionAusencia extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'notificacion_ausencia';
        
        /**
	 * Retorna los tipos de instrumentos que existen
	 * 
	 * @return array
	 */
	function buscarTiposInstrumentos(){
            return array(
                0=>'Cuentas',
                1=>'Tarjeta de Crédito',
                2=>'Ambos'			
            );
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
                            'rule'=>array('inList',array('crear','editar','eliminar'))
                    ),
                    'requerido'=> array(
                            'rule'=>'notEmpty'
                    )
		),
                'codigo_pais' => array(                    	
                    'requerido'=> array(
                            'rule'=>'notEmpty'
                    )
		),
                'fecha_inicio' => array(                    	
                    'requerido'=> array(
                            'rule'=>'notEmpty'
                    )
		),
                'fecha_fin' => array(                    	
                    'requerido'=> array(
                            'rule'=>'notEmpty'
                    ),
                    'tiempoMax'=>array(
                            'rule'=>'validaTiempoMaximo'
                    )
		),
                'instrumento' => array(                    	
                    'requerido'=> array(
                            'rule'=>'notEmpty'
                    )
		),
	);
	
        /*
	 * Método para la consultar una Notificacion de Viaje (Cliente)
	 * 	 
         * @param array|string $id
	 * @return array
	 */        
        function buscarNotificacionesViaje($id){
                         
            $condiciones = array(
                "id" => $id               
            );
                       
            $parametros = array(
                'conditions' => $condiciones
            );
            
            $res = $this->find('first', $parametros);
            return $res;
        }                
        
        /*
	 * Método para la consultar todas las Notificaciones de Viaje de un Cliente
	 * 	 
         * @param array|string $usuario_id
	 * @return array
	 */        
        function buscarNotificacionesViajeByUsuarioId($usuario_id){
                         
            $condiciones = array(                
                "usuario_id" => $usuario_id,
                "estatus" => "1"
            );
                       
            $parametros = array(
                'conditions' => $condiciones
            );
            
            $res = $this->find('all', $parametros);
            
            return $res;
        }
        
        /*
	 * Método para la consultar las Notificaciones de Viaje de un Cliente excepto la modificada
	 * 	 
         * @param array|string $usuario_id
         * * @param array|string $notificacion_id
	 * @return array
	 */        
        function buscarNotificacionesViajeByUsuarioNot($usuario_id,$notificacion_id){
                         
            $condiciones = array(
                "id <>" => $notificacion_id,
                "usuario_id" => $usuario_id,
                "estatus" => "1"
            );
                       
            $parametros = array(
                'conditions' => $condiciones
            );
            
            $res = $this->find('all', $parametros);
            
            return $res;
        }
        
         /**
	 * Método para validar que las notificaciones no superen el tiempo maximo definido.
	 * 	 
	 * @return boolean
	 */
	function validaTiempoMaximo(){
            
            App::import('Component', 'Session');
            $session = new SessionComponent();
            
            $usuario = $session->read('usuarios.datos');
            $notificaciones = $this->buscarNotificacionesViajeByUsuarioNot($usuario['id'],$this->data['NotificacionAusencia']['id']);
            
            $interval = 0;            
            
            if(count($notificaciones)>0){                
                foreach ($notificaciones as $fecha){                     
                    $fecha_inicio = (date_format(new DateTime($fecha['NotificacionAusencia']['fecha_inicio']), 'U'));
                    $fecha_fin = (date_format(new DateTime($fecha['NotificacionAusencia']['fecha_fin']), 'U'));
                    $interval += round(($fecha_fin - $fecha_inicio) / (60*60*24));
                }
            }            
            
            $fechaInicio = new DateTime($this->data['NotificacionAusencia']['fecha_inicio']);
            $fechaFin = new DateTime($this->data['NotificacionAusencia']['fecha_fin']);
            
            $interval += round(($fechaFin->format('U') - $fechaInicio->format('U')) / (60*60*24));
            $tiempoMaximo = _p('limite_plazo_ausencia');
            
            return $tiempoMaximo>=$interval;
	}
}

?>