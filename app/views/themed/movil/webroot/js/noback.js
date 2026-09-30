window.history.forward();
function noBack() { window.history.forward(); }

$(function(){

	$('body').keydown(function(e){
		
		if(
			!$(document.activeElement).is(':input') &&
			(
				e.keyCode==8 ||
				e.keyCode==116
			)
		){
			return false;
		}
	})
	.bind('onload',noBack())
	.bind('onpageshow',function(){
		if (event.persisted) noBack();
	});
});
