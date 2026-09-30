<?php
	if(!empty($servicios)){
		echo $form->input(
			'numservicio',
			array(
				'id'=>'PagoServiciosNumservicio',
				'label'=>false,
				'type'=>'select',
				'options'=>$servicios,
				'between'=>'</td><td class="campo" id="td_afiliados_wrapper">',
				'empty'=>'Seleccione',
				'name'=>'data[PagoServicios][numservicio]',
				'onchange' => "javascript:traer_saldos(this.value)"
			)
		);
	}else{
		echo "<label class='error-message'>". _m('pago.noafiliado',null,false)."</label>";
	
	}
?>