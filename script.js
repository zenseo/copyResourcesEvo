jQuery(function(){

	// Zebra table
jQuery('table.copy-table tr:even').addClass('even');


// check all
/*jQuery('.no').on('click', function(){
	jQuery('.checkme').prop('checked', true);
	jQuery(this).prop('class', 'yes')
	.prop('src', '/assets/modules/copyEvo/img/check.jpg');
}); */


jQuery('.inradio').on('click', function(){
	if (jQuery(this).prop("value") == 't3' ) {
			jQuery('.newtitle').show(); 
	} else{  
		jQuery('.newtitle').hide(); 
	} 
}); 

jQuery('#ucuc').on('click', function(){
if ( jQuery("#ucuc").hasClass("yes") ) {
	jQuery('.checkme').prop('checked', false);
	jQuery("#ucuc").prop('class', 'no')
	.prop('src', '/assets/modules/copyEvo/img/uncheck.jpg');		
} else{ 
	jQuery('.checkme').prop('checked', true);
		jQuery("#ucuc").prop('class', 'yes')
		.prop('src', '/assets/modules/copyEvo/img/check.jpg'); 
}

});
 

})
