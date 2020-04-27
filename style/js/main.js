/**
 *	JS függvények
 */
var classPage = function()  
{  
    var self = this;
    var responseDataStatus = 0;
   
    this.init = function() {
    	this.processStart();
     	this.processStatusCheck();
    }
    
    this.processStart = function() {
    	$( "form" ).submit(function( event ) {
    		  var data = $( this ).serializeArray();
    		  event.preventDefault();
    		  $.ajax({
	    		url: "ajax.php",
				type: "POST",
				dataType: "json",
				data: {
					processFunction: 'startcrawler' , 
					status:1,
					formdata: data
				},
	            async: true,
	            cache: false,
	            timeout: 900000,
	            error: function(){
	            	console.log('error1');
	            },
	            success: function(response){ 
	            	console.log(response) ;
	            }
	        });
    	});
    	return false;
    }
        
    this.processStatusCheck = function() {
		$.ajax({
    		url: "ajax.php",
			type: "POST",
			dataType: "json",
			data: {
				processFunction: 'status' , 
				status:1
			},
            async: true,
            cache: false,
            timeout: 900000,
            error: function(){
            	console.log('error1');
            },
            success: function(response){ 
            	$('.status').html(response.html);
            }
	    }).then(function() {           // on completion, restart
	        
	        	setTimeout(self.processStatusCheck, 1000);  // function refers to itself
	        
	    });
		return false;
    }
    
 }  

var objPage = new classPage(); 
$(document).ready(function()
{
    objPage.init();
});