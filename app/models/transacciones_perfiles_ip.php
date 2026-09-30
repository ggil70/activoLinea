<?php
/**
 * Modelo para el manejo de la información de las transacciones con condicionamiento ip.
 * 
 * @author EMSYS
 */


/**
 * Manejo de la información de las transacciones con condicionamiento ip.
 * 
 */
class TransaccionesPerfilesIp extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'transacciones_perfiles_ip';                
                
        /*
	 * Método para la consultar las transacciones definidas para el condicionamiento ip 
	 * por perfil	 
         * @param string $perfilId
	 * @return array
	 */        
        function buscarTransaccionesIP($perfilId){
                         
            $condiciones = array(
                "perfil_ib_id" => $perfilId               
            );
                       
            $parametros = array(
                'conditions' => $condiciones
            );
            
            $res = $this->find('all', $parametros);
            
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
        
         /**
	 * Método para validar que las notificaciones no superen el tiempo maximo definido.
	 * 	 
	 * @return boolean
	 */
	function validaTiempoMaximo(){
            
            App::import('Component', 'Session');
            $session = new SessionComponent();
            
            $usuario = $session->read('usuarios.datos');
            $notificaciones = $this->buscarNotificacionesViajeByUsuarioId($usuario['id']);
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