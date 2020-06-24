/**
 * JS függvények
 */
var classPage = function() {
	var self = this;
	var responseDataStatus = 0;

	this.init = function() {
		this.processStart();
		this.processStatusCheck();
	}

	this.processStart = function() {
		$( "#submit" ).click(function(event) {
			event.preventDefault();
			var data = $("form").serializeArray(); // form data

			$.ajax({
				url : "ajax.php",
				type : "POST",
				dataType : "json",
				data : {
					processFunction : 'checkUrl',
					formdata : data
				},
				async : true,
				cache : false,
				timeout : 10000,
				error : function() {
					console.log('error1');
				},
				success : function(response) {
					if (response.statusurl == 1) {
						$('#submitModal').modal('show');
						$("#accept-download").click(function() {
							self.processDownloadStart();
							$('#submitModal').modal('hide')
						});
					} else {
						self.processDownloadStart();
					}
				}
			});

		});
		return false;
	}
	this.processDownloadStart = function() {
		
		$("form").unbind().submit(function(event) {
			event.preventDefault();
			$( this ).unbind( event );
			var data = $(this).serializeArray(); // form data
			console.log(data);
			//$('#spinner').removeClass('spinner-none');
			$('#submit').prop("disabled", true);
			$('#error').html('').addClass('error-hidden');

			$.ajax({
				url : "ajax.php",
				type : "POST",
				dataType : "json",
				data : {
					processFunction : 'startcrawler',
					status : 1,
					formdata : data
				},
				async : true,
				cache : false,
				timeout : 900000,
				error : function() {
					console.log('error2');
				},
				success : function(response) {
					$('#url').prop("disabled", true);
					$('#match_url').prop("disabled", true);
					$('#w-name').prop("disabled", true);
					$('#submit').prop("disabled", true);
				}
			});
			
		});
		$('form').trigger('submit');
		return false;

	}

	this.processStatusCheck = function() {
		$.ajax({
			url : "ajaxcheck.php",
			type : "POST",
			dataType : "html",
			data : {
				processFunction : 'status',
				status : 1
			},
			async : true,
			cache : false,
			timeout : 900000,
			error : function() {
				console.log('error1');
			},
			success : function(response) {
				$('.status').html(response);
				//eval(document.getElementById("status").innerHTML);
			}
		}).then(function() { // on completion, restart

			setTimeout(self.processStatusCheck, 5000); // function refers to
														// itself

		});
		return false;
	}

}

var objPage = new classPage();
$(document).ready(function() {
	objPage.init();
});