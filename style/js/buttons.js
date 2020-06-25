/**
 * JS függvények
 */
var classButtons = function() {
	var self = this;
	var ok = 0;
	var responseDataStatus = 0;

	this.init = function() {
		this.stopButton();
		this.deleteButton();
		this.metaButton();
		this.metaCheck();
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
			$('#deleteModal').modal('show');
			$('#deleteModal').attr( "data-id", data );
			$("#accept-delete").click(function() {
				
				var id = $('#deleteModal').attr( "data-id");
				var buttonTd = $('[data-id="' + id + '"]').closest('.fbuttons');
				var buttonTr = $('[data-id="' + id + '"]').closest('.w-row');
				$('#deleteModal').modal('hide')
				event.preventDefault();
				
				$.ajax({
					url : "buttons-ajax.php",
					type : "POST",
					dataType : "json",
					data : {
						processFunction : 'delete',
						data : id
					},
					async : true,
					cache : false,
					timeout : 10000,
					error : function() {
						//console.log('error stopButton');
					},
					success : function(response) {
						if (response.status == 1) {
							buttonTr.remove();
						}
					}
				});
			});
		});
		return false;
	}
	
	this.metaButton = function() {
		$( ".export-meta" ).click(function(event) {
			$('#meta-export-modal').modal({backdrop: 'static', keyboard: false});
			var button = $(this);
			var data = $(this).data("id"); // form data
			
			event.preventDefault();
			
			$.ajax({
				url : "export_meta.php",
				type : "POST",
				dataType : "json",
				data : {
					id : data
				},
				async : true,
				cache : false,
				timeout : 90000,
				error : function() {
					//console.log('error stopButton');
				},
				success : function(response) {
				}
			});
			
		});
		return false;
	}
	
	
	this.metaCheck = function() {
		$( ".export-meta" ).click(function(event) {
			event.preventDefault();
			setTimeout(function() {
						  self.metaCheckAjax();
					  }, 5000);
		});
	}
	
	this.metaCheckAjax = function() {
		var ok = 0;
		var mca = $.ajax({
			url : "ajaxmetacheck.php",
			type : "POST",
			dataType : "json",
			data : {
			},
			async : true,
			cache : false,
			timeout : 90000,
			error : function() {
				console.log('error2');
			},
			success : function(response) {
				if (response.check == 0) {
					$('.progress').html(response.percentage);
				} else {
					$('#meta-export-modal').modal('hide');
					window.location = 'downloadmeta.php?file=' + response.file;
					ok = 1;
				}
			}
		}).then(function() { // on completion, restart
		    if (ok == 1){
			 	
			} else {
				setTimeout(self.metaCheckAjax, 2000); // function refers to
			}
														// itself

		});
	}

}

var objButtons = new classButtons();
$(document).ready(function() {
	objButtons.init();
});