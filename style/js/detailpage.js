/**
 * JS függvények
 */
var classDetailsPage = function() {
	var self = this;
	var responseDataStatus = 0;

	this.init = function() {
		this.processDetailPageContent();
		this.processDetailPageStatus();
	}

	this.processDetailPageContent = function() {
		if ($('.container-fluid').data('status') < 1) {
			var id = $('.container-fluid').data('id');
			$.ajax({
				url : "mainpage-ajax.php",
				type : "POST",
				dataType : "html",
				data : {
					processFunction : 'detail',
					data : id
				},
				async : true,
				cache : false,
				timeout : 10000,
				error : function() {
					console.log('error1');
				},
				success : function(response) {
					$('.stat').html(response);
					//eval(document.getElementById("status").innerHTML);
				}
			}).then(function() { // on completion, restart
	
				setTimeout(self.processDetailPageContent, 5000); // function refers to
															// itself
	
			});
			return false;
		}
	}
	
	this.processDetailPageStatus = function() {
		if ($('.container-fluid').data('status') < 1) {
			var id = $('.container-fluid').data('id');
			$.ajax({
				url : "mainpage-ajax.php",
				type : "POST",
				dataType : "json",
				data : {
					processFunction : 'detail-status',
					data : id
				},
				async : true,
				cache : false,
				timeout : 10000,
				error : function() {
					console.log('error1');
				},
				success : function(response) {
					$('.status-data').html(response);
					//eval(document.getElementById("status").innerHTML);
				}
			}).then(function() { // on completion, restart
	
				setTimeout(self.processDetailPageStatus, 5000); // function refers to
															// itself
	
			});
			return false;
		}
	}


}

var objDetailPage = new classDetailsPage();
$(document).ready(function() {
	objDetailPage.init();
});