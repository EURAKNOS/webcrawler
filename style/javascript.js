/**
 * 
 */
$( document ).ready(function() {
	// add row
	var counter = 1;
	$("#addRow").click(function () {
	    var html = '';
	    html += '<div id="inputFormRow">';
	    html += '<div class="input-group mb-3">';
	    html += '<input type="text" name="class[' + counter + '][name]" class="form-control m-input" placeholder="Enter class name" autocomplete="off">';
	    html += '<input type="text" name="class[' + counter + '][title]" class="form-control m-input" placeholder="Enter title" autocomplete="off">';
	    html += '<div class="input-group-append">';
	    html += '<button id="removeRow" type="button" class="btn btn-danger">Remove</button>';
	    html += '</div>';
	    html += '</div>';
	
	    $('#newRow').append(html);
	    counter++;
	});
	
	// remove row
	$(document).on('click', '#removeRow', function () {
	    $(this).closest('#inputFormRow').remove();
	});
	
});

