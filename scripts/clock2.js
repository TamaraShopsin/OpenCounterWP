    var clockCanvColor = ["#000", "#000"];
    function hoverClock(){
      clockCanvColor = ["rgb(0,124,196)", "rgb(0,124,196)"];
      draw();
    }
    function unhoverClock(){
      clockCanvColor = ["#000","#000"];
      draw();
    }

    function draw() {
      var canvas = document.getElementById('clockcanvas');
      if (canvas.getContext) {
        var c2d=canvas.getContext('2d');
        c2d.clearRect(0,0,50,50);
        //Define gradients for 3D / shadow effect
        var grad1=c2d.createLinearGradient(0,0,50,50);
        grad1.addColorStop(0,clockCanvColor[1]);
        grad1.addColorStop(1,clockCanvColor[0]);
        var grad2=c2d.createLinearGradient(0,0,50,50);
        grad2.addColorStop(0,clockCanvColor[0]);
        grad2.addColorStop(1,clockCanvColor[1]);
        c2d.font="Bold 14px Arial";
        c2d.textBaseline="middle";
        c2d.textAlign="center";
        c2d.lineWidth=2;
        c2d.save();
        //Outer bezel
        c2d.strokeStyle=grad1;
        c2d.lineWidth=4;
        c2d.beginPath();
        c2d.arc(25,25,23,0,Math.PI*2,true);
        
       
        c2d.stroke();
        //Inner bezel
        c2d.restore();
        c2d.strokeStyle=grad2;
        c2d.lineWidth=2;
        c2d.beginPath();
        c2d.arc(25,25,22,0,Math.PI*2,true);
        c2d.stroke();
        //c2d.strokeStyle="#222";
        c2d.save();
        c2d.translate(25,25);
        //Markings/Numerals
        /*for (i=1;i<=12;i++) {
          ang=Math.PI/30*i*5;
          sang=Math.sin(ang);
          cang=Math.cos(ang);
          sx=sang*19;
          sy=cang*-19;
          ex=sang*24;
          ey=cang*-24;
          nx=sang*16;
          ny=cang*-16;
          c2d.fillText(i/5,nx,ny);
        }*/

        var now=new Date();
        var hrs=now.getHours();
        var min=now.getMinutes();
        var sec=now.getSeconds();
        //c2d.strokeStyle="#000";

        
        c2d.lineWidth=2;
        c2d.save();
        //Draw clock pointers but this time rotate the canvas rather than
        //calculate x/y start/end positions.
        //
        //Draw hour hand
        c2d.rotate(Math.PI/6*(hrs+(min/60)+(sec/3600)));
        c2d.beginPath();
        c2d.moveTo(0,4);
        c2d.lineTo(0,-10);
        c2d.stroke();
        c2d.restore();
        c2d.save();
        //Draw minute hand
        c2d.rotate(Math.PI/30*(min+(sec/60)));
        c2d.beginPath();
        c2d.moveTo(0,4);
        c2d.lineTo(0,-18);
        c2d.stroke();
        c2d.restore();
        c2d.save();
        /*Draw second hand
        c2d.rotate(Math.PI/30*sec);
        c2d.strokeStyle="#E33";
        c2d.lineWidth=1;
        c2d.beginPath();
        c2d.moveTo(0,3);
        c2d.lineTo(0,-18);
        c2d.stroke();*/
        c2d.restore();
        
        //Additional restore to go back to state before translate
        //Alternative would be to simply reverse the original translate
        c2d.restore();
      }
    }
    
    window.onload = function(){
        document.getElementById("clockcanvas").onmouseover = hoverClock;
        document.getElementById("clockcanvas").onmouseout = unhoverClock;
        setInterval(draw, 60000);
        draw();
    };