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

	if(!window.opener){
		var props = 'resizable=1,width='+screen.availWidth+',height='+screen.availHeight+',left='+screen.availLeft+',top='+screen.availTop;
	
		if($.browser.msie){
			props += ',toolbar=0,scrollbars=1,statusbar=0,menubar=0,location=1';
		} else {
			props += ',toolbar=0,scrollbars=1,statusbar=0,menubar=0,location=0';
		}
	
		page = window.open(window.location.href, 'bancoactivo', props);
		
		$('body').remove();	
	}	
});
