window.addEvent('domready', function() {

	//zebra table
	var count = 0;
	$$('table.copy-table tr').each(function(el) {
		el.addClass(count++ % 2 == 0 ? 'odd' : 'even');
	});

/*
	// check all
	var ucuc = $('ucuc');
	if (ucuc) {
	ucuc.addEvent('click', function() {

		
		if(ucuc.get('rel') == 'yes') {
			do_check = false;
			ucuc.set('src','/assets/modules/copyEvo/img/uncheck.jpg').set('rel','no');
		}
		else {
			do_check = true;
			ucuc.set('src','/assets/modules/copyEvo/img/check.jpg').set('rel','yes');
		}
		$$('.check-me').each(function(el) { el.checked = do_check; });
		 
		alert('111');
		console.log(ucuc.get('rel'));


	});
}

*/


})