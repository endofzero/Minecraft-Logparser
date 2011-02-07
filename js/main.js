var ctx;
var unixMin;
var unixMax;
var drawMin;
var drawMax;
var userCount;
var canvasWidth=1000;
var userChatArray = new Array();
var startArray = new Array();
var userArray = new Array();
var nameArray = new Array();
var severeArray = new Array();
var warningArray = new Array();
var consoleMsgArray = new Array();
var consoleChatArray = new Array();
var hey0Array = new Array();
var runecraftArray = new Array();

$(document).ready(function(){
		ctx = document.getElementById('grapher').getContext('2d');
		unixMin = parseFloat($("#unixMin").text());	
		unixMax = parseFloat($("#unixMax").text());	
		drawMin = unixMin;
		drawMax = unixMax;
		userCount = $("#userCount").text();

// Graph Buttons
		$( "#serverStartGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});
		$( "#severeErrorGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});
		$( "#warningErrorGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});
		$( "#heyLoggingGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});
		$( "#runecraftGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});
		$( "#userLoginGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});
		$( "#userLogoutGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});
		$( "#userChatGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});
		$( "#consoleChatGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});
		$( "#consoleMsgGraph" ).button({icons:{primary:"ui-icon-signal"},text:false});

// Log Buttons
		$( "#serverStartLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
		.click(function() {
			var options;
			if ( $( this ).text() === "show" ) {
			$('.serverStart').removeClass('hide');
			$('.serverStop').removeClass('hide');
				options = {
					label: "hide",
					icons: {
						primary: "ui-icon-minus"
					}
				};
			} else {
			$('.serverStart').addClass('hide');
			$('.serverStop').addClass('hide');
				options = {
					label: "show",
					icons: {
						primary: "ui-icon-plus"
					}
				};
			}
			$( this ).button( "option", options );
		});

		$( "#severeErrorLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
                .click(function() {
                        var options;
                        if ( $( this ).text() === "show" ) {
                        $('.serverStart').removeClass('hide');
                        $('.serverStop').removeClass('hide');
                                options = {
                                        label: "hide",
                                        icons: {
                                                primary: "ui-icon-minus"
                                        }
                                };
                        } else {
                        $('.serverStart').addClass('hide');
                        $('.serverStop').addClass('hide');
                                options = {
                                        label: "show",
                                        icons: {
                                                primary: "ui-icon-plus"
                                        }
                                };
                        }
                        $( this ).button( "option", options );
                });

		$( "#warningErrorLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
                .click(function() {
                        var options;
                        if ( $( this ).text() === "show" ) {
                        $('.serverStart').removeClass('hide');
                        $('.serverStop').removeClass('hide');
                                options = {
                                        label: "hide",
                                        icons: {
                                                primary: "ui-icon-minus"
                                        }
                                };
                        } else {
                        $('.serverStart').addClass('hide');
                        $('.serverStop').addClass('hide');
                                options = {
                                        label: "show",
                                        icons: {
                                                primary: "ui-icon-plus"
                                        }
                                };
                        }
                        $( this ).button( "option", options );
                });

		$( "#heyLoggingLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
                .click(function() {
                        var options;
                        if ( $( this ).text() === "show" ) {
                        $('.serverStart').removeClass('hide');
                        $('.serverStop').removeClass('hide');
                                options = {
                                        label: "hide",
                                        icons: {
                                                primary: "ui-icon-minus"
                                        }
                                };
                        } else {
                        $('.serverStart').addClass('hide');
                        $('.serverStop').addClass('hide');
                                options = {
                                        label: "show",
                                        icons: {
                                                primary: "ui-icon-plus"
                                        }
                                };
                        }
                        $( this ).button( "option", options );
                });

		$( "#runecraftLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
                .click(function() {
                        var options;
                        if ( $( this ).text() === "show" ) {
                        $('.serverStart').removeClass('hide');
                        $('.serverStop').removeClass('hide');
                                options = {
                                        label: "hide",
                                        icons: {
                                                primary: "ui-icon-minus"
                                        }
                                };
                        } else {
                        $('.serverStart').addClass('hide');
                        $('.serverStop').addClass('hide');
                                options = {
                                        label: "show",
                                        icons: {
                                                primary: "ui-icon-plus"
                                        }
                                };
                        }
                        $( this ).button( "option", options );
                });

		$( "#userLoginLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
                .click(function() {
                        var options;
                        if ( $( this ).text() === "show" ) {
                        $('.serverStart').removeClass('hide');
                        $('.serverStop').removeClass('hide');
                                options = {
                                        label: "hide",
                                        icons: {
                                                primary: "ui-icon-minus"
                                        }
                                };
                        } else {
                        $('.serverStart').addClass('hide');
                        $('.serverStop').addClass('hide');
                                options = {
                                        label: "show",
                                        icons: {
                                                primary: "ui-icon-plus"
                                        }
                                };
                        }
                        $( this ).button( "option", options );
                });

		$( "#userLogoutLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
                .click(function() {
                        var options;
                        if ( $( this ).text() === "show" ) {
                        $('.serverStart').removeClass('hide');
                        $('.serverStop').removeClass('hide');
                                options = {
                                        label: "hide",
                                        icons: {
                                                primary: "ui-icon-minus"
                                        }
                                };
                        } else {
                        $('.serverStart').addClass('hide');
                        $('.serverStop').addClass('hide');
                                options = {
                                        label: "show",
                                        icons: {
                                                primary: "ui-icon-plus"
                                        }
                                };
                        }
                        $( this ).button( "option", options );
                });

		$( "#userChatLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
                .click(function() {
                        var options;
                        if ( $( this ).text() === "show" ) {
                        $('.serverStart').removeClass('hide');
                        $('.serverStop').removeClass('hide');
                                options = {
                                        label: "hide",
                                        icons: {
                                                primary: "ui-icon-minus"
                                        }
                                };
                        } else {
                        $('.serverStart').addClass('hide');
                        $('.serverStop').addClass('hide');
                                options = {
                                        label: "show",
                                        icons: {
                                                primary: "ui-icon-plus"
                                        }
                                };
                        }
                        $( this ).button( "option", options );
                });

		$( "#consoleChatLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
                .click(function() {
                        var options;
                        if ( $( this ).text() === "show" ) {
                        $('.serverStart').removeClass('hide');
                        $('.serverStop').removeClass('hide');
                                options = {
                                        label: "hide",
                                        icons: {
                                                primary: "ui-icon-minus"
                                        }
                                };
                        } else {
                        $('.serverStart').addClass('hide');
                        $('.serverStop').addClass('hide');
                                options = {
                                        label: "show",
                                        icons: {
                                                primary: "ui-icon-plus"
                                        }
                                };
                        }
                        $( this ).button( "option", options );
                });

		$( "#consoleMsgLog" ).button({icons:{primary:"ui-icon-minus"},text:false})
                .click(function() {
                        var options;
                        if ( $( this ).text() === "show" ) {
                        $('.serverStart').removeClass('hide');
                        $('.serverStop').removeClass('hide');
                                options = {
                                        label: "hide",
                                        icons: {
                                                primary: "ui-icon-minus"
                                        }
                                };
                        } else {
                        $('.serverStart').addClass('hide');
                        $('.serverStop').addClass('hide');
                                options = {
                                        label: "show",
                                        icons: {
                                                primary: "ui-icon-plus"
                                        }
                                };
                        }
                        $( this ).button( "option", options );
                });

		$( "button" ).button();
		$( "#graphDialog" ).dialog({
			autoOpen: false,
			show: "fade",
			hide: "fade",
			closeOnEscape: true
		});
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
		$( "#graphOpt" ).click(function() {
			$( "#graphDialog" ).dialog( "open" );
			return false;
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
		$( "#slider-range" ).slider({
			range: true,
			min: 0,
			max: 1000,
			values: [ 0, 1000 ],
			slide: function( event, ui ) {
				$( "#amount" ).html( convertPercent((ui.values[ 0 ]/10)) + " - " + convertPercent((ui.values[ 1 ]/10)) );
				
			},
			change: function( event, ui ) {
				setScale((ui.values[ 0 ]/10), (ui.values[ 1 ]/10));
				draw();
			}
		});
		$( "#amount" ).html( convertUnix(unixMin)+" - "+convertUnix(unixMax));
		pushInfo();
   });

function pushInfo(){
createArray();
draw();
extractUptime();
}

function setScale(percentMin, percentMax){
//alert(percentMin+" "+percentMax);
var base = unixMax - unixMin;
var minDiff = Math.floor((percentMin/100)*base);
var maxDiff = Math.floor((percentMax/100)*base);
//alert(minDiff+" "+maxDiff);
drawMin = (unixMin+minDiff);
drawMax = unixMin+maxDiff;
//alert(drawMin);

}

function convertUnix(unixTime){
var myDate = new Date( unixTime *1000);
return myDate.toDateString()+" "+myDate.getHours()+":"+myDate.getMinutes()+":"+myDate.getSeconds();
}

function convertPercent(percent){
var base = unixMax - unixMin;
var minDiff = Math.floor((percent/100)*base);
dateValue = (unixMin+minDiff);
//return dateValue;

var myDate = new Date( dateValue *1000);
return myDate.toDateString()+" "+myDate.getHours()+":"+myDate.getMinutes()+":"+myDate.getSeconds();
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
	$("#uptimeDialog").remove();	
	
}

function calcPixel(unixTime)
{
var base = drawMax - drawMin;
var offset = unixTime - drawMin;
var percent = offset / base;
var pixel = Math.floor(percent*canvasWidth);
if (pixel<1){pixel=1;}
return pixel;
}


function createArray()
{
	$('.userChatItem').each(function(index) {
		userChatArray.push($(this).text());
	});
	$('.severeErrorItem').each(function(index) {
		severeArray.push($(this).text());
	});
	$('.warningErrorItem').each(function(index) {
		warningArray.push($(this).text());
	});
	$('.serverStartItem').each(function(index) {
		startArray.push($(this).text());
	});
	$('.consoleChatItem').each(function(index) {
		consoleChatArray.push($(this).text());
	});
	$('.consoleMsgItem').each(function(index) {
		consoleMsgArray.push($(this).text());
	});
	$('.hey0Item').each(function(index) {
		hey0Array.push($(this).text());
	});
	$('.runecraftItem').each(function(index) {
		runecraftArray.push($(this).text());
	});
	$('.userStatsItem').each(function(index) {
		userArray.push($(this).text());
	});
	$('.userNameItem').each(function(index) {
		nameArray.push($(this).text());
	});

//	$('#userChatArray').remove();
//	$('#severeErrorArray').remove();
//	$('#warningErrorArray').remove();
//	$('#serverStatsArray').remove();
//	$('#consoleChatArray').remove();
//	$('#consoleMsgArray').remove();
//	$('#hey0Array').remove();
//	$('#runecraftArray').remove();

}

function draw() {

var x;
var mark;
var startPixel;
var hei;
var pos;
var userChatColor ="rgba(48, 209, 209, 1)";
var startColor ="rgba(255,255,255,1)";
var severeColor ="rgba(255,0,0,1)";
var warningColor ="rgba(255,128,0,1)";

var hey0Color ="rgba(255,255,0,1)";
var runecraftColor ="rgba(157,194,48,1)";
var consoleMsgColor ="rgba(55,186,43,1)";
var consoleChatColor ="rgba(40,173,173,1)";
var uptimeColor ="rgba(100,100,100,1)";
var activeUptimeColor ="rgba(38,41,79,1)";

ctx.fillStyle = uptimeColor;
hei=30;
pos=0;
var lastUptime=0;

ctx.globalCompositeOperation ="source-over"
ctx.clearRect(0,0,2000,2000);

for (x in startArray)
{
	if (startArray[x].substring(0,1)==1){
		mark=calcPixel(startArray[x].substring(2));
		if (mark >= lastUptime){lastUptime=mark}
		ctx.fillRect(mark,pos,1,hei);
		startPixel=mark;
	}
	if (startArray[x].substring(0,1)==0){
		mark=calcPixel(startArray[x].substring(2));
		if (mark >= lastUptime){lastUptime=mark}
		var pixelDiff = mark - startPixel;
		if (pixelDiff == 0){pixelDiff=1;}
		ctx.fillRect(startPixel,pos,pixelDiff,hei);
	}
}

ctx.fillStyle = activeUptimeColor;
lastUptime = lastUptime + 1;
ctx.fillRect(lastUptime,pos,canvasWidth,hei);

ctx.fillStyle = startColor;
hei=14;
pos=8

for (x in startArray)
{
	if (startArray[x].substring(0,1)==1){
		mark=calcPixel(startArray[x].substring(2));
		ctx.fillRect(mark,pos,1,hei);
	}
}

ctx.fillStyle = severeColor;
hei=15;
pos=31;

for (x in severeArray)
{
	mark=calcPixel(severeArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

    ctx.fillStyle = warningColor;
pos= pos + (hei+1);

for (x in warningArray)
{
	mark=calcPixel(warningArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

ctx.fillStyle = hey0Color;
pos= pos + (hei+1);

for (x in hey0Array)
{
	mark=calcPixel(hey0Array[x]);
	ctx.fillRect(mark,pos,1,hei);
}

ctx.fillStyle = runecraftColor;
pos= pos + (hei+1);

for (x in runecraftArray)
{
	mark=calcPixel(runecraftArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

ctx.fillStyle = userChatColor;
pos= pos + (hei+1);

for (x in userChatArray)
{
	mark=calcPixel(userChatArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

ctx.fillStyle = consoleChatColor;
pos= pos + (hei+1);

for (x in consoleChatArray)
{
	mark=calcPixel(consoleChatArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

ctx.fillStyle = consoleMsgColor;
pos= pos + (hei+1);

for (x in consoleMsgArray)
{
	mark=calcPixel(consoleMsgArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

//Set start user colors
r=224
g=157
b=27

// If USER COUNT IS N THEN PROCESS THE NEXT USER INFO< OTHERWISE NOOP

for (i=0;i<=(userCount-1);i++)
{

pos= pos + (hei+3);
ctx.fillStyle = "rgba("+r+","+g+","+b+",1)";
ctx.shadowOffsetX = 1;
ctx.shadowOffsetY = 1;
ctx.shadowBlur = 1;
//ctx.shadowColor = "rgba(255, 255, 255, .75)";
ctx.font = "15px Times New Roman";
//nameArray[0] = '1:Player 1';
//nameArray[1] = '1:Player 2';
ctx.globalCompositeOperation ="source-over"
ctx.fillText(nameArray[i].substring(2), 3, pos+12);
ctx.globalCompositeOperation ="destination-over"

ctx.shadowOffsetX = 0;
ctx.shadowOffsetY = 0;
ctx.shadowBlur = 0;
ctx.shadowColor = "rgba(0, 0, 0, 0.5)";
g= g-80
b= b+30

for (x in userArray)
{
	if (userArray[x].substring(2,3)==1 && userArray[x].substring(0,1)==i){
		mark=calcPixel(userArray[x].substring(4));
		if (mark >= lastUptime){lastUptime=mark}
		ctx.fillRect(mark,pos,1,hei);
		startPixel=mark;
	}
	if (userArray[x].substring(2,3)==0 && userArray[x].substring(0,1)==i){
		mark=calcPixel(userArray[x].substring(4));
		if (mark >= lastUptime){lastUptime=mark}
		var pixelDiff = mark - startPixel;
		if (pixelDiff == 0){pixelDiff=1;}
		ctx.fillRect(startPixel,pos,pixelDiff,hei);
	}
}
}

return;
    }
