var ctx;
var unixMin; // Minimum Unix Time in Logs
var unixMax;
var drawMin;
var drawMax;
var userCount;
var canvasWidth=1000;
var XOffset = 8;
var YOffset = 86;
var userChatArray = new Array();
var startArray = new Array();
var userArray = new Array();
var nameArray = new Array();
var severeArray = new Array();
var warningArray = new Array();
var consoleMsgArray = new Array();
var consoleChatArray = new Array();
var commandArray = new Array();
var runecraftArray = new Array();

var mcVersionArray = new Array();
var cbVersionArray = new Array();
var bkVersionArray = new Array();

$(document).ready(function(){
		ctx = document.getElementById('grapher').getContext('2d');
		unixMin = parseFloat($("#unixMin").text());	
		unixMax = parseFloat($("#unixMax").text());	
		drawMin = unixMin;
		drawMax = unixMax;
		userCount = $("#userCount").text();

    $("#grapher").mousedown(function(e){
      var XCoords = e.pageX;
      var YCoords = e.pageY;
      XCoords = XCoords - XOffset;
      YCoords = YCoords - YOffset;

      $("#cursorPosition").text(convertInlinePercent(calcTime(XCoords)));
	draw();
        ctx.globalCompositeOperation ="destination-over";
        ctx.fillStyle = "rgba(255,0,0,.5)";
        ctx.fillRect(XCoords,0,1,300);
        ctx.globalCompositeOperation = 'source-over';
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
                        active: false
                        });
		$( "#slider-range" ).slider({
			range: true,
			min: 0,
			max: 10000,
			values: [ 0, 10000 ],
			slide: function( event, ui ) {
				$( "#startRange" ).html(convertPercent((ui.values[ 0 ]/100)));
				$( "#endRange" ).html(convertPercent((ui.values[ 1 ]/100)));
				
			},
			change: function( event, ui ) {
				setScale((ui.values[ 0 ]/100), (ui.values[ 1 ]/100));
				draw();
				$( "#cursorPosition" ).html("");
			}
		});
		$( "#startRange" ).html( convertUnix(unixMin));
		$( "#endRange" ).html( convertUnix(unixMax));
		pushInfo();
		draw();
   });

function pushInfo(){
createArray();
draw();
extractUptime();
}

function getPosition(mouseEvent, element) {
    var x, y;
    if (mouseEvent.pageX != undefined && mouseEvent.pageY != undefined) {
        x = mouseEvent.pageX;
        y = mouseEvent.pageY;
    } else {
        x = mouseEvent.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        y = mouseEvent.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }
    return { X: x - element.offsetLeft, Y: y - element.offsetTop };
}

function navigate(page)
{
document.location=page;
}

function getcontent(page,div)
{
$.get(page, function(data) {
$("#"+div).html(data);
});
}

function autoupdate(page,div,delay)
{
var auto_refresh = setInterval(function()
{
$("#"+div).load(page);
}, delay * 1000);
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

var myDate = new Date( dateValue *1000);
return myDate.toDateString()+" "+myDate.getHours()+":"+myDate.getMinutes()+":"+myDate.getSeconds();
//return dateValue;
}
function convertInlinePercent(percent){
var base = drawMax - drawMin;
var minDiff = Math.floor((percent/100)*base);
dateValue = (drawMin+minDiff);
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

function calcPixel(unixTime) //Takes unixTime and converts it into a pixel value
{
var base = drawMax - drawMin;
var offset = unixTime - drawMin;
var percent = offset / base;
var pixel = Math.floor(percent*canvasWidth);
if (pixel<1){pixel=1;}
return pixel;
}

function calcTime(pixel) //Takes X-Pixel and converts it back into a Time value
{
if (pixel<1){pixel=1;}
var percent = pixel / canvasWidth;
return percent*100;
}


function createArray()
{
	$('.userChatItem').each(function(index) {
		userChatArray.push($(this).text());
	});
	$('.mcVersionList').each(function(index) {
		mcVersionArray.push($(this).text());
	});
	$('.cbVersionList').each(function(index) {
		cbVersionArray.push($(this).text());
	});
	$('.bkVersionList').each(function(index) {
		bkVersionArray.push($(this).text());
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
		commandArray.push($(this).text());
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
//	$('#commandArray').remove();
//	$('#runecraftArray').remove();

}

function drawLabel(pos,textLabel){
ctx.font = "12px Times New Roman";
//ctx.globalCompositeOperation ="destination-over";
ctx.fillText(textLabel, 3, pos+12);
//ctx.globalCompositeOperation ="source-over";
return;
}

function drawVersion(pos,pixel,textLabel){
ctx.font = "10px Times New Roman";
//ctx.globalCompositeOperation ="destination-over";
ctx.fillRect(pixel,pos+5,1,25);
//ctx.globalCompositeOperation ="source-over";
ctx.fillText(textLabel, pixel+2, pos+8);
//ctx.globalCompositeOperation ="source-over";
return;
}


function draw() {

var x;
var mark;
var startPixel;
var hei;
var pos;
var userChatColor ="rgba(48, 209, 209, 1)";
var startColor ="rgba(255,255,255,1)";

var mcVersionColor ="rgba(255,0,0,.65)";
var cbVersionColor ="rgba(40,173,173,.65)";
var bkVersionColor ="rgba(255,255,0,.65)";

var severeColor ="rgba(255,0,0,1)";
var warningColor ="rgba(255,128,0,1)";

var warningTextColor ="rgba(255,128,0,.65)";
var severeTextColor ="rgba(255,0,0,.65)";

var hey0Color ="rgba(255,255,0,1)";
var runecraftColor ="rgba(157,194,48,1)";
var consoleMsgColor ="rgba(55,186,43,1)";
var consoleChatColor ="rgba(40,173,173,1)";
var uptimeColor ="rgba(100,100,100,1)";
var activeUptimeColor ="rgba(38,41,79,1)";

//ctx.shadowOffsetX = 0;
//ctx.shadowOffsetY = 0;
//ctx.shadowBlur = 0;
ctx.shadowColor = "rgba(0, 0, 0, 0.55)";

var lastUptime=0;
//ctx.globalCompositeOperation ="source-over"
ctx.clearRect(0,0,2000,2000);

//Draw Minecraft Version in Red
pos=0;
ctx.fillStyle = mcVersionColor;
var i=0;
var zeroVersion;
for (x in mcVersionArray){
//alert(mcVersionArray[x]);
var test;
test = mcVersionArray[x].split("|");
mark = calcPixel(test[1]);
if (i==0){
    zeroVersion = test[0];
//drawVersion(pos,mark,test[0]);
} 
if (mark==1&&zeroVersion!=test[0]){
//alert(pos);
ctx.clearRect(2,pos+1,(pos+35),(pos+35));
//ctx.clearRect(0,0,2000,2000);

drawVersion(pos,mark,test[0]);
zeroVersion = test[0];
}else{
drawVersion(pos,mark,test[0]);    
}

i = i+1;
}

pos=10;
var i=0;
var zeroVersion;
ctx.fillStyle = bkVersionColor;
for (x in bkVersionArray){
//alert(mcVersionArray[x]);
var test;
test = bkVersionArray[x].split("|");
mark = calcPixel(test[1]);
if (i==0){
    zeroVersion = test[0];
//drawVersion(pos,mark,test[0]);
} 
if (mark==1&&zeroVersion!=test[0]){
//alert(pos);
ctx.clearRect(2,pos+1,(pos+25),(pos+25));
//ctx.clearRect(0,0,2000,2000);

drawVersion(pos,mark,test[0]);
zeroVersion = test[0];
}else{
drawVersion(pos,mark,test[0]);    
}

i = i+1;
}

pos=20;
var i=0;
var zeroVersion;
ctx.fillStyle = cbVersionColor;
for (x in cbVersionArray){
//alert(mcVersionArray[x]);
var test;
test = cbVersionArray[x].split("|");
mark = calcPixel(test[1]);
//ctx.fillRect(mark,pos,1,8);
//alert(test[1])
if (i==0){
    zeroVersion = test[0];
//drawVersion(pos,mark,test[0]);
} 
if (mark==1&&zeroVersion!=test[0]){
//alert(pos);
ctx.clearRect(2,pos,(pos+25),(pos+25));
//ctx.clearRect(0,0,2000,2000);

drawVersion(pos,mark,test[0]);
zeroVersion = test[0];
}else{
drawVersion(pos,mark,test[0]);    
}

i = i+1;
}

hei=30;
pos=30;
ctx.fillStyle = uptimeColor;
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
pos=pos+8;

for (x in startArray)
{
	if (startArray[x].substring(0,1)==1){
		mark=calcPixel(startArray[x].substring(2));
		ctx.fillRect(mark,pos,1,hei);
	}
}

hei=15;
//pos=31;
pos=pos+23;
ctx.fillStyle = severeTextColor;
drawLabel(pos,"Severe");
ctx.fillStyle = severeColor;

for (x in severeArray)
{
	mark=calcPixel(severeArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

pos= pos + (hei+1);


ctx.fillStyle = warningTextColor;
drawLabel(pos,"Warning");
ctx.fillStyle = warningColor;

for (x in warningArray)
{
	mark=calcPixel(warningArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}



ctx.fillStyle = hey0Color;
pos= pos + (hei+1);

drawLabel(pos,"Command");

for (x in commandArray)
{
	mark=calcPixel(commandArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

ctx.fillStyle = runecraftColor;
pos= pos + (hei+1);
drawLabel(pos,"WorldEdit");

for (x in runecraftArray)
{
	mark=calcPixel(runecraftArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

ctx.fillStyle = userChatColor;
pos= pos + (hei+1);
drawLabel(pos,"Chat");

for (x in userChatArray)
{
	mark=calcPixel(userChatArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

ctx.fillStyle = consoleChatColor;
pos= pos + (hei+1);
//drawLabel(pos,"Console Chat");

for (x in consoleChatArray)
{
	mark=calcPixel(consoleChatArray[x]);
	ctx.fillRect(mark,pos,1,hei);
}

ctx.fillStyle = consoleMsgColor;
//pos= pos + (hei+1);
drawLabel(pos,"Console");

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

drawLabel(pos,nameArray[i].substring(2));
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
