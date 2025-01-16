$(document).ready(function () {
	$('html').keypress(function(e) {
		var key = e.which;
		if (key == 110) {
			$('.next').click();
		}
		if (key == 112) {
			$('.prev').click();
		}
		if (key == 97) {
			$('.A').click();
		}
		if (key == 98) {
			$('.B').click();
		}
		if (key == 99) {
			$('.C').click();
		}
		if (key == 100) {
			$('.D').click();
		}
		if (key == 101) {
			$('.E').click();
		}
		if (key == 102) {
			$('.F').click();
		}
			
	});
});