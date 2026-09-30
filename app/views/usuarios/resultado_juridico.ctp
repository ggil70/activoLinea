<?php
/**
 * Mensaje de resultado del registro de usuarios jurídicos
**/
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($data_session['pasos']); $i++) { ?>
	<div class="paso_nro"><?php echo $i+1 ?></div>
	<div class="paso_desc"><?php echo $data_session['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

	
<div class="contenedor_formulario">
	<p class="exito">Operación realizada satisfactoriamente</p>

	<p>
		<?php 
		if(
			$this->data['Usuario']['perfil']>2 || 
			isset($this->data['legacy']) || 
			(
				isset($this->data['Usuario']['registro_incompleto']) && 
				$this->data['Usuario']['ibuser'] && 
				$this->data['Usuario']['perfil'] != 2
			) 
		){
			echo _m('registro.juridico.final2'); 
		}
		else{
			echo 'Estimado Cliente, para finalizar su registro descargue la plantilla a continuación.';
		}
		?>
	</p>

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td colspan="2" class="botonera">

				<?php 
				if(
					$this->data['Usuario']['perfil']>2 || 
					isset($this->data['legacy']) || 
					(
						isset($this->data['Usuario']['registro_incompleto']) && 
						$this->data['Usuario']['ibuser'] && 
						$this->data['Usuario']['perfil'] != 2
					) 
				){
					
					if(count($pendientes)>0){
						$ruta = '/transacciones_pendientes/';
					} else {
						$ruta = '/consultas/index';
					}
					
					
					// verifica si el usuario migrado esta activo
					if(isset($this->data['Usuario']['accesoIB']) && $this->data['Usuario']['accesoIB']!='A') $ruta = '/usuarios/login/J'; 
								
					echo $html->link(
						'Continuar',
						$ruta,
						array(
							'class'=>'button',
						)
					);
				}
				else{
					echo $html->link(
						'Descargar Planilla',
						'descargar_planilla',
						array(
							'class'=>'button',
							'id'=>'download'
						)
					);

					echo '&nbsp;&nbsp;';

					echo $html->link(
						'Continuar',
						'resultado_juridico_fin',
						array(
							'class'=>'button',
							'style'=>'display:none;',
							'id'=>'continue'
						)
					);
				}
				?>

			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	$(function(){
		$('#download').click(function(){
			$('#continue').show();
		});
	});
</script>