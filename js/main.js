$(document).ready(function(){
		$( "button" ).button();
		$( "#legendDialog" ).dialog({
			autoOpen: false,
			show: "fade",
			hide: "fade",
			closeOnEscape: true
		});
		$( "#statsDialog" ).dialog({
			autoOpen: false,
			show: "fade",
			hide: "fade",
			closeOnEscape: true
		});
		$( "#legend" ).click(function() {
			$( "#legendDialog" ).dialog( "open" );
			return false;
		});
		$( "#stats" ).click(function() {
			$( "#statsDialog" ).dialog( "open" );
			return false;
		});
                $( "#accordion" )
                        .accordion({
                        collapsible: true,
                        autoHeight: false,
                        active: false,
                        });
		pushInfo();
   });

function pushInfo(){
draw();
extractUptime();
}

function draw() {
  var ctx = document.getElementById('grapher').getContext('2d');
    ctx.fillStyle = "white";
    ctx.fillRect(5,5,795,195);
    ctx.fillStyle = "red";
    ctx.fillRect(10,10,785,30);
    ctx.fillStyle = "green";
    ctx.fillRect(10,45,785,30);
    ctx.fillStyle = "blue";
    ctx.fillRect(10,80,785,30);
return;
    }

function extractUptime(){
	var uptimeWeek = $("#uptimeWeek").html();	
	var uptimeDay = $("#uptimeDay").html();	
	var uptimeHrs = $("#uptimeHrs").html();	
	var uptimeMin = $("#uptimeMin").html();	
	var uptimeSeconds = $("#uptimeSeconds").html();	
	var uptimeStr= uptimeWeek+"w:"+uptimeDay+"d:"+uptimeHrs+"h:"+uptimeMin+"m:"+uptimeSeconds+"s"; 
	$("#uptimeOutput").html(uptimeStr);	
	var newuptime = $("#uptimeOutput").html();
//	alert(newuptime);
	$("#uptimeDialog").remove();	
	
}
