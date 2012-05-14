var clockCanvColor = "#000";
function hoverClock(){
  clockCanvColor = "rgb(0,124,196)";
  draw();
}
function unhoverClock(){
  clockCanvColor = "#000";
  draw();
}

function draw() {
  var canvas = document.getElementById('clockcanvas');
  if (canvas.getContext) {
    var c2d=canvas.getContext('2d');
    c2d.clearRect(0,0,50,50);
    c2d.strokeStyle=clockCanvColor;
    c2d.lineWidth=2;
    c2d.beginPath();
    c2d.arc(25,25,23,0,Math.PI*2,true);
    c2d.arc(25,25,21,0,Math.PI*2,true);
    c2d.closePath();
    c2d.stroke();

    var now=new Date();
    var hrs=now.getHours();
    var min=now.getMinutes();
    var sec=now.getSeconds();
        
    // minutes
    c2d.lineWidth=2;
    c2d.beginPath();
    c2d.moveTo(25 + Math.cos(Math.PI/30*(min+(sec/60)) - Math.PI / 2) * 18, 25 + Math.sin(Math.PI/30*(min+(sec/60)) - Math.PI / 2) * 18);
    c2d.lineTo(25 - Math.cos(Math.PI/30*(min+(sec/60)) - Math.PI / 2) * 4, 25 - Math.sin(Math.PI/30*(min+(sec/60)) - Math.PI / 2) * 4);
    c2d.closePath();
    c2d.stroke();
	// hours
    c2d.moveTo(25, 25);
    c2d.beginPath();
    c2d.lineTo(25 - Math.cos(Math.PI/6*(hrs+(min/60)+(sec/3600)) - Math.PI / 2) * 4, 25 - Math.sin(Math.PI/6*(hrs+(min/60)+(sec/3600)) - Math.PI / 2) * 4);
    c2d.lineTo(25 + Math.cos(Math.PI/6*(hrs+(min/60)+(sec/3600)) - Math.PI / 2) * 12, 25 + Math.sin(Math.PI/6*(hrs+(min/60)+(sec/3600)) - Math.PI / 2) * 12);
    c2d.closePath();
    c2d.stroke();
  }
}

window.onload = function(){
  if(document.getElementById("clockcanvas")){
    document.getElementById("clockcanvas").onmouseover = hoverClock;
    document.getElementById("clockcanvas").onmouseout = unhoverClock;
    setInterval(draw, 60000);
    draw();
  }
};
 
 if (typeof console == "undefined" || typeof console.log == "undefined"){
 console = { log: function() {} };
}