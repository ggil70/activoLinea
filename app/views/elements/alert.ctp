<?php
/**
 * Muestra alerta que viene del controlador
**/
?>
<script>
$(function(){
	alert(<?php echo json_encode(utf8_encode(strip_tags($message))) ?>);
});
</script>