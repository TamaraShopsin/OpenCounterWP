/*
 * Copyright 2011-2012, www.SuperSignature.com
 * The first part below is jQuery and Full javascript code follows it
 * Kindly compress and obfuscate it when using in your production DLL
 * You can use: http://javascriptcompressor.com/
 */
ValidateSignature = function(id) {
    var result = true;

    if (id == null || 'undefined' == id || "" == id)
    {
        for (i = 0; i < signObjects.length; i++)
        {
            var id = signObjects[i], isitOk = eval("obj" + id).IsValid();
            if (!isitOk)
            {
                result = false;
            }
        }
    }
    else
    {
        result = eval("obj" + id).IsValid();
    }

    if (!isMobileIE && result == false) {
        document.getElementById(id).style.borderColor = "red";
    }

    return result;
};

ClearSignature = function(id) {

    if (id == null || 'undefined' == id || "" == id) {
        for (i = 0; i < signObjects.length; i++) {
            var id = signObjects[i];
            eval("obj" + id).ResetClick();
        }
    }
    else {
        eval("obj" + id).ResetClick();
    }

};

ResizeSignature = function(id, w, h) {
    eval("obj" + id).ResizeSignature(w, h);
};

SignatureColor = function(id, color) {
    eval("obj" + id).SignatureColor(color);
};

SignatureBackColor = function(id, color) {
    eval("obj" + id).SignatureBackColor(color);
};

SignaturePen = function(id, size) {
    eval("obj" + id).SignaturePen(size);
};

SignatureEnabled = function(id, enable) {
    eval("obj" + id).SignatureEnabled(enable);
};

SignatureStatusBar = function(id, show) {
    eval("obj" + id).SignatureStatusBar(show);
};

SignatureTotalPoints = function(id) {
    return eval("obj" + id).CurrentTotalpoints();
};

UndoSignature = function(id) {
    eval("obj" + id).UndoSignature();
};

LoadSignature = function(id, data, ratio) {
    eval("obj" + id).LoadSignature(data, ratio);
};

var msie = window.navigator.userAgent.toUpperCase().indexOf("MSIE ");
var isIE = false;
var isIENine = false;
var isMobileIE = false;
var isOperaMini = false;

if (window.navigator.userAgent.toUpperCase().indexOf("OPERA MINI") > 0) {
    isOperaMini = true;
}

if (window.navigator.userAgent.toUpperCase().indexOf("OPERA MOBI") > 0) {
    isOperaMini = true;
}

function supports_canvas() {
    return !!document.createElement('canvas').getContext;
}

function getInternetExplorerVersion()
{
    var rv = -1;
    if (window.navigator.appName == 'Microsoft Internet Explorer') {
        var ua = window.navigator.userAgent;
        var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
            rv = parseFloat(RegExp.$1);
    }
    return rv;
}

if (msie > 0) {
    isIE = true;

    if (supports_canvas()) {
        isIE = false;
        var ver = getInternetExplorerVersion();
        if (ver >= 9.0) {
            isIENine = true;
        }
    }

    if (window.navigator.userAgent.toUpperCase().indexOf("IEMOBILE ") > 0) {
        isMobileIE = true;
    }

    if (window.navigator.userAgent.toUpperCase().indexOf("WINDOWS PHONE ") > 0) {
        isMobileIE = true;
    }
}

function SuperSignature() {
    this.SignObject = "";
    this.PenSize = 3;
    this.PenColor = "#D24747";
    this.PenCursor = '';
    this.ClearImage = '';
    this.BorderWidth = "2px";
    this.BorderStyle = "dashed";
    this.BorderColor = "#DCDCDC";
    this.BackColor = "#fffffe";
    this.BackImageUrl = '';
    this.SignzIndex = "99";
    this.SignWidth = 450;
    this.SignHeight = 250;
    this.CssClass = "";
    this.ApplyStyle = true;
    this.SignToolbarBgColor = "#FFFFFF";
    this.RequiredPoints = 50;
    this.ErrorMessage = "Please continue your signature.";
    this.StartMessage = "Please sign";
    this.ImageScaleFactor = 0.50;
    this.TransparentSign = true;
    this.Enabled = true;
    this.Visible = true;
    this.Edition = "Trial";
    this.IsCompatible = false;
    this.InternalError = "";
    this.LicenseKey = "";
    this.IeModalFix = false;

    for (var n in arguments[0]) { this[n] = arguments[0][n]; }


    var pointCount = 0;
    var isIPhone = false;

    var currentSignObj = null;
    var currentSignContainerObj = null;
    var currSignStatus = null;
    var currSignToolbar = null;
    var currSignData = null;
    var currSignDebug = null;

    var enabled = this.Enabled;

    var mouseMoved = false;
    var isMouseDown = false;
    var cData = [], fData = [], kData = [];
    var currVersion = "1", dcMode = false, currPenSize = this.PenSize, currPenColor = this.PenColor, currBackColor = this.BackColor, currBorderColor = this.BorderColor;
    var currID = this.SignObject;
    var currW = this.SignWidth;
    var currH = this.SignHeight;
    var currErrMsg = this.ErrorMessage;
    var currBackUrl = this.BackImageUrl;
    var isValid = false;
    var currImgScale = this.ImageScaleFactor;
    var currTrans = this.TransparentSign;
    var a = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var currReqPts = this.RequiredPoints;

    var htm = "";
    var lastPositionX = 0;
    var lastPositionY = 0;
    var graphics;

    var iPhoneXPos = 0;
    var iPhoneYPos = 0;

    var iemodalfix = this.IeModalFix;

    if (isMobileIE) {

        graphics = new jsGraphics(currID + "_Container");

        if (graphics != null && graphics != 'undefined') {

            try {
                graphics.clear();
                graphics.paint();
            }
            catch (ee) {
                alert("Graphics object error " + ee.description);
            }
        }
        else {
            alert("Graphics object error");
        }

    }

    this.IsValid = function() { return isValid; };

    this.CurrentTotalpoints = function() { return currTotalPts; };

    this.ReturnFalse = function(e) {

        if (!isIE) {
            e.preventDefault();
        }

        return false;
    };


    function MyAttachEvent(obj, evt, fnc) {

        if (!obj.myEvents) obj.myEvents = {};

        if (!obj.myEvents[evt]) obj.myEvents[evt] = [];

        var evts = obj.myEvents[evt];

        evts[evts.length] = fnc;

    }

    function MyFireEvent(obj, evt) {

        if (!obj || !obj.myEvents || !obj.myEvents[evt]) return;

        var evts = obj.myEvents[evt];

        for (var i = 0, len = evts.length; i < len; i++) evts[i]();

    }

    this.XBrowserAddHandler = function(target, eventName, handlerName) {
        if (target.addEventListener)
            target.addEventListener(eventName, handlerName, false);
        else if (target.attachEvent)
            target.attachEvent("on" + eventName, handlerName);
        else {
            try {
                MyAttachEvent(target, eventName, handlerName);
                target['on' + eventName] = function() { MyFireEvent(target, eventName) };
            }
            catch (ex) {
                alert("Event attaching exception, " + ex.description);
            }
        }

    };

    this.DisableSelection = function() {
        if (!isIE) {
            if (typeof currentSignContainerObj.style.MozUserSelect != "undefined") {
                currentSignContainerObj.style.MozUserSelect = "none";
            }
            else {

                currentSignContainerObj.style.cursor = "default";
            }
        }
    };

    this.ResizeSignature = function(w, h) {

        currentSignContainerObj.style.width = w + "px";
        currentSignContainerObj.style.height = h + "px";

        currSignToolbar.style.width = w + "px";

        if (!isIE) {
            var curSiO = document.getElementById(this.SignObject);
            curSiO.width = parseInt(w, 0);
            curSiO.height = parseInt(h, 0);

            curSiO.style.width = w + "px";
            curSiO.style.height = h + "px";
        }
        else {
            currentSignObj.style.width = w + "px";
            currentSignObj.style.height = h + "px";
        }

        this.ResetClick();

        this.SignWidth = w;
        this.SignHeight = h;

        currW = w;
        currH = h;
    };

    this.SignatureColor = function(color) {
        this.PenColor = color;
        currPenColor = color;
    }

    this.SignatureBackColor = function(color) {
        this.BackColor = color;
        currBackColor = color;

        if (isIE) {
            currentSignObj.style.backgroundColor = color;
        }
        else {
            currentSignObj.fillStyle = color;
            currentSignObj.fillRect(0, 0, currW, currH);
        }
    }

    this.SignaturePen = function(size) {
        this.PenSize = size;
        currPenSize = size;
    }

    this.SignatureEnabled = function(enable) {
        this.Enabled = enable;
        enabled = enable;
    }

    this.SignatureStatusBar = function(show) {
        if (show) {
            $("#" + currSignToolbar.id).show("slow");
        }
        else {
            $("#" + currSignToolbar.id).hide("slow");
        }
    }

    this.UndoSignature = function() {

        if (cData.length <= 2) {
            this.ResetClick();
            return;
        }

        cData.pop();
        kData.pop();
        SetSignData();
        var dataNow = base64Decode("'" + currSignData.value + "'");
        this.LoadSignature(dataNow, 1);
    }

    this.LoadSignature = function(data, ratio) {

        this.ResetClick();

        if (ratio == null || ratio == 'undefined') {
            ratio = '1.0';
        }

        ratio = parseFloat(ratio);

        var cords = findPos(currentSignContainerObj);
        var XPos = cords[0];
        var YPos = cords[1];

        var allStrokes = RTrim(LTrim(data)).split(";");

        for (var i = 0, len = allStrokes.length; i < len - 1; i++) {
            cData[i] = allStrokes[i] + ";";
        }

        var allusefullData = allStrokes[0].split(",");

        currBackColor = allusefullData[1];
        currW = allusefullData[3];
        currH = allusefullData[4];
        currTrans = allusefullData[5];
        currID = allusefullData[7];

        this.SignatureBackColor(currBackColor);

        kData[0] = 0;

        for (var i = 1, len = allStrokes.length; i < len - 1; i++) {

            var allCords = RTrim(LTrim(allStrokes[i])).split(" ");

            kData[i] = parseInt(allCords.length, 0) - 1;
            kData[0] = parseInt(kData[0], 0) + parseInt(allCords.length, 0);

            for (var j = 0, lent = allCords.length; j < lent; j++) {
                var twoVals = allCords[j].split(",");
                var ptX = twoVals[0];
                var ptY = twoVals[1];


                if (j == 0) {
                    this.SignaturePen(ptX);
                    this.SignatureColor(ptY);
                }
                else if (j == 1) {

                    ptX = Math.abs(parseInt(ptX, 0) * ratio);
                    ptY = Math.abs(parseInt(ptY, 0) * ratio);

                    if (isIE) {
                        if (isMobileIE) {

                            lastPositionX = ptX;
                            lastPositionY = ptY;

                        }
                        else {
                            var w = '<SuperSignature:stroke weight="' + currPenSize + '" color="' + currPenColor + '" />',
                            t = '"m' + ptX + "," + ptY + " l" + ptX + "," + ptY, v;
                            v = '<SuperSignature:shape id="' + currID + "_l_" + (i - 1) + '" style="position: absolute; left:0px; top:0px; width:' + currW + "px; height: " + currH + 'px;" coordsize="' + currW + "," + currH + '"><SuperSignature:path v=' + t + ' e" /><SuperSignature:fill on="false" />' + w + "</SuperSignature:shape>";
                            currentSignObj.pathCoordString = t;
                            currentSignObj.insertAdjacentHTML("beforeEnd", v);
                        }
                    }
                    else {
                        currentSignObj.beginPath();
                        currentSignObj.lineJoin = "round";
                        currentSignObj.lineCap = "round";
                        currentSignObj.moveTo(ptX, ptY);
                    }
                }
                else {

                    ptX = Math.abs(parseInt(ptX, 0) * ratio);
                    ptY = Math.abs(parseInt(ptY, 0) * ratio);

                    if (!isIE) {
                        currentSignObj.strokeStyle = currPenColor;
                        currentSignObj.lineWidth = currPenSize;

                        currentSignObj.lineTo(ptX, ptY);
                        currentSignObj.stroke();
                        currentSignObj.moveTo(ptX, ptY);
                    }
                    else {
                        currentSignObj.pathCoordString += " " + ptX + "," + ptY;
                        var g = document.getElementById(currID + "_l_" + (i - 1));
                        if (g) {
                            var ic = g.firstChild;

                            if (ic) {
                                try {
                                    ic.setAttribute("v", currentSignObj.pathCoordString + " e");
                                }
                                catch (je)
                                { }
                            }
                        }
                    }
                }

                if (!isIE) {
                    currentSignObj.closePath();
                    currentSignObj.restore();
                }
                else {
                    currentSignObj.innerHTML = currentSignObj.innerHTML;
                }

            }
            pointCount++;
        }

        SetSignData();
    }

    this.CheckCompatibility = function() {
        if (isIE) {
            this.IsCompatible = true;

            if (!isMobileIE) {
                if (!document.namespaces["SuperSignature"]) {
                    document.namespaces.add("SuperSignature", "urn:schemas-microsoft-com:vml", "#default#VML");
                }
            }
        }
        else {
            var canvasCreated = false;

            try {
                canvasCreated = !!document.createElement("canvas").getContext("2d");
            }
            catch (e) {
                canvasCreated = !!document.createElement("canvas").getContext;
            }

            if (canvasCreated) {
                this.IsCompatible = true;
            }
            else {
                this.InternalError = "Your browser does not support our signature control.";
            }
        }
    };

    function ShowMessage(msg) {
        ShowDebug(msg);
    };

    function LTrim(value) {
        var re = /\s*((\S+\s*)*)/;
        return value.replace(re, "$1");
    }

    function RTrim(value) {
        var re = /((\s*\S+)*)\s*/;
        return value.replace(re, "$1");
    }

    function ShowDebug(msg) {

        if (currSignDebug != null && currSignDebug != 'undefined') {
            try {
                currSignDebug.innerHTML = msg + "...<br/>" + currSignDebug.innerHTML;
            }
            catch (exp1) {
                alert(exp1.description);
            }
        }
    };

    function base64Encode(b) {
        var h = "", i, d, e, k, l, j, f, g = 0;
        b = c(b);
        while (g < b.length) {
            i = b.charCodeAt(g++);
            d = b.charCodeAt(g++);
            e = b.charCodeAt(g++);
            k = i >> 2;
            l = (i & 3) << 4 | d >> 4;
            j = (d & 15) << 2 | e >> 6;
            f = e & 63;
            if (isNaN(d)) {
                j = f = 64;
            }
            else if (isNaN(e)) {
                f = 64;
            }
            h = h + a.charAt(k) + a.charAt(l) + a.charAt(j) + a.charAt(f);
        }
        return h;
    };

    function b(c) {
        var d = "", a = 0, b = c1 = c2 = 0;
        while (a < c.length) {
            b = c.charCodeAt(a);
            if (b < 128) {
                d += String.fromCharCode(b);
                a++;
            }
            else if (b > 191 && b < 224) {
                c2 = c.charCodeAt(a + 1);
                d += String.fromCharCode((b & 31) << 6 | c2 & 63);
                a += 2;
            }
            else {
                c2 = c.charCodeAt(a + 1);
                c3 = c.charCodeAt(a + 2);
                d += String.fromCharCode((b & 15) << 12 | (c2 & 63) << 6 | c3 & 63);
                a += 3;
            }
        }

        return d;
    };

    function c(c) {
        c = c.replace(/\x0d\x0a/g, "\n");
        for (var b = "", d = 0; d < c.length; d++) {
            var a = c.charCodeAt(d);
            if (a < 128) {
                b += String.fromCharCode(a);
            }
            else if (a > 127 && a < 2048) {
                b += String.fromCharCode(a >> 6 | 192);
                b += String.fromCharCode(a & 63 | 128);
            }
            else {
                b += String.fromCharCode(a >> 12 | 224);
                b += String.fromCharCode(a >> 6 & 63 | 128);
                b += String.fromCharCode(a & 63 | 128);
            }
        }
        return b;
    };

    function base64Decode(input) {
        var output = "";
        var chr1, chr2, chr3 = "";
        var enc1, enc2, enc3, enc4 = "";
        var i = 0;
        var keyStr = a;

        var base64test = /[^A-Za-z0-9\+\/\=]/g;
        if (base64test.exec(input)) {
        }

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        do {
            enc1 = keyStr.indexOf(input.charAt(i++));
            enc2 = keyStr.indexOf(input.charAt(i++));
            enc3 = keyStr.indexOf(input.charAt(i++));
            enc4 = keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

            chr1 = chr2 = chr3 = "";
            enc1 = enc2 = enc3 = enc4 = "";

        } while (i < input.length);

        return unescape(output);
    };

    function SetSignData() {

        kData[0] = 0;

        for (var h = 1; h < kData.length; h++) {
            kData[0] += kData[h];
        }

        isValid = kData[0] >= currReqPts ? true : false;
	currTotalPts = kData[0];

        var j = "";

        cData[0] = currVersion + "," + currBackColor + "," + currImgScale + "," + currW + "," + currH + "," + currTrans + "," + kData[0] + "," + currID + ";";

        for (h = 0; h < cData.length; h++) {
            j += cData[h];
        }

        currSignData.value = base64Encode(j);
    };

    function findPos(obj) {
        var curleft = curtop = 0;

        if (obj.offsetParent) {
            do {
                curleft += obj.offsetLeft;
                curtop += obj.offsetTop;
            } while (obj = obj.offsetParent);
        }
        return [curleft, curtop];
    }

   this.MouseMove = function(e) {

        if (!enabled) {
            return;
        }
        if (!mouseMoved) {
            return;
        }

        if (!isIE) {
            e.preventDefault();
        }

        var ptX = 0, ptY = 0;

        if (isIPhone) {

            var touch = e.targetTouches[0];

            ptX = touch.pageX - iPhoneXPos;
            ptY = touch.pageY - iPhoneYPos;
        }

        else if (isIE || isIENine) {
            ptX = e.x;
            ptY = e.y;
        }
        else if (isMouseDown) {
            ptX = e.layerX;
            ptY = e.layerY;
        }
        else {
            ptX = e.pageX - currentSignContainerObj.offsetLeft;
            ptY = e.pageY - currentSignContainerObj.offsetTop;
        }

        ShowDebug("(" + ptX + "," + ptY + ")");

        if (isMobileIE) {
            fData.push(Math.abs(parseInt(ptX) - parseInt(currentSignContainerObj.offsetLeft)) + "," + Math.abs(parseInt(ptY) - parseInt(currentSignContainerObj.offsetTop)));
        }
        else {
            fData.push(ptX + "," + ptY);
        }

        kData[pointCount]++;

        if (!isIE) {
            currentSignObj.lineTo(ptX, ptY);
            currentSignObj.stroke();
        }
        else {

            if (isMobileIE) {

                var difx = (ptX - lastPositionX);
                var dify = (ptY - lastPositionY);

                var sqDist = (difx * difx + dify * dify);
                var sqPrec = (8 * 8);

                if (sqDist > sqPrec) {

                    if (graphics != null && graphics != 'undefined') {
                        try {
                            graphics.setStroke(currPenSize);
                            graphics.setColor(currPenColor);

                            graphics.drawLine(lastPositionX, lastPositionY, ptX, ptY);
                            graphics.paint();
                        }
                        catch (mme) {
                            alert("Drawing error: " + mme.description);
                        }
                    }
                    else {
                        ShowDebug("Graphics object NULL");
                    }

                    lastPositionX = ptX;
                    lastPositionY = ptY;
                }

            }
            else {
                currentSignObj.pathCoordString += " " + ptX + "," + ptY;
                var g = document.getElementById(currID + "_l_" + pointCount);
                if (g) {
                    var i = g.firstChild;

                    if (i) {
                        try {
                            i.setAttribute("v", currentSignObj.pathCoordString + " e");
                        }
                        catch (j) { }
                        if (dcMode && kData[pointCount] % 8 == 0) {
                            currentSignObj.innerHTML = currentSignObj.innerHTML;
                        }
                    }
                }
            }
        }

    };

    this.MouseUp = function(e) {

        if (!enabled) {
            return;
        }

        ShowDebug("Mouse up");

        mouseMoved = false;

        cData[pointCount] = " " + fData.join(" ") + ";";

        SetSignData();

        if (kData[0] < currReqPts) {
            currSignStatus.innerHTML = currErrMsg;
        }
        else {
            currSignStatus.innerHTML = "";
        }

        if (!isIE) {
            currentSignObj.closePath();
            currentSignObj.restore();
        }
        else {
            currentSignObj.innerHTML = currentSignObj.innerHTML;
        }

        if (isIPhone) {
            iPhoneXPos = 0;
            iPhoneYPos = 0;
        }

    };

    this.MouseDown = function(e) {

        if (!enabled) {
            return false;
        }

        if (!isIE) {
            e.preventDefault();
        }

        ShowDebug("Mouse down");

        mouseMoved = true;
        var ptX, ptY;

        if (isIPhone) {

            var touch = e.targetTouches[0];

            if (iPhoneXPos == 0) {
                var cords = findPos(currentSignContainerObj);
                iPhoneXPos = cords[0];
                iPhoneYPos = cords[1];
            }

            ptX = touch.pageX - iPhoneXPos;
            ptY = touch.pageY - iPhoneYPos;
        }
        else {
            isMouseDown = e.layerX ? true : false;
            if (isIE || isIENine) {
                ptX = e.x;
                ptY = e.y;

                if (iemodalfix) {

                    var mOffSet = $('#' + currentSignContainerObj.id).offset();

                    if (isIENine) {
                        ptX = e.pageX - mOffSet.left;
                        ptY = e.pageY - mOffSet.top;
                    }
                    else {
                        ptX = e.x - mOffSet.left + document.documentElement.scrollLeft;
                        ptY = e.y - mOffSet.top + document.documentElement.scrollTop;

                        if (getInternetExplorerVersion() < 8.0) {
                            ptX = e.x;
                            ptY = e.y;
                        }
                    }
                }
            }
            else if (isMouseDown) {
                ptX = e.layerX;
                ptY = e.layerY;
            }
            else {
                ptX = e.pageX - currentSignContainerObj.offsetLeft;
                ptY = e.pageY - currentSignContainerObj.offsetTop;
            }
        }

        ShowDebug("Down (" + ptX + "," + ptY + ")");

        pointCount++;
        kData[pointCount] = 0;
        fData.length = 0;
        fData[0] = currPenSize + "," + currPenColor;

        if (isMobileIE) {
            fData.push(Math.abs(parseInt(ptX) - parseInt(currentSignContainerObj.offsetLeft)) + "," + Math.abs(parseInt(ptY) - parseInt(currentSignContainerObj.offsetTop)));
        }
        else {
            fData.push(ptX + "," + ptY);
        }


        if (isIE) {
            if (isMobileIE) {

                lastPositionX = ptX;
                lastPositionY = ptY;

            }
            else {
                var w = '<SuperSignature:stroke weight="' + currPenSize + '" color="' + currPenColor + '" />',
                    t = '"m' + ptX + "," + ptY + " l" + ptX + "," + ptY, v;
                v = '<SuperSignature:shape id="' + currID + "_l_" + pointCount + '" style="position: absolute; left:0px; top:0px; width:' + currW + "px; height: " + currH + 'px;" coordsize="' + currW + "," + currH + '"><SuperSignature:path v=' + t + ' e" /><SuperSignature:fill on="false" />' + w + "</SuperSignature:shape>";
                currentSignObj.pathCoordString = t;
                currentSignObj.insertAdjacentHTML("beforeEnd", v);
            }
        }
        else {
            currentSignObj.save();
            currentSignObj.beginPath();
            currentSignObj.lineJoin = "round";
            currentSignObj.lineCap = "round";
            currentSignObj.strokeStyle = currPenColor;
            currentSignObj.lineWidth = currPenSize;
            currentSignObj.moveTo(ptX, ptY);
        }

        return false;

    };

    this.ResetClick = function(e) {

 	if (!enabled) {
            return;
        }

        if (!isMobileIE) {
            document.getElementById(currID).style.borderColor = currBorderColor;
        }

        if (isIE) {
            currentSignObj.innerHTML = "";

            if (isMobileIE) {
                lastPositionX = 0;
                lastPositionY = 0;

                if (graphics != null && graphics != 'undefined') {
                    graphics.clear();
                    graphics.paint();
                }
            }
        }
        else {
            currentSignObj.clearRect(0, 0, currW, currH);
            if (currBackUrl.length > 0) {
            } else {
                SignatureBackColor(currID, currBackColor);
            }
        }

        cData.splice(1, pointCount);
        kData.splice(1, pointCount);

        SetSignData();

        pointCount = 0;
        htm = "";

    };

    this.Init = function() {

        this.CheckCompatibility();

        if (this.IsCompatible) {

            currSignDebug = document.getElementById(this.SignObject + "_Debug");

            currentSignObj = document.getElementById(this.SignObject);
            currentSignContainerObj = document.getElementById(this.SignObject + "_Container");


            if (currentSignObj.addEventListener) {
                ShowDebug("addEventListener supported");
            }
            else if (currentSignObj.attachEvent) {
                ShowDebug("attachEvent supported");
            }
            else {
                ShowDebug("Mouse events are not supported");
                return;
            }

            this.enabled = this.Enabled;
            this.mouseMoved = false;
            this.isMouseDown = false;


            if (currentSignObj != null && currentSignObj != 'undefined') {

                ShowDebug("Objects OK");

                if (isIE && !isMobileIE) {
                    dcMode = document.documentMode ? document.documentMode >= 8 : false;
                }

                if (isMobileIE) {
                    ShowDebug("Mobile IE");
                }

                if (isOperaMini) {
                    ShowDebug("Opera Mini");
                }

                if (!this.Visible) {
                    ShowDebug("Control hidden");
                    currentSignObj.style.display = "none";
                    return;
                }

                kData[0] = 0;
                cData[0] = currVersion + "," + currBackColor + "," + currImgScale + "," + currW + "," + currH + "," + currTrans + "," + kData[0] + "," + currID + ";";

                if (this.ApplyStyle) {

                    ShowDebug("Setting up style");

                    try {
                        if (isMobileIE) {
                            currentSignContainerObj.style.borderWidth = this.BorderWidth;
                            currentSignContainerObj.style.borderStyle = this.BorderStyle;
                            currentSignContainerObj.style.borderColor = this.BorderColor;
                            currentSignContainerObj.style.backgroundColor = this.BackColor;

                            currentSignContainerObj.style.zIndex = this.SignzIndex;
                            currentSignContainerObj.style.cursor = "url('" + this.PenCursor + "'), pointer";

                            if(this.BackImageUrl.length > 0)
                            {
                              currentSignContainerObj.style.backgroundImage = 'url("' + this.BackImageUrl + '")';
                            }
                        }
                        else {

                            currentSignObj.style.borderWidth = this.BorderWidth;
                            currentSignObj.style.borderStyle = this.BorderStyle;
                            currentSignObj.style.borderColor = this.BorderColor;
                            currentSignObj.style.backgroundColor = this.BackColor;

                            currentSignObj.style.zIndex = this.SignzIndex;
                            currentSignObj.style.cursor = "url('" + this.PenCursor + "'), pointer";

                            if(this.BackImageUrl.length > 0)
                            {
                              currentSignObj.style.backgroundImage = 'url("' + this.BackImageUrl + '")';
                            }


                            if (this.CssClass != "") {
                                currentSignObj.className = this.CssClass;
                            }

                            currentSignObj.style.width = this.SignWidth + "px";
                            currentSignObj.style.height = this.SignHeight + "px";
                            currentSignObj.style.position = "relative";

                            if (currentSignObj.style.cursor == "auto") {
                                currentSignObj.style.cursor = "url('" + this.PenCursor + "'), hand";
                            }
                        }

                        ShowDebug("Style OK");
                    }
                    catch (exs) {
                        ShowDebug("Style Error : " + exs.description);
                    }


                }


                var htmlContent = '<div id="' + this.SignObject + '_toolbar" style="height:20px;width:' + this.SignWidth + "px; background-color:" + this.SignToolbarBgColor + ';"><span style="float:right;"><img src="' + this.ClearImage + '" id="' + this.SignObject + '_resetbutton" style="cursor:pointer;width:24px;height:24px;float:right;" alt="Clear Signature" />';

                htmlContent += '<span id="' + this.SignObject + '_status" style="color:blue;font-family:verdana;font-size:12px;float:right;margin-right:5px;">' + this.StartMessage + "</span>";
                htmlContent += document.getElementById(this.SignObject + "_data") == null ? '<input type="hidden" id="' + this.SignObject + '_data" name="' + this.SignObject + '_data" value="">' : "";
                htmlContent += "</span></div>";

                ShowDebug("Setting up tools");

                var newdiv = document.createElement('div');
                currentSignContainerObj.appendChild(newdiv);
                newdiv.innerHTML = htmlContent;

                ShowDebug("Attaching mouse out");

                if (isIE) {
                    this.XBrowserAddHandler(currentSignObj, "mouseleave", this.MouseUp);
                }
                else {
                    this.XBrowserAddHandler(currentSignObj, "mouseout", this.MouseUp);
                }

                pointCount = 0;

                var mouseDownEvent = "mousedown", mouseUpEvent = "mouseup", mouseMoveEvent = "mousemove";

                isIPhone = (new RegExp("iPhone", "i")).test(navigator.userAgent) || (new RegExp("iPad", "i")).test(navigator.userAgent) || (new RegExp("Android", "i")).test(navigator.userAgent);

                if (isIPhone) {
                    ShowDebug("Found iPhone");

                    mouseDownEvent = "touchstart";
                    mouseUpEvent = "touchend";
                    mouseMoveEvent = "touchmove";
                }

                if (!isIE) {
                    currentSignObj = document.getElementById(this.SignObject).getContext("2d");
                    currentSignObj.width = this.SignWidth;
                    currentSignObj.height = this.SignHeight;
                }

                currSignStatus = document.getElementById(this.SignObject + "_status");
                currSignToolbar = document.getElementById(this.SignObject + "_toolbar");
                currSignData = document.getElementById(this.SignObject + "_data");

                var objResetButton = document.getElementById(this.SignObject + "_resetbutton");
                if (null != objResetButton) {
                    this.XBrowserAddHandler(objResetButton, "click", this.ResetClick);
                }

                ShowDebug("Attaching events");

                this.XBrowserAddHandler(currentSignContainerObj, "contextmenu", this.ReturnFalse);
                this.XBrowserAddHandler(currentSignContainerObj, "selectstart", this.ReturnFalse);
                this.XBrowserAddHandler(currentSignContainerObj, "dragstart", this.ReturnFalse);

                this.XBrowserAddHandler(currentSignObj, "contextmenu", this.ReturnFalse);
                this.XBrowserAddHandler(currentSignObj, "selectstart", this.ReturnFalse);
                this.XBrowserAddHandler(currentSignObj, "dragstart", this.ReturnFalse);

                this.DisableSelection();

                if (isIE) {
                    this.XBrowserAddHandler(currentSignObj, mouseDownEvent, this.MouseDown);
                    this.XBrowserAddHandler(currentSignObj, mouseUpEvent, this.MouseUp);
                    this.XBrowserAddHandler(currentSignObj, mouseMoveEvent, this.MouseMove);
                }
                else {
                    this.XBrowserAddHandler(currentSignContainerObj, mouseDownEvent, this.MouseDown);
                    this.XBrowserAddHandler(currentSignContainerObj, mouseUpEvent, this.MouseUp);
                    this.XBrowserAddHandler(currentSignContainerObj, mouseMoveEvent, this.MouseMove);
                }

                ShowDebug("Ready");

            }
            else {
                ShowDebug("Error initializing signature control");
            }
        }
    };

};
