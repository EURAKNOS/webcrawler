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
					$('#submit').prop("disabled", false);
				},
				success : function(response) {
					$('#submit').prop("disabled", true);
					if (response.status == 1) {
						//$('#spinner').addClass('spinner-none');
					} else {
						// $('#error').html('Enter a domain
						// name').removeClass('error-hidden');
						//$('#spinner').addClass('spinner-none');
					}
				}
			});
			//$("form").remove(); //Remove the form
		});
		$('form').trigger('submit');
		return false;

	}

	this.processStatusCheck = function() {
		$.ajax({
			url : "ajax.php",
			type : "POST",
			dataType : "json",
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
				$('.status').html(response.html);
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