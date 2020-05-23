/**
 * JS függvények
 */
var classButtons = function() {
	var self = this;
	var responseDataStatus = 0;

	this.init = function() {
		this.stopButton();
		this.deleteButton();
	}

	
	//stop-button
	
	this.stopButton = function() {
		$( ".stop-button" ).click(function(event) {
			var button = $(this);
			var data = $(this).data("id"); // form data
			var buttonTd = button.closest('.fbuttons');
			event.preventDefault();
			
			

			$.ajax({
				url : "buttons-ajax.php",
				type : "POST",
				dataType : "json",
				data : {
					processFunction : 'stop',
					data : data
				},
				async : true,
				cache : false,
				timeout : 10000,
				error : function() {
					console.log('error stopButton');
				},
				success : function(response) {
					if (response.status == 1) {
						buttonTd.html(response.button);
					}
				}
			});

		});
		return false;
	}
	

	this.deleteButton = function() {
		$( ".delete-button" ).click(function(event) {
			var button = $(this);
			var data = $(this).data("id"); // form data
			var buttonTd = button.closest('.fbuttons');
			var buttonTr = button.closest('.w-row');
			event.preventDefault();
			
			

			$.ajax({
				url : "buttons-ajax.php",
				type : "POST",
				dataType : "json",
				data : {
					processFunction : 'delete',
					data : data
				},
				async : true,
				cache : false,
				timeout : 10000,
				error : function() {
					console.log('error stopButton');
				},
				success : function(response) {
					if (response.status == 1) {
						buttonTr.remove();
					}
				}
			});

		});
		return false;
	}

}

var objButtons = new classButtons();
$(document).ready(function() {
	objButtons.init();
});