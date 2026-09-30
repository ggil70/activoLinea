// Math closures
    (function(){

	/**
	 * Decimal adjustment of a number.
	 *
	 * @param	{String}	type	The type of adjustment.
	 * @param	{Number}	value	The number.
	 * @param	{Integer}	exp		The exponent (the 10 logarithm of the adjustment base).
	 * @returns	{Number}			The adjusted value.
	 */
	function decimalAdjust(type, value, exp) {
		// If the exp is undefined or zero...
		if (typeof exp === 'undefined' || +exp === 0) {
			return Math[type](value);
		}
		value = +value;
		exp = +exp;
		// If the value is not a number or the exp is not an integer...
		if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
			return NaN;
		}
		// Shift
		value = value.toString().split('e');
		value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
		// Shift back
		value = value.toString().split('e');
		return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
	}

	// Decimal round
	if (!Math.round10) {
		Math.round10 = function(value, exp) {
			return decimalAdjust('round', value, exp);
		};
	}
	// Decimal floor
	if (!Math.floor10) {
		Math.floor10 = function(value, exp) {
			return decimalAdjust('floor', value, exp);
		};
	}
	// Decimal ceil
	if (!Math.ceil10) {
		Math.ceil10 = function(value, exp) {
			return decimalAdjust('ceil', value, exp);
		};
	}

    })();




function getSelectionStart(o) {
	if (o.createTextRange) {
		var r = document.selection.createRange().duplicate()
		r.moveEnd('character', o.value.length)
		if (r.text == '') return o.value.length
		return o.value.lastIndexOf(r.text)
	} else return o.selectionStart
}

function getSelectionEnd(o) {
	if (o.createTextRange) {
		var r = document.selection.createRange().duplicate()
		r.moveStart('character', -o.value.length)
		return r.text.length
	} else return o.selectionEnd
}

function acomodar(){

	$('#box-login, #box-login2')
	.css('margin-top',
		Math.max(
			30,
			(
			window.innerHeight
			-$('#box-login, #box-login2').height()
			)/2
			-$('#top').height()
			-$('#footer').height()
		)
		

	)+"px";

}

function number_format2(val){
	if(/^[\d\.]+,\d{2,}$/.test(val)){
		val = val.replace(/\./g,'');
		
		var x = val.split(',');
		
		var entera = x[0];
		
		var decimal = '00'
		
		if(x.length > 1){
			decimal = x[1];
		}
		
		var entera_formateada = '';
		
		var count = 0;
		
		for(i=entera.length;i>=0;i--){
			entera_formateada = entera.charAt(i) + entera_formateada;
			
			if(count++ % 3 == 0 && count > 1 && i > 0){
				entera_formateada = '.'+entera_formateada;				
			}
		}
		
		return entera_formateada + ',' + decimal;
	} else if(/^[\d\.]+$/.test(val)){
		val = val.replace(/\./g,'');
		
		var entera = val;
		
		var entera_formateada = '';
		
		var count = 0;
		
		for(i=entera.length;i>=0;i--){
			entera_formateada = entera.charAt(i) + entera_formateada;
			
			if(count++ % 3 == 0 && count > 1 && i > 0){
				entera_formateada = '.'+entera_formateada;				
			}
		}
		
		return entera_formateada;
	} else {
		return val;
	}
}

function number_format(val){
	if(/^[\d\.]+(,\d{2,})?$/.test(val)){
		val = val.replace(/\./g,'');
		
		var x = val.split(',');
		
		var entera = x[0];
		
		var decimal = '00'
		
		if(x.length > 1){
			decimal = x[1];
		}
		
		var entera_formateada = '';
		
		var count = 0;
		
		for(i=entera.length;i>=0;i--){
			entera_formateada = entera.charAt(i) + entera_formateada;
			
			if(count++ % 3 == 0 && count > 1 && i > 0){
				entera_formateada = '.'+entera_formateada;				
			}
		}
		
		return entera_formateada + ',' + decimal;
	} else {
		return val;
	}
}

window.onresize=acomodar;

function showInfo(tipo){
	$.modal.close();
	setTimeout( function(){ $('#' + tipo).modal() }, 1000);
}



/***constantes**/
$key_Coma	= 188;
$key_Espacio=  32;
$key_Num_0	=  96;
$key_Num_9	= 105;
$key_0		=  48;
$key_9		=  57;
$key_A		=  65;
$key_Z		=  90;
$key_a		=  97;
$key_z		= 122;
/***************/

function teclaConflictiva(e){
	/* Indica para  todos los diversos navegadores 
	 * si la tecla pulsada es una tecla conflicto, que se confunda con otra
	 * por los valores de keyCode vs which.
	 * Retorna true o false	
	 **/
	if((e.keyCode==0) && (e.which==0)) return false;
	if((( ((e.keyCode>=33)&&(e.keyCode<=46)))&&((e.keyCode==e.which)))){	   
		return true;
	}
	if((( ((e.which>=33) && (e.which<=43))  ) && ((e.keyCode==0)) )){	   
		return true;
	}
	return false;	
}

function teclaEditNav(e){
	/* Indica para todos los diversos navegadores 
	 * si la tecla pulsada es una tecla de edicion o navegacion validas
	 * insert,supr,inicio,fin,left,up,right,down
	 * Retorna true o false	
	 **/
	if(e.keyCode == 8 ||  e.keyCode == 9 ||  e.keyCode == 33  ||  e.keyCode == 34  ||  e.keyCode == 35  ||  e.keyCode == 36  ||  e.keyCode == 37 || e.keyCode == 38 || e.keyCode == 39 || e.keyCode == 40|| e.keyCode == 45|| e.keyCode == 46){
		return true;			
	}
	return false;
}

function esDigito(e){		
	if(teclaConflictiva(e))	{return false;}
	if(teclaEditNav(e))		{return true;}
	code = e.keyCode?e.keyCode:e.which;
	result = 
		(
		 (code >=$key_0) && (code <= $key_9) 
		); 		
	return result;
}

function esLetra(e){
	if(teclaConflictiva(e))	{return false;}
	if(teclaEditNav(e))		{return true;}
	code = e.keyCode?e.keyCode:e.which;
	result = 
		(((code >=$key_A ) && (code <= $key_Z) )     || 
		 ((code >=$key_a ) && (code <= $key_z) )
		); 		
	return result;
} 

function esAlfanumerico(e){		
	if(teclaConflictiva(e))	{return false;}
	if(teclaEditNav(e))		{return true;}
	code = e.keyCode?e.keyCode:e.which;
	result = 
		(((code >=$key_A) && (code <= $key_Z) )     || 
		 ((code >=$key_a) && (code <= $key_z) )    || 
		 ((code >=$key_0) && (code <= $key_9) )	
		); 		
	return result;
}

function esNombrePersonal(e){		
	if(teclaConflictiva(e))	{return false;}
	if(teclaEditNav(e))		{return true;}

	code = e.keyCode?e.keyCode:e.which;
	result = 
		(((code >=$key_A) && (code <= $key_Z) )     ||
		 ((code >=$key_a) && (code <= $key_z) )     ||
		 (code == $key_Espacio)						||
		 ((e.which==241)|| (e.which==209))
		); 		
	return result;
}

function esDigitoKeyDownEvent(e){			
	if(teclaConflictiva(e))	{return false;}	
	if(teclaEditNav(e))		{return true;}		

	if((e.keyCode==0) && (e.which==0)) return false;
	
	result = ((e.which >= $key_Num_0) && (e.which <= $key_Num_9))||
			 ((e.which >= $key_0)	  && (e.which <= $key_9)) ;
	return result;
}


// Para deshabilitar las opciones de division dependiendo del tipo de formato
function deshabilitaDivisionFormato(){
	var opcionesTxt = {"f":"Longitud fija", ";":"Punto y coma (;)", "tab":"Tabulador"};
	
	var opcionesExcel = {"f":"Longitud fija"};
	
	var opciones;
	
	var select = $('.divisionFormato');
	
	if( $('.tipoFormato').val() == 'e' || $('.tipoFormato').val() == 'p' ){
		opciones = opcionesExcel;
	}
	else{
		opciones = opcionesTxt;
	}
	
	select.empty();
	
	select.append('<option value="">Seleccione</option>');
	
	for(i in opciones){
		select.append('<option value="'+i+'">'+opciones[i]+'</option>');
	}
}


$(function(){
	acomodar();
	
	$('a.envio_formulario').click(function(){
		$(this).parents('form').submit();
		return false;
	});
	
	$('input.monto').blur(function(){
		$(this).val(number_format($(this).val()));
	}).keydown(function(e){		
		if(
			e.keyCode==8 ||
			e.keyCode==9 ||
			e.keyCode==46 ||
			e.keyCode==35 ||
			e.keyCode==36 ||
			e.keyCode==37 ||
			e.keyCode==38 ||
			e.keyCode==39 ||
			e.keyCode==40 ||
			e.keyCode==16 ||
			e.keyCode==17 ||
			e.keyCode==18){
			// Caracteres de control
			return true;
		} else if(
			e.keyCode==48 ||
			e.keyCode==49 ||
			e.keyCode==50 ||
			e.keyCode==51 ||
			e.keyCode==52 ||
			e.keyCode==53 ||
			e.keyCode==54 ||
			e.keyCode==55 ||
			e.keyCode==56 ||
			e.keyCode==57 ||
			e.keyCode==96 ||
			e.keyCode==97 ||
			e.keyCode==98 ||
			e.keyCode==99 ||
			e.keyCode==100 ||
			e.keyCode==101 ||
			e.keyCode==102 ||
			e.keyCode==103 ||
			e.keyCode==104 ||
			e.keyCode==105		
		){
			// Números
			
			if(e.shiftKey || e.ctrlKey || e.altKey){
				return false;
			} else {
				var enteros = 0;
				var decimales = 0;			
				
				var commaPos = this.value.indexOf(','); // Posición de la coma
				var caretStart = getSelectionStart(this); // Posición del cursor en el input
				var caretEnd = getSelectionEnd(this); // Fin de la selección en el input
				// Contar los dígitos de la parte entera y la parte decimal
				
				for(i=0;i<this.value.length;i++){
					if(
						this.value.charAt(i)==0 ||
						this.value.charAt(i)==1 ||
						this.value.charAt(i)==2 ||
						this.value.charAt(i)==3 ||
						this.value.charAt(i)==4 ||
						this.value.charAt(i)==5 ||
						this.value.charAt(i)==6 ||
						this.value.charAt(i)==7 ||
						this.value.charAt(i)==8 ||
						this.value.charAt(i)==9
					){
						if(commaPos < 0){
							enteros++;
						} else if(i < commaPos){
							enteros++;
						} else {
							decimales++;
						}
					}
				}
				
				// Ver si ya hay coma en el número
				if(commaPos >= 0){
					// Ver si se esta editando la parte entera o la parte decimal
					if(caretStart > commaPos){
						// Se esta editando la parte decimal
						if(decimales < 2){
							// Si tenemos menos de 2 decimales puede ingresar uno mas
							return true;
						} else {
							// Si hay 2 decimales solo se puede insertar si se estan sustituyendo
							return caretEnd > caretStart;
						}
					} else if(enteros < 15){
						// Se esta editando la parte entera y la cantidad de digitos es menor a 15
						// permitir ingresar uno mas
						return true;
					} else {
						// Permitir sólo sustitución en caso de que hayan 15 decimales
						return caretEnd > caretStart;
					}
				} else if(enteros < 15){
					// No hay decimales, se esta editando la parte entera
					// y la cantidad de digitos es menor a 15
					// permitir ingresar uno mas
					return true;
				} else {
					// Permitir sólo sustitución en caso de que hayan 15 decimales
					return caretEnd > caretStart;
				}
			}
		} else if(
			e.keyCode==188
		){
			// Coma
			
			if(e.shiftKey || e.ctrlKey || e.altKey){
				return false;
			} else {
				return this.value.indexOf(',') == -1;
			}
		} else if(
			e.keyCode==110 ||
			e.keyCode==190
		){
			// Punto
			
			if(e.shiftKey || e.ctrlKey || e.altKey){
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}).keyup(function(e){
		if(
			e.keyCode==48 ||
			e.keyCode==49 ||
			e.keyCode==50 ||
			e.keyCode==51 ||
			e.keyCode==52 ||
			e.keyCode==53 ||
			e.keyCode==54 ||
			e.keyCode==55 ||
			e.keyCode==56 ||
			e.keyCode==57 ||
			e.keyCode==96 ||
			e.keyCode==97 ||
			e.keyCode==98 ||
			e.keyCode==99 ||
			e.keyCode==100 ||
			e.keyCode==101 ||
			e.keyCode==102 ||
			e.keyCode==103 ||
			e.keyCode==104 ||
			e.keyCode==105 ||
			e.keyCode==110 ||
			e.keyCode==188 ||
			e.keyCode==190			
		)
		{
			if(e.shiftKey || e.ctrlKey || e.altKey){
			} else {
				$(this).val(number_format2($(this).val()));
			}
		}
	});

	$('input.pago').keydown(function(e){
		if( (e.which==48 || e.which==96) && !$(this).val() ){
			return false;
		}
	});
	
	$('input.numerico').keypress(function(e){return esDigito(e);});

	$('input.letrasEspacio').keypress(function(e){return e.which==32 || esLetra(e);});

	$('input.soloLetras').keypress(function(e){return esLetra(e);});

	$('input.alfanumerico').keypress(function(e){return esAlfanumerico(e);});
	
	$('input.alfanumericoEspacio').keypress(function(e){return e.which==32 || esAlfanumerico(e);});

	$('input.nombrePersonal').keypress(function(e){ return esNombrePersonal(e);});
	
	$('input.tarjetaCredito').keypress(function(e){ return esDigito(e); });
	
	$("input[name*='email']").keypress(function(e){ return e.which!=32; });
	
	
	arrayTipos = {"V" : 8, "E" : 8, "P" : 15, "J" : 9, "G" : 9 };
	arrayTiposJur = {"V" : 9, "E" : 9, "J" : 9, "G" : 9, "P": 15 };
	
	
	$('.tipoCedulaRif').each(function(){
		$(this).next().attr('maxlength',arrayTipos[$(this).val()]);
	});
	
	$('.tipoCedulaRif').change(function(){
		var tamano = arrayTipos[$(this).val()];
		
		$(this).next()
			.val('')
			.attr('maxlength',tamano);
	});
	
	$('.tipoRif').each(function(){
		$(this).next().attr('maxlength',arrayTiposJur[$(this).val()]);
	});	
	
	
	$('.tipoRif').change(function(){
		
		var tamano = arrayTiposJur[$(this).val()];
		
		$(this).next()
			.val('')
			.attr('maxlength',tamano);
	});

/*******<login>********/

	$('input.login').keypress(function(e){
		if(this.value=='')return esLetra(e);		
		return esAlfanumerico(e);
	});

/*******</login>********/

	$('.nroCedulaRif, .nroRif')
	.keypress(function(e){
		if($(this).prev().val()=='P'){		
			return esAlfanumerico(e);
		}else{
			return esDigito(e);
		}
	})

	$('input').attr('autocomplete','off');

	$(".cambio_producto").change(function(){
		$(this).parents('form').submit();
	});
	
	arrayDays = {"01" : 31, "02" : 28, "03" : 31, "04" : 30, "05" : 31, "06" : 30, "07" : 31, "08" : 31, "09" : 30, "10" : 31, "11" : 30, "12" : 31 };
	$('select[name$="[month]"]').change(function(){	
		var id = "#" + ($(this).attr('id')).replace("Month", "Day");
		var cantidad = arrayDays[$(this).val()];
		
		if( $(this).val()=="02" ){
			
			var idYear = "#" + ($(this).attr('id')).replace("Month", "Year");
			var Year = $(idYear).val();
			
			if ((Year % 4 == 0) && (( Year % 100 != 0) || (Year % 400 ==0))){
				cantidad = 29;
			}
		}
		
		var dia_seleccion = $(id).val();
		$(id).empty();
		
		for(var i = 01; i<=cantidad; i++){			
			if(i<10){
				$(id).append('<option value="0'+i+'">0'+i+'</option');
			}
			else{
				$(id).append('<option value="'+i+'">'+i+'</option');
			}
		}
		$(id + " option[value="+dia_seleccion+"]").prop("selected",true);
	});
	
	$('select[name$="[year]"]').change(function(){
		var id = "#" + ($(this).attr('id')).replace("Year", "Day");
		var cantidad = arrayDays[$("#" + ($(this).attr('id')).replace("Year", "Month")).val()];
		
		var Year = $(this).val();

		if ((Year % 4 == 0) && (( Year % 100 != 0) || (Year % 400 ==0))){
			cantidad = 29;
		}
		
		var dia_seleccion = $(id).val();
		$(id).empty();
		
		for(var i = 01; i<=cantidad; i++){
			if(i<10){
				$(id).append('<option value="0'+i+'">0'+i+'</option');
			}
			else{
				$(id).append('<option value="'+i+'">'+i+'</option');
			}
		}
		$(id + " option[value="+dia_seleccion+"]").prop("selected",true);
	});
	
	jQuery.validator.setDefaults({
		submitHandler: function(form) {
			$.each($('tr td div.error-message'),function(){
				if($(this).siblings().size()==0){
					$(this).parent().parent().remove();
				}
			});
			
			$('div.error-message').remove();
			form.submit();
		},
		invalidHandler: function() {
			$.each($('tr td div.error-message'),function(){
				if($(this).siblings().size()==0){
					$(this).parent().parent().remove();
				}
			});
			$('div.error-message').remove();
		},
		errorPlacement: function(error, element) {
			$.each($('tr td div.error-message'),function(){
				if($(this).siblings().size()==0){
					$(this).parent().parent().remove();
				}
			});
			$('div.error-message').remove();
			error.insertAfter( element );
		}
	});	
	
	
	$('.seleccion_afiliado').click(function(){
		var s = $(this).attr('id');
		if(s){
			$('#monto_maximo').val($('#' + s.replace('sel', 'monto')).val());	
		}
	});
	
	$('#sijs').css('display','');acomodar();
	
	$('.boton_accion').click(function(){
		var form = $(this)
			.parents('form')
			.first();
			
		form.find('input[name="data[accion]"]').val($(this).attr('id'));
		
		form.submit();
		
		return false;
	});	
	
	// Mensaje de los links
	$(document).mouseover(function(){
		window.status='';
		return true;
	});
	

	// Inhabilita los enlaces en las confirmaciones
	$('a.bloqueo_repetir').click(function(e){
		
		if(!bloqueado){
			bloqueado = true;
		}
		else{
			e.preventDefault();
		}
			
	});
	
});


// manejo para bloqueo de boton
var bloqueado = false;


// manejo del cierre del explorador
var ejecutando = false;
function explorerEjecutando(){
	ejecutando = true;
}
function excepcionTecla(){
	// Casos en los que no debe saltar la alerta
    $('form').bind('submit',explorerEjecutando);
    $('a').bind('click',explorerEjecutando);
}
