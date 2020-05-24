/**
 * JS függvények
 */
var classMainPage = function() {
	var self = this;
	var responseDataStatus = 0;

	this.init = function() {
		this.processMainPageContent();
	}

	this.processMainPageContent = function() {
		$.ajax({
			url : "mainpage-ajax.php",
			type : "POST",
			dataType : "html",
			data : {
				processFunction : 'mainpage',
				status : 1
			},
			async : true,
			cache : false,
			timeout : 10000,
			error : function() {
				console.log('error1');
			},
			success : function(response) {
				console.log(response);
				$('.container-fluid').html(response);
				//eval(document.getElementById("status").innerHTML);
			}
		}).then(function() { // on completion, restart

			setTimeout(self.processMainPageContent, 5000); // function refers to
														// itself

		});
		return false;
	}

}

var objMainPage = new classMainPage();
$(document).ready(function() {
	objMainPage.init();
});