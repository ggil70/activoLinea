<?php
/**
 * Description of CreditosLinea
 *
 * @author P011203513
 */

class AvanceEfectivo extends AppModel {
    /**
     * Nombre de la tabla en base de datos.
     * 
     * @var string
     */
    var $name = 'AvanceEfectivo';
    var $useTable = false;
    var $error_tarjetas = array();
    var $form_errores = array();
    var $tarjetas = array();
    
    
    /**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
        
   var $validate = array(
		'cuentaAbono' => array(
                    'rule'=>'alphaNumeric',
                    'required' => true,
                    'allowEmpty' => false,
                    'message' => 'Este campo no puede estar vacio'
                )
	);
     
    // filtra las cuentas que no califican para el producto de Avance de Efectivo
    function getCuentas($res)
    {
        $cuentas = array();
        foreach($res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'] as $producto)
        {
            // SI son cuentas de ahorro o corriente
            if($producto['SUMTYP']=='DDA' || $producto['SUMTYP']=='SAV' || $producto['SUMTYP']=='NOW'){
                // Si no es IVSS o firma conjunta
                if($producto['SUMUSR']!='IVSS' && $producto['SUMFIR'] == 1){
                    // Si la cuenta esta activa
                    if($producto['SUMAST']=='A'){
                        $cuenta = array($producto['SUMACN']=>$producto['SUMDSC']);
                        array_push($cuentas,$cuenta);
                    }
                }
            }
        }
        return $cuentas;
    }
    
    // Obtenemos los parametros del producto
    function getParametros()
    {
        App::Import('Parametro','Model');
        $parametros = new Parametro();
        $p = $parametros->find('list',
                array(
                    'fields' => array("Parametro.codigo","Parametro.valor"),
                    'conditions'=>array(
                                   "Parametro.codigo"=>array("avance.monto.min",
                                                    "avance.tasa.vig",
                                                    "avance.tasa.prom",
                                                    "avance.fecha.desde.prom",
                                                    "avance.fecha.hasta.prom",
                                                    "avance.comision",
                                                    "avance.porc.max",
                                                    "avance.extra.porc.max",
                                                    "avance.extra.monto.min",
                                                    "avance.extra.tasa.vig",
                                                    "avance.extra.tasa.prom",
                                                    "avance.extra.fecha.prom.desde",
                                                    "avance.extra.fecha.prom.hasta",
                                                    "avance.extra.plazos",
                                                    "avance.extra.comision",
                                                    "avance.extra.emp.porc.max" ,
                                                    "avance.extra.emp.plazos",
                                                    "avance.extra.emp.monto.min" ,
                                                    "avance.extra.emp.tasa.vig" ,
                                                    "avance.extra.emp.tasa.prom" ,
                                                    "avance.extra.emp.fecha.prom.desde" ,
                                                    "avance.extra.emp.fecha.prom.hasta" ,
                                                    "avance.extra.emp.comision" ,
                                                    "avance.extra.emp.bin" 
                                                    ),
                                    "Parametro.canal_id"=>Configure::read('canal.ib')
                                    )
                    )
                );
        if(isset($p)){
            //Si existe una tasa promocional para avance de efectivo, nos aseguramos que este vigente seg?n la fecha
            if($p['avance.tasa.prom'] != '' && $p['avance.tasa.prom'] != '0')
            {
                $fecha_ini = date('d-m-Y',strtotime(str_replace('/', '-',$p['avance.fecha.desde.prom'])));
                $fecha_fin = date('d-m-Y',strtotime(str_replace('/', '-',$p['avance.fecha.hasta.prom'])));
                $today = date('d-m-Y');
                if($today >= $fecha_ini && $today <= $fecha_fin){
                    $p['avance.tasa.vig'] = $p['avance.tasa.prom'];
                }
            }
            
            //Si existe una tasa promocional para ExtraCredito efectivo, nos aseguramos que este vigente seg?n la fecha
            if($p['avance.extra.tasa.prom'] != '' && $p['avance.extra.tasa.prom'] != '0')
            {
                $fecha_ini = date('d-m-Y',strtotime(str_replace('/', '-',$p['avance.extra.fecha.prom.desde'])));
                $fecha_fin = date('d-m-Y',strtotime(str_replace('/', '-',$p['avance.extra.fecha.prom.hasta'])));
                $today = date('d-m-Y');
                if($today >= $fecha_ini && $today <= $fecha_fin){
                    $p['avance.extra.tasa.vig'] = $p['avance.extra.tasa.prom'];
                }
            }
            
            //Si existe una tasa promocional para ExtraCredito efectivo empleados, nos aseguramos que este vigente seg?n la fecha
            if($p['avance.extra.emp.tasa.prom'] != '' && $p['avance.extra.emp.tasa.prom'] != '0')
            {
                $fecha_ini = date('d-m-Y',strtotime(str_replace('/', '-',$p['avance.extra.emp.fecha.prom.desde'])));
                $fecha_fin = date('d-m-Y',strtotime(str_replace('/', '-',$p['avance.extra.emp.fecha.prom.hasta'])));
                $today = date('d-m-Y');
                if($today >= $fecha_ini && $today <= $fecha_fin){
                    $p['avance.extra.emp.tasa.vig'] = $p['avance.extra.emp.tasa.prom'];
                }
            }
            
        }
        return $p;       
    }
   
    
    // Limpiamos la data asegurandonos que los montos son double para poder realizar calculos
    function beforeValidate($opcions = array()){
        foreach($this->data['AvanceEfectivo']['monto_tarjeta'] as $k => $v)
        {
            $valdouble = (double)str_replace(',','.',(str_replace('.','',$v)));
            $this->data['AvanceEfectivo']['monto_tarjeta'][$k] = $valdouble;
        }
    }
    
    // Valida que el monto solicitado no es superior al saldo disponible
    function maxMonto(){
        unset($this->error_tarjetas);
        $this->error_tarjetas = array();
        $res = true;
        foreach($this->data['AvanceEfectivo']['monto_tarjeta'] as $k => $v){
            $saldo = $this->getMaxTarjeta($k, $this->tarjetas);
            if ($saldo < $v){
                array_push($this->error_tarjetas,$k);
               $res = false; 
            }                
        }
        return $res;
    }
    
    // Valida que al menos una tarjeta tenga solicitud de avance
    function alMenosUnaTarjeta(){
        unset($this->form_errores);
        $this->form_errores = array();
        foreach($this->data['AvanceEfectivo']['monto_tarjeta'] as $t){
            if($t > 0 )
                return true;
        }
        array_push($this->form_errores, _m('monto.cero', true));
        return false;
    }
    
    // Retorna el monto maximo que puede solicitar en una tarjeta especifica
    function getMaxTarjeta($num, $tarjetas){
       foreach($tarjetas as $t)
        {
            if($num == $t['numero'])
                return $t['saldo'];
        }
        return '';
    }
    
    // Devuelve true si la la tarjeta tiene errores de validaci?n
    function getErrorTarjetas($num){
        if(!empty($this->error_tarjetas) && in_array($num, $this->error_tarjetas)){
            return true;
        }
        return false;
    }
    
    function getDataConfirmacion($u){
        $d_confirm = array();
        $d_confirm['nombre_cliente'] = desencriptar($u['nombre']);
        $d_confirm['cuenta_abono']['numero'] = $this->data['AvanceEfectivo']['cuentaAbono'];
        $d_confirm['cuenta_abono']['msk'] = enmascara($this->data['AvanceEfectivo']['cuentaAbono']);
        $d_confirm['total_abono'] = 0;
        $d_confirm['total_comision_flat'] = 0;
        $tarjeta_confirm = array();
        $i = 0;
        //$tasa_anualizada = isset($this->data['AvanceEfectivo']['tasa_vig']) ? ($this->data['AvanceEfectivo']['tasa_vig']/12)/100 : "";
        foreach ($this->data['AvanceEfectivo']['monto_tarjeta'] as $k => $v){
            if($v != '' || $v != '0,00'){
                foreach($this->tarjetas as $t){
                    if($k == $t['numero']){
                        $alias = $t['alias'];
                        $i = $i + 1;
                        $tasa_anualizada = isset($this->data['AvanceEfectivo']['tasa'.$i]) ? ($this->data['AvanceEfectivo']['tasa'.$i]/12)/100 : "";
                        $numero = $k;
                        $msk = $t['msk'];
                        $saldo_disponible = $t['saldo'];
                        $monto_solicitado = $v;
                        $plazo = isset($this->data['AvanceEfectivo']['plazo_tarjeta'][$k]) ? $this->data['AvanceEfectivo']['plazo_tarjeta'][$k] : "";
                        $primer_pago = is_numeric($plazo) ? round($v *($tasa_anualizada/(1- pow((1 + $tasa_anualizada),(-$plazo)))),2) : 0;
                        //$comision_flat = round(($v * (double)$this->data['AvanceEfectivo']['comision'])/100,2);
                        $comision_flat = round(($v * (double)$this->data['AvanceEfectivo']['comi'.$i])/100,2);
                        $d_confirm['total_abono'] += $v;
                        $d_confirm['total_comision_flat'] += $comision_flat;
                        array_push($tarjeta_confirm, array('alias'=>$alias,
                                                            'numero'=>$numero,
                                                            'msk'=>$msk,
                                                            'saldo_disponible'=>$saldo_disponible,
                                                            'monto_solicitado'=>$monto_solicitado,
                                                            'plazo' => $plazo,
                                                            'primer_pago' => $primer_pago,
                                                            'comision_flat'=>$comision_flat));
                    }
                }
            }
        }
        $d_confirm['tarjetas'] = $tarjeta_confirm;
        
        return $d_confirm;
    }
}
?>