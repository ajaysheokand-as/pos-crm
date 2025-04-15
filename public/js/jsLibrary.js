var globalXML = null;
var C = ['#FFFFFF', '#E1AA79', '#E77817', '#F8ECE2', '#800000', '#FF0000', '#FF0000', '#AAAAAA', '#C5C5C5'];
var UA = navigator.userAgent ? navigator.userAgent : navigator.appName
var M = {toString: function () {
        return 'Master Object';
    }, W: window, promptExit: function (m) {
        if (m == undefined || !m || m.length == 0)
            m = "Ar" + "e You S" + "ure to Lea" + "ve this We" + "bsite";
        M.W.onbeforeunload = function () {
            return m;
        };
    }, unpromptExit: function () {
        /*M.detachEvent(M.W,'beforeunload',M.W.beforeunload);*/M.W.onbeforeunload = function () {};
    }, FB: !(("console" in window) && ("firebug" in console)), FRAME: function (n) {
        return M.W.frames[n];
    }, DOC: window.document,
    StartUp: new Date().toString(),
    SW: window.screen.width,
    SH: window.screen.height,
    UA: {
        name: UA,
        IE: UA.match(/msie/ig) != null,
        IE6: UA.match(/msie\s6\.0/ig) != null,
        IE7: UA.match(/msie\s7\.0/ig) != null,
        IE8: UA.match(/msie\s8\.0/ig) != null,
        IE9: UA.match(/msie\s9\.0/ig) != null,
        FF: UA.match(/(firefox|minefield|namoroka)/ig) != null,
        OP: UA.match(/(opera|presto)/ig) != null,
        SF: UA.match(/(chrome|safari)/ig) != null,
        KK: UA.match(/(konqueror|khtml\/)/ig) != null,
        OS: UA.match(/windows/ig) != null,
        LX: UA.match(/linux/ig) != null,
        MK: UA.match(/mac/ig) != null,
        offset: ((this.name.indexOf("Mac") != -1 || this.name.indexOf("Gecko") != -1 || this.name.indexOf("Netscape") != -1) ? true : false),
        NNArgs: "alwaysLowered=0,alwaysRaised=1,copyhistory=0,dependent=1,directories=0,hotkeys=0,location=0,menubar=0,resizable=0,screenX,screenY,scrollbars=1,status=0,titlebar=0,toolbar=0,z-lock=1",
        IEArgs: "channelMode=0,directories=0,fullscreen=1,location=0,menubar,resizable=1,scrollbars=1,status=0,toolbar=0"
    },
    EVENT: {
        onLoad: function (elem) {
            return M.byId(elem).onload;
        },
        onClick: function (elem) {
            return M.byId(elem).onclick;
        },
        onDblClick: function (elem) {
            return M.byId(elem).ondblclick;
        }
    },
    getRequestUrl: function () {
        return document.URL.toString().replace(new RegExp("^" + document.location.protocol + "//" + document.domain), "");
    },
    MIME: {image: ['#', 'png', 'gif', 'jpg', 'jpeg'], doc: ['#', 'doc', 'docx', 'pdf', 'xls', 'xlsx'], toString: function () {
            var x = "";
            var m = this.image;
            for (var i = 1; i < m.length; i++)
                x += m[i] + ',';
            m = this.doc;
            for (var i = 1; i < m.length; i++)
                x += m[i] + ',';
            return x.substring(0, x.length - 1);
        }},
    trim: function (str) {
        if (!str || this.isEmpty(str) || typeof (str) != "string")
            return null;
        return str.replace(/(^\s+|\s+$)/g, '');
    },
    textCounter: function (src, target, Max) {
        target = this.byId(target);
        src = this.byId(target);
        if (!src && !target && src.type == undefined)
            return;
        if (Max) {
            try {
                Max = parseInt(Max);
                target.innerHTML = Max - src.value.toString().length;
                return;
            } catch (e) {
            }
        }
        target.innerHTML = src.value.toString().length;
    },
    tmpText: function (src, txt) {
        src = M.byId(src);
        if (!src || src.type == undefined || src.type != "text" || txt == undefined || !txt || !txt.length) {
            alert("Error! to set temporary text");
            return;
        }
        if (src.tmptxt == undefined)
            src.tmptxt = txt;
        if (src.value == txt) {
            src.value = "";
        }
        M.attachEvent(src, "focus", function () {
            if (!M.trim(src.value) || M.trim(src.value) == txt) {
                src.value = "";
                src.style.color = "#000";
            }
        });
        M.attachEvent(src, "blur", function () {
            if (!M.trim(src.value))
                src.value = txt;
            src.style.color = "#666";
        })
    },
    checkEmailById: function (target, XML)
    {
        if (XML)
        {
            XML = XML.documentElement;
            XML = XML.firstChild;
            if (XML != null && XML != undefined)
            {
                if (XML.nodeValue == 'valid')
                {
                    M.byId(target).innerHTML = '<img src="support/img/yes.gif" style="width:20px; height:20px;">';
                } else
                {
                    M.byId(target).innerHTML = XML.nodeValue;
                }
                if (XML.nodeValue == 'Duplicate') {
                    M.byId('emailID').style.backgroundColor = C[1];
                    M.byId('emailID').focus();
                }
            } else
            {
                alert('some error occur');
            }
        }
    },
    populate2Select: function (target, XML, append, sepTxt) {
        if (XML) {
            target.disabled = true;
            XML = XML.documentElement;
            XML = XML.firstChild;
            if (XML != null && XML != undefined) {
                target.disabled = false;
                if (append == undefined || !append) {
                    target.options.length = 0;
                    target.options[target.options.length] = new Option("Select", "", false, true);
                } else {
                    var optg = M.create("optgroup");
                    optg.label = sepTxt != undefined && M.trim(sepTxt).length ? sepTxt : "-----------------";
                    target.appendChild(optg);
                }

                do {
                    target.options[target.options.length] = new Option(XML.attributes.item(1).value, XML.attributes.item(0).value, false, false);
                } while ((XML = XML.nextSibling));
                target.disabled = false;
            } else
                target.options[target.options.length] = new Option("No Records", 0, false, true);
        }
    },
    AJAX: {
        /* Deprecated */
        getHTTPRequestObject: function () {
            var XML_HTTP_OBJECT = null;
            if (window.ActiveXObject) {
                try {
                    XML_HTTP_OBJECT = new window.ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {
                    XML_HTTP_OBJECT = "Miscrosoft Request Object Can't be Create.";
                }
            } else {
                try {
                    XML_HTTP_OBJECT = new XMLHttpRequest();
                } catch (e) {
                    XML_HTTP_OBJECT = "XML Request Object Can't be Create.";
                }
            }
            return XML_HTTP_OBJECT;
        },
        XHR: function () {
            var XML_HTTP_OBJECT = null;
            if (window.ActiveXObject) {
                try {
                    XML_HTTP_OBJECT = new window.ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {
                    XML_HTTP_OBJECT = "Miscrosoft Request Object Can't be Create.";
                }
            } else {
                try {
                    XML_HTTP_OBJECT = new XMLHttpRequest();
                } catch (e) {
                    XML_HTTP_OBJECT = "XML Request Object Can't be Create.";
                }
            }
            return XML_HTTP_OBJECT;
        },
        methods: ['GET', 'POST', 'HEAD', 'PUT'],
        request: function (res) {
            if (!res)
                return;
            if (res.target == undefined || M.trim(res.target) == '') {
                alert("Target can't be null");
                return;
            }
            var AX = this.XHR();
            if (!AX)
                return;
            if (AX.readyState == 4 || AX.readyState == 0) {
                var prevClass = null;
                try {
                    if (res.loader != undefined) {
                        prevClass = res.loader.className;
                        if (res.styleClass != undefined)
                            M.FX.addClass(M.byId(res.loader), res.styleClass);
                        else
                            M.byId(res.loader).style.background = "url(../support/img_rp/rp-loader.gif) no-repeat 90% 0px";
                    }
                    var send = false;
                    var toSend = "";

                    if (res.encodedQuery != undefined) {
                        toSend = validateAllAjaxScanning(res.encodedQuery);
                    }
                    if (res.query != undefined) {
                        toSend = validateAllAjaxScanning(res.query);
                        toSend = encodeURI(toSend);
                    }

                    if (toSend)
                        send = true

                    if (res.method != undefined && M.search(res.method.toUpperCase(), this.methods) >= 0)
                    {
                        res.method = res.method.toUpperCase();
                        if (res.method == this.methods[0])
                        {
                            send = false;
                            AX.open(res.method, res.target + "?" + toSend, true);
                        } else
                        {
                            AX.open(res.method, res.target, true);
                        }
                    } else
                    {
                        AX.open(this.methods[1], res.target, true);
                    }
                    AX.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    AX.setRequestHeader("Encoding", "application/x-www-form-urlencoded");
                    AX.setRequestHeader("Connection", "keep-alive");
                    AX.setRequestHeader("Content-Length", toSend.length);
                    AX.onreadystatechange = function () {
                        if (AX.readyState == 4) {
                            if (AX.status == 200) {
                                var RES = null;
                                var ct = AX.getResponseHeader("Content-Type");
                                ct = ct.substring(0, ct.indexOf(';'));
                                if (ct == "text/plain" || ct == "text/html")
                                    RES = AX.responseText;
                                else if (ct == "text/xml" || ct == "application/xml")
                                    RES = AX.responseXML;
                                else if (ct == "application/json")
                                    RES = AX.responseText;
                                if (res.onSuccess != undefined && typeof (res.onSuccess) == "function")
                                    res.onSuccess(RES);
                                if (res.loader != undefined && res.styleClass != undefined)
                                    M.FX.removeClass(M.byId(res.loader), res.styleClass)
                                if (res.loader != undefined)
                                    res.loader.style.backgroundImage = "none";
                            }
                        }
                    };
                    AX.send(send ? toSend : null);
                } catch (e) {
                    if (res.loader != undefined && res.styleClass != undefined)
                        M.FX.removeClass(M.byId(res.loader), res.styleClass)
                    if (res.loader != undefined)
                        M.byId(res.loader).className = prevClass

                    if (res.onFailed != undefined && typeof (res.onFailed) == "function")
                        res.onFailed("Error: " + e.message + "\nOn Line: " + e.lineNumber + "\nOn Column: " + e.columnNumber);
                }
            }
        },
        JSONParse: function (a) {
            try {
                if (a == undefined)
                    return null;
                a = a.toString();
                if (M.UA.IE6 || M.UA.IE7)
                    return !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(a.replace(/"(\\.|[^"\\])*"/g, ''))) && eval('(' + a + ')')
                else
                    return JSON.parse(a)
            } catch (e) {
                return e
            }
        }
    },
    remove: function (theElem) {
        theElem = this.byId(theElem);
        theElem.parentNode.removeChild(theElem);
    },
    randomHexColor: function () {
        return this.RGB2Hex(Math.floor(Math.random() * 255), Math.floor(Math.random() * 255), Math.floor(Math.random() * 255));
    },
    RGB2Hex: function (r, g, b) {
        return '#' + this.byte2Hex(r) + this.byte2Hex(g) + this.byte2Hex(b);
    },
    byte2Hex: function (n) {
        return String("0123456789ABCDEF".substr((n >> 4) & 0x0F, 1)) + "0123456789ABCDEF".substr(n & 0x0F, 1);
    },
    //formatAsCurrency:function(num,dec){num=num.toString().replace(/\$|\,/g,'');if(isNaN(num))num="0";sign=(num==(num=Math.abs(num)));num=Math.floor(num*100+0.50000000001);cents=num%100;num=Math.floor(num/100).toString();if(cents<10)cents="0"+cents;for(var i=0;i<Math.floor((num.length-(1+i))/3);i++)num=num.substring(0,num.length-(4*i+3))+','+num.substring(num.length-(4*i+3));return (((sign)?'':'-')+"Rs. "+num+(dec?'.'+cents:''));},
    unformatMoney: function (str) {
        if (str != undefined && M.trim(str))
            return str.replace(/[,\s+]/g, '');
        if (!this.value)
            return '';
        this.value = this.value.replace(/[,\s+]/g, '');
        return 1;
    },
    formatAsMoney: function (str) {
        if (str != undefined && M.trim(str))
            return M.moneyFormat(str);
        if (!this.value)
            return false;
        this.value = M.moneyFormat(this.value);
        return true;
    },
    formatAsMoneyStr: function (str) {
        if (str != undefined && M.trim(str))
            return M.moneyFormat(str);
        return;
    },
    moneyFormatNeg: function (str) {
        if (str == undefined)
            return;
        if (str.match(/^-/)) {
            str = M.trim(str);
            str = str.replace(/-/g, "");
            if (str != undefined && M.trim(str))
                return "-" + M.moneyFormat(str);
        } else {
            return M.moneyFormat(str);
        }
    },
    moneyFormat: function (str) {
        str += '';
        if (!str || !str.length)
            return null;
        str = str.split('.');
        var dP = '';
        if (str.length > 1 && str[1].match(/^[0-9]+$/)) {
            dP = '.' + str[1];
        }
        str = str[0];
        str = str.replace(/^[0]{1,20}/, '');
        str = str.replace(/[,\s+]/g, '');
        var tmp = "";
        var tmpcount = 0;
        var hsep = true;
        var prev = 0;

        for (prev = str.length - 1; prev >= 0; prev--) {
            tmp += str.substr(prev, 1);
            tmpcount++;
            if (hsep && tmpcount == 3 && prev) {
                tmp += ",";
                hsep = false;
                tmpcount = 0;
            } else if (!hsep && tmpcount == 2 && prev) {
                tmp += ",";
                tmpcount = 0;
            }
        }
        str = "";
        for (prev = tmp.length - 1; prev >= 0; prev--)
            str += tmp.substr(prev, 1);
        return str + dP
    },
    format: function (str) {
        if (!str)
            return null;
        str = this.trim(str);
        var tmp = '';
        var count = 0;
        var f = 3;
        for (var nxt = str.length - 1; nxt >= 0; nxt--) {
            count++;
            tmp = str[nxt] + tmp;
            if (f == count) {
                tmp = "," + tmp;
                f = 2;
            }
        }
        return 'Rs. ' + str;
    },
    decimal: function (value, decimals) {
        return Math.round(value * Math.pow(10, decimals)) / Math.pow(10, decimals)
    },
    getCookie: function (cn) {
        var ar = {};
        var t = "";
        var C = document.cookie.split('; ');
        for (var nxt = 0; nxt < C.length; nxt++) {
            var s = M.trim(C[nxt].substring(0, C[nxt].indexOf('=')));
            var v = C[nxt].substr(C[nxt].indexOf('=') + 1);
            if (cn && s == cn)
                return unescape(v);
        }
        if (cn)
            return null;
        return unescape(ar);
    },
    setCookieOld: function (name, value) {
        var expire = new Date();
        var nowPlusOneWeek = expire.getTime() + (7 * 24 * 60 * 60 * 1000);
        expire.setTime(nowPlusOneWeek);
        document.cookie = name + "=" + value + ";expires=" + expire.toGMTString() + ";";
    },
    setCookie: function (name, value, expire) {
        if (expire == undefined || isNaN(expire))
            expire = 7;
        document.cookie = name + "=" + escape(value) + "; expires=" + new Date(new Date().getTime() + expire * 24 * 60 * 60 * 1000).toGMTString() + "; domain=" + document.domain + "; path=/; secure;";
    },
    isSpecialCharsWithN: function (str) {
        return str.match(/\<|\>|\"|\'|\~|\@|\#|\$|\^|\*|\(|\)|\_|\+|\=|\:|\?|\"|\/|\!|\%|\;|\(|\)|\&|\+|\-|\[|\]|\[0-9]/g);
    },
    isSpecialChars: function (str) {
        return str.match(/\<|\>|\"|\'|\~|\@|\#|\$|\^|\*|\(|\)|\_|\+|\=|\:|\?|\"|\/|\!|\%|\;|\(|\)|\&|\+|\-|\[|\]/g);
    },
    isSpecialCharsWithoutAnd: function (str) {
        return str.match(/\<|\>|\"|\'|\~|\@|\#|\$|\^|\*|\(|\)|\_|\+|\=|\:|\?|\"|\/|\!|\%|\;|\(|\)|\+|\[|\]/g);
    },
    isAlpha: function (str) {
        if (str == undefined)
            return null;
        return str.match(/^[a-zA-Z]+$/);
    },
    isDocumentProofNo: function (evO) { // address line
        evO = M.objEvent(evO);

        var keyCode = null;
        if ((evO.e.keyCode == 0 || evO.e.keyCode == 229)) { //for android chrome keycode fix
            /**
             *  I am not able to get current value
             **/
            //keyCode = M.getKeyCode(evO.src.value);
            //keyCode = M.getKeyCode(M.byId(evO.src.id).value);

            return true;
        } else {
            keyCode = evO.e.keyCode
        }

        if (evO.e.shiftKey == true) {
            var r = ((evO.e.keyCode >= 65 && evO.e.keyCode <= 90) || (evO.e.keyCode >= 45 && evO.e.keyCode <= 47));
            if (r)
                return true;
            else
                return false;
        }
        if (!evO)
            return false;
        if (evO.e.shiftKey && evO.e.keyCode == 9)
            return true;

        var r = (evO.e.keyCode == 173 || evO.e.keyCode == 220);

        if (r)
            return true;

        var r = (evO.e.keyCode >= 48 && evO.e.keyCode <= 57);//0-9
        if (r)
            return true;

        r = (evO.e.keyCode >= 96 && evO.e.keyCode <= 105);
        if (r)
            return true;
        r = (evO.e.keyCode >= 45 && evO.e.keyCode <= 47);
        if (r)
            return true;
        var r = (evO.e.keyCode >= 65 && evO.e.keyCode <= 90);//A-Z a-z
        if (r)
            return true;
        var is = M.search(evO.e.keyCode, M.keys);
        return (is >= 0);
    },
    isAlphaSpace: function (str) { // this is for the enters middle name in first name or last name
        if (str == undefined)
            return null;
        var length = str.length;
        for (var i = 0; i < length; i++) {
            if (str[0] == ' ') {
                return false;
                break;
            } else if (str[length - 1] == ' ') {
                return false;
                break;
            } else {
                return str.match(/^[a-zA-Z ]+$/)
            }
        }

    },
    isAddressLine: function (evO) { // address line
        evO = M.objEvent(evO);
        var keyCode = null;
        if ((evO.e.keyCode == 0 || evO.e.keyCode == 229)) { //for android chrome keycode fix
            /**
             *  I am not able to get current value
             **/
            //keyCode = M.getKeyCode(evO.src.value);
            //keyCode = M.getKeyCode(M.byId(evO.src.id).value);

            return true;
        } else {
            keyCode = evO.e.keyCode
        }

        if (evO.e.shiftKey == true) {
            var r = ((evO.e.keyCode >= 65 && evO.e.keyCode <= 90) || (evO.e.keyCode >= 45 && evO.e.keyCode <= 47));
            if (r)
                return true;
            else
                return false;
        }
        if (!evO)
            return false;
        if (evO.e.shiftKey && evO.e.keyCode == 9)
            return true;
        //if(evO.e.shiftKey||evO.e.keyCode==16)return true;
        //console.log(evO.e.keyCode);
        var r = (evO.e.keyCode == 32 || evO.e.keyCode == 189 || evO.e.keyCode == 173 || evO.e.keyCode == 92 || evO.e.keyCode == 44
                || evO.e.keyCode == 188 || evO.e.keyCode == 190 || evO.e.keyCode == 191 || evO.e.keyCode == 111 || evO.e.keyCode == 109);
        if (r)
            return true;
        var r = (evO.e.keyCode >= 48 && evO.e.keyCode <= 57);
        if (r)
            return true;
        r = (evO.e.keyCode >= 96 && evO.e.keyCode <= 105);
        if (r)
            return true;
        r = (evO.e.keyCode >= 45 && evO.e.keyCode <= 47);
        if (r)
            return true;
        var r = (evO.e.keyCode >= 65 && evO.e.keyCode <= 90);
        if (r)
            return true;
        var is = M.search(evO.e.keyCode, M.keys);
        return (is >= 0);
    },
    isAlphaNumericWithSp: function (evO) { // FOR ALPHANUMERIC AND SPACE #,/,.,-
        evO = M.objEvent(evO);
        if (evO.e.shiftKey && evO.e.keyCode == 51)
            return true // FOR #
        if (evO.e.shiftKey == true) {
            var r = ((evO.e.keyCode >= 65 && evO.e.keyCode <= 90));
            if (r)
                return true;
            else
                return false;
        }
        if (!evO)
            return false;

        if (evO.e.shiftKey && evO.e.keyCode == 9)
            return true
        if (evO.e.shiftKey || evO.e.keyCode == 16)
            return false;
        var r = (evO.e.keyCode >= 48 && evO.e.keyCode <= 57)
        if (r)
            return true
        r = (evO.e.keyCode >= 96 && evO.e.keyCode <= 105)
        if (r)
            return true
        r = (evO.e.keyCode >= 37 && evO.e.keyCode <= 40)
        if (r)
            return true
        var r = (evO.e.keyCode == 32 || evO.e.keyCode == 127 || evO.e.keyCode == 191 || evO.e.keyCode == 190 || evO.e.keyCode == 173); //(space=>32,/=>191,.=>190,-=>173); 
        if (r)
            return true;
        var r = (evO.e.keyCode >= 65 && evO.e.keyCode <= 90)
        if (r)
            return true
        var is = M.search(evO.e.keyCode, M.keys);
        return (is >= 0)
    },
    isPAN: function (str) {
        if (str == undefined)
            return false;
        str = M.trim(str);
        var m = str.match(/^([A-Za-z]{3})+(P|C|F|H|F|A|T|B|J|G)+([A-Za-z]{1})+([0-9]{4})+([A-Za-z]{1})$/);
        return (m && m[0].length == 10);
    },
    isPANSalaried: function (str) {
        if (str == undefined)
            return false;
        str = M.trim(str);
        var m = str.match(/^([A-Za-z]{3})+(P)+([A-Za-z]{1})+([0-9]{4})+([A-Za-z]{1})$/);
        return (m && m[0].length == 10);
    },
    isPIN: function (str) {
        if (str == undefined)
            return null;
        return str.match(/^[0-9]{6}$/)
    },
    IsNumeric: function (sText) {
        var ValidChars = "0123456789.";
        var IsNumber = true;
        var Char;
        for (i = 0; i < sText.length && IsNumber == true; i++) {
            Char = sText.charAt(i);
            if (ValidChars.indexOf(Char) == -1) {
                IsNumber = false;
            }
        }
        return IsNumber;
    },
    isDigitOnly: function (sText) {
        return sText.match(/^[\d\.]/)
    },
    isEmail: function (mailid) {
        mailid = mailid.toString();
        return mailid.match(/^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/);
    },
    //isMobile:function(cont){cont=cont.toString();return cont.match(/^((\+){0,1}91(\s){0,1}(\-){0,1}(\s){0,1}){0,1}\d{10,12}$/);},
    isMobile: function (cont) {
        cont = cont.toString();
        if (cont.length < 10) {
            return null;
        } else if (cont < '6000000000' || cont > '9999999999') {
            return null;
        } else {
            return cont;
        }
    },
    isPhone: function (phone) {
        phone = phone.toString();
        return phone.match(/^(((\+){0,1}91(\-){1})\d{2,5}(\-){1}\d{6,8})|(\d{2,5}(\-){1}\d{6,8})$/);
    },
    isIPAddress: function (ip) {
        var ary = ip.split(".");
        var ip = true;
        for (var i in ary) {
            ip = (!ary[i].match(/^\d{1,3}$/) || (Number(ary[i]) > 255)) ? false : ip;
        }
        ip = (ary.length != 4) ? false : ip;
        return ip;
    },
    isDate: function (date, byToday) {
        if (date == undefined || typeof (date) != "string")
            return false;
        if (date.match(/^\d{2}\-\d{2}\-\d{4}$/)) {
            date = date.split("-");
            //alert(date)
            var days = M.daysInMonth(date[1] - 1, date[2]);
            var R = (date[0] > 0 && date[0] <= days && date[1] > 0 && date[1] <= 12)
            if (R && byToday != undefined) {
                return M.countAge(date[0], date[1], date[2])
            }
            return R;
        }
        return false;
    },
    isDateReverse: function (date, byToday) {
        if (date == undefined || typeof (date) != "string")
            return false;
        if (date.match(/^\d{4}\-\d{2}\-\d{2}$/)) {
            date = date.split("-");
            //alert(date)
            var days = M.daysInMonth(date[1] - 1, date[0]);
            var R = (date[2] > 0 && date[2] <= days && date[1] > 0 && date[1] <= 12)
            if (R && byToday != undefined) {
                return M.countAge(date[2], date[1], date[0])
            }
            return R;
        }
        return false;
    },
    isFeb: function (date) {
        if (M.isDate(date).length)
            date = date.split('-');
        return((((date[2] % 4 == 0) && ((!(date[2] % 100 == 0)) || (date[2] % 400 == 0))) ? 29 : 28) == date[0]);
    },
    daysInMonth: function (iMonth, iYear) {
        return 32 - (new Date(iYear, iMonth, 32).getDate());
    },
    daysWillBe: function (trg, Year, Month) {
        if (Year.selectedIndex && Month.selectedIndex) {
            var days = parseInt(M.daysInMonth(M.getSelectValue(Month) - 1, M.getSelectValue(Year)));
            trg.options.length = 1;
            for (var d = 1; d <= days; d++) {
                trg.options[trg.options.length] = new Option(d, d, false, false);
            }
        }
    },
    countAge: function (a1, a2, a3) {
        if (a1 == undefined)
            return 0;
        var day = 0, month = 0, year = 0;
        if (a2 != undefined && a3 != undefined) {
            day = parseFloat(0 + a1);
            day = (day < 10 ? "0" + day.toString() : day);
            month = parseFloat(0 + a2);
            month = (month < 10 ? "0" + month.toString() : month);
            year = a3;
            if (!M.isDate(day + "-" + month + "-" + year))
                return 0;
        } else if (typeof (a1) == "string" && a1.match(/\d{4}-\d{2}-\d{2}/)) {
            a1 = a1.split("-")
            year = a1[0]
            month = a1[1]
            day = a1[2]
            if (!M.isDate(day + "-" + month + "-" + year))
                return 0;
        } else if (typeof (a1) == "string" && a1.match(/\d{2}-\d{2}-\d{4}/)) {
            a1 = a1.split("-")
            year = a1[2]
            month = a1[1]
            day = a1[0]
            if (!M.isDate(day + "-" + month + "-" + year))
                return 0;
        }
        return parseFloat((new Date() - new Date(year, month, day)) / (1000 * 60 * 60 * 24 * 365))
    },
    calcBMI: function (h, w) {
        if (h == undefined || w == undefined || h <= 0 || w <= 0)
            return '';
        h = parseFloat(h)
        w = parseFloat(w)
        var BMI = w / ((h / 100) * (h / 100))
        var fBMI = Math.floor(BMI)
        var D = Math.round((BMI - fBMI) * 10)
        if (D == 10)
            fBMI += 1
        fBMI = fBMI + "." + D
        if (fBMI < 18.5)
            fBMI = "Underweight";
        else if (fBMI >= 18.5 && fBMI < 25)
            fBMI = "Normal";
        else if (fBMI >= 25 && fBMI < 30)
            fBMI = "Overweight";
        else if (fBMI > 30)
            fBMI = "Obese";
        return fBMI
    },
    isTime: function (time) {
        if (time == undefined || typeof (time) != "string" || !time.match(/^\d{1,2}:\d{2}\s([ap]m)?$/i))
            return false;
        time = time.split(" ")[0].split(":");
        return (time[0] < 24 && time[1] < 60);
    },
    isTimeHH: function (time) {
        if (time == undefined || typeof (time) != "string" || !time.match(/^\d{1,2}:\d{2}$/i))
            return false;
        time = time.split(":");
        return (time[0] < 24 && time[1] < 60);
    },
    dateDiff: function (date1, td) {
        var bd = date1.split("-");
        bd = new Date(bd[2], bd[1] - 1, bd[0]);
        if (td) {
            td = td.split('-');
            td = new Date(td[2], td[1] - 1, td[0]);
        } else
            td = new Date();
        var dy = td.getFullYear() - bd.getFullYear();
        var dm = (td.getMonth() + 1) - bd.getMonth();
        var dd = td.getDate() - bd.getDate();
        if (dm <= 0) {
            dy--;
            dm = 12 - Math.abs(dm);
        }
        return [dy, dm, dd];
    },
    dateDiffDays: function (date1, td) {
        var bd = date1.split("-");
        bd = new Date(bd[2], bd[1] - 1, bd[0]);
        if (td) {
            td = td.split('-');
            td = new Date(td[2], td[1] - 1, td[0]);
        } else
            td = new Date();
        var one_day = 1000 * 60 * 60 * 24;
        var diffInDays = Math.ceil((bd.getTime() - td.getTime()) / (one_day));
        return diffInDays;
    },
    dateDiffSecs: function (date1) {
        var bd = date1.split("-");
        bd = new Date(bd[2], (bd[1] - 1), bd[0]);
        var todayDate = new Date();
        var secondsDifference = bd.getTime() - todayDate.getTime();
        return secondsDifference;
    },
    duplicate: function (cont) {
        var pattern = /([A-Z]|[a-z]|[0-9])(\1)(\1)(\1)/;
        if (pattern.test(cont))
            return false;
        return true;
    },
    isEmpty: function (inputStr) {
        if (null === inputStr || "" === inputStr)
            return true;
        return false;
    },
    getRadioValue: function (radio, form) {
        form = M.getForm(form);
        if (!form)
            return '';
        if (form[radio] == undefined)
            return '';
        for (var i = 0; i < form[radio].length; i++)
            if (form[radio][i].checked)
                return form[radio][i].value;
        return false;
    },
    getSelectValue: function (elem, i) {
        if (!elem || elem.selectedIndex == undefined || elem.options.length == 0)
            return false;
        return elem.options[(i == undefined ? elem.selectedIndex : i)].value;
    },
    checkAll: function (frm, chk) {
        frm = this.getForm(frm);
        for (var n = 0; n < frm.elements.length; n++)
            if (frm.elements[n].type != undefined && frm.elements[n].type == 'checkbox')
                frm.elements[n].checked = (chk != undefined && typeof (chk) == "boolean" ? chk : true);
    },
    getForm: function (src) {
        if (document.forms[src] != undefined)
            return document.forms[src];
        else if (src.tagName == "FORM")
            return src;
        else
            return null;
    },
    months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    monthsFull: ['January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    byId: function (id) {
        if (typeof (id) != "string")
            return id;
        return (this.DOC.getElementById ? (this.DOC.getElementById(id) != undefined ? this.DOC.getElementById(id) : null) : (this.DOC.all ? (this.DOC.all[id] != undefined ? this.DOC.all[id] : null) : (this.DOC.layers[id] != undefined ? this.DOC.layers[id] : null)));
    },
    byTag: function (tagName, target) {
        var p = target || document;
        return p.getElementsByTagName(tagName);
    },
    docHeight: function () {
        return ((document.height != undefined) ? document.height : document.body.clientHeight);
    },
    docWidth: function () {
        return ((document.width != undefined) ? document.width : document.body.clientWidth);
    },
    isValidColorHex: function (c) {
        return /^(#)?([0-9a-fA-F]{3})([0-9a-fA-F]{3})?$/.test(c);
    },
    addE: function (e) {
        document.body.appendChild(e);
    },
    evtXY: function (e) {
        var XY = {x: 0, y: 0};
        if (e.pageX || e.pageY) {
            XY.x = e.pageX + 2;
            XY.y = e.pageY;
        } else if (e.clientX || e.clientY) {
            XY.x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            XY.y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }
        return XY;
    },
    elemXY: function (src) {
        var POS = {x: 0, y: 0};
        if (src.offsetParent) {
            POS.x = src.offsetLeft;
            POS.y = src.offsetTop;
            while ((src = src.offsetParent)) {
                POS.x += src.offsetLeft;
                POS.y += src.offsetTop;
            }
        }
        return POS;
    },
    visibleWidth: ((window.innerWidth != undefined) ? window.innerWidth : (((document.documentElement != undefined) && (document.documentElement.clientWidth != undefined) && (document.documentElement.clientWidth != 0)) ? document.documentElement.clientWidth : document.getElementsByTagName('body')[0].clientWidth)),
    visibleHeight: ((window.innerHeight != undefined) ? window.innerHeight : (((document.documentElement != undefined) && (document.documentElement.clientHeight != undefined) && (document.documentElement.clientHeight != 0)) ? document.documentElement.clientHeight : document.getElementsByTagName('body')[0].clientHeight)),
    FX: {fade: function (src, InOut, Color) {
            src = M.byId(src);
            src.style.backgroundColor = ((Color && M.isValidColorHex(Color)) ? Color : M.randomHexColor());
            var fader = function (target, fadeIO) {
                if (window.tmpopaval == undefined || !window.tmpopaval)
                    window.tmpopaval = fadeIO ? 100 : 0;
                target.style.display = "block";
                target.style.visibility = "visible";
                var val = parseInt(window.tmpopaval);
                target.style.opacity = val > 0 ? val / 100 : 0;
                if (M.UA.isIE) {
                    target.style.filter = "alpha(opacity=" + val + ")";
                }
                window.tmpopaval = fadeIO ? val + 1 : val - 1;
                setInterval(function () {
                    this(target, fadeIO);
                }, 100);
            };
            fader(src, InOut);
        }, toggleWithAnimate: function (target, size, coOrd) {
            if (!target.height || target.height == undefined)
                target.height = 0;
            target.animate = function () {
                var obj = this;
                var inc = 15, aniTime = 5;
                if (obj.height) {
                    if (obj.height <= 100) {
                        inc = 5;
                    } else if (obj.height > 100 && obj.height <= 200) {
                        inc = 10;
                    }
                }
                if (coOrd == 'y') {
                    obj.height += inc;
                    obj.style.height = obj.height + "px";
                    if (obj.height <= size)
                        setTimeout(function () {
                            obj.animate();
                        }, aniTime);
                } else if (coOrd == 'x') {
                    obj.width += inc;
                    obj.style.width = obj.width + "px";
                    if (obj.width <= size)
                        setTimeout(function () {
                            obj.animate();
                        }, aniTime);
                }
            };
            target.animate();
        },
        collapseWithAnimate: function (target, coOrd) {
            target.animate = function () {
                var obj = this;
                var dec = 15, aniTime = 15;
                if (obj.height) {
                    if (obj.height <= 100) {
                        dec = 5;
                    } else if (obj.height > 100 && obj.height <= 200) {
                        dec = 10;
                    }
                }
                if (coOrd == 'y') {
                    if (obj.height)
                        obj.height -= dec;
                    obj.style.height = obj.height + "px";
                    if (obj.height > 0)
                        setTimeout(function () {
                            obj.animate();
                        }, aniTime);
                    else
                        obj.style.display = "none";
                } else if (coOrd == 'x') {
                    obj.width -= dec;
                    obj.style.width = obj.width + "px";
                    if (obj.width > 0)
                        setTimeout(function () {
                            obj.animate();
                        }, aniTime);
                    else
                        obj.style.display = "none";
                }
            };
            target.animate();
        },
        hideShow: function (src) {
            src = M.byId(src);
            var stat = M.FX.srcStatus(src);
            if (!stat)
                src.style.display = "block";
            else if (stat)
                src.style.display = "none";
        },
        tick: function (src) {
            var s = M.FX.srcStatus(src);
            if (!s)
                src.style.backgroundPosition = "left top";
            else if (s)
                src.style.backgroundPosition = "right top";
            return s;
        },
        srcStatus: function (src) {
            if (!src)
                return null;
            if (src.status == undefined)
                src.status = 0;
            if (src.status == 0) {
                src.status = 1;
                return 1;
            } else if (src.status == 1) {
                src.status = 0;
            }
            return 0;
        },
        initHeightWidth: function (src) {
            if (src.initHeight == undefined)
            {
                src.initHeight = src.offsetHeight;
                src.initWidth = src.offsetWidth;
            }
            return [src.initWidth, src.initHeight];
        },
        toggle: function (target)
        {
            target = M.byId(target);
            target.style.display = "block";
            var wh = M.FX.initHeightWidth(target);
            var s = M.FX.srcStatus(target);
            if (s)
            {
                target.height = 0;
                M.FX.toggleWithAnimate(target, wh[1], 'y');
            } else
            {
                M.FX.collapseWithAnimate(target, 'y');
            }
        },
        hasClass: function (e, c) {
            return e.className.match(new RegExp('(\\s|^)' + c + '(\\s|$)'));
        },
        addClass: function (e, c) {
            if (!M.FX.hasClass(e, c))
                e.className += " " + c;
        },
        removeClass: function (e, c) {
            if (M.FX.hasClass(e, c)) {
                var reg = new RegExp('(\\s|^)' + c + '(\\s|$)');
                e.className = e.className.replace(reg, ' ');
            }
        }
    },
    TOOLTIPS: {obj: null, prevTipSrc: null},
    toolTip: function (txt, evO) {
        evO = M.objEvent(evO);
        if (!evO)
            return;
        var XY = M.evtXY(evO.e);
        var posx = XY.x;
        var posy = XY.y;

        if (evO.src != M.TOOLTIPS.prevTipSrc) {
            var obj = M.create("div");
            if (obj.className == undefined)
                obj.setAttribute("class", "tool-tip")
            else
                obj.className = "tool-tip";
            obj.setAttribute("id", "tooltipidentity");
            var tmp = M.create("div");
            obj.appendChild(tmp);
            var p = M.create("div");
            p.setAttribute("id", "txt");
            p.innerHTML = txt;
            tmp.appendChild(p);
            M.TOOLTIPS.prevTipSrc = evO.src;
            M.addE(obj);
            M.TOOLTIPS.obj = obj;
            evO.src.onmouseout = function () {
                document.body.removeChild(M.TOOLTIPS.obj);
                M.TOOLTIPS.prevTipSrc = null;
            };
        }

        if (M.TOOLTIPS.obj) {
            if (M.docHeight() < posy + 250)
                posy -= 50;
            if (M.docWidth() < 250 + posx || M.visibleWidth < 250 + posx)
                posx -= 270;
            M.TOOLTIPS.obj.style.left = (posx + 10) + "px";
            M.TOOLTIPS.obj.style.top = (posy - 5) + "px";
        }
    },
    toolTip_TM: function (txt, evO, src, help_width, x_offset, y_offset) {
        evO = M.objEvent(evO);
        if (!evO)
            return;
        var XY = M.elemXY(src);
        var posx = XY.x;
        var posy = XY.y;

        if (evO.src != M.TOOLTIPS.prevTipSrc) {
            var obj = M.create("div");
            if (obj.className == undefined)
                obj.setAttribute("class", "tool-tip")
            else
                obj.className = "tool-tip";

            obj.setAttribute("id", "tooltipidentity");
            var tmp = M.create("div");
            obj.appendChild(tmp);
            var p = M.create("div");
            p.setAttribute("id", "txt");
            p.innerHTML = txt;
            tmp.appendChild(p);
            M.TOOLTIPS.prevTipSrc = src;
            M.addE(obj);
            M.TOOLTIPS.obj = obj;
            src.onblur = function () {
                document.body.removeChild(M.TOOLTIPS.obj);
                M.TOOLTIPS.prevTipSrc = null;
                if (src.name == "grossmonthlyincome" || src.name == "insuredAmount" || src.name == "preEMIs" || src.name == "current_year_taxable_income" || src.name == "current_year_gross_turnover" || src.name == "current_year_tax" || src.name == "current_year_depreciation" || src.name == "previous_year_taxable_income" || src.name == "current_year_income_other" || src.name == "incomeTotalPLEMI") {
                    var tmp = M.formatAsMoneyStr(src.value);
                    src.value = tmp ? tmp : '';
                }
            };
        }

        if (M.TOOLTIPS.obj) {
            //M.attachEvent(src, "blur", function(){document.body.removeChild(M.TOOLTIPS.obj);M.TOOLTIPS.prevTipSrc=null;});
            M.TOOLTIPS.obj.style.width = help_width + "px";
            M.TOOLTIPS.obj.style.fontSize = "11px";

            if (M.docHeight() < (posy + 50)) {
                posy -= 50;
            }
            if (posy < 50) {
                posy += 25;
            }
            if (M.docWidth() < (250 + posx) || M.visibleWidth < (250 + posx)) {
                posx -= 270;
            }

            M.TOOLTIPS.obj.style.left = (posx + 200 + x_offset) + "px";
            M.TOOLTIPS.obj.style.top = (posy - 15 + y_offset) + "px";
        }
    },
    toolTip_TM_2: function (txt, evO, src, help_width, x_offset, y_offset) {
        evO = M.objEvent(evO);
        if (!evO)
            return;
        var XY = M.elemXY(src);
        var posx = XY.x;
        var posy = XY.y;

        if (evO.src != M.TOOLTIPS.prevTipSrc) {
            var obj = M.create("div");
            if (obj.className == undefined)
                obj.setAttribute("class", "tool-tip")
            else
                obj.className = "tool-tip";

            obj.setAttribute("id", "tooltipidentity");
            var tmp = M.create("div");
            obj.appendChild(tmp);
            var p = M.create("div");
            p.setAttribute("id", "txt");
            p.innerHTML = txt;
            tmp.appendChild(p);
            M.TOOLTIPS.prevTipSrc = src;
            M.addE(obj);
            M.TOOLTIPS.obj = obj;
            src.onmouseout = function () {
                document.body.removeChild(M.TOOLTIPS.obj);
                M.TOOLTIPS.prevTipSrc = null;
                if (src.name == "grossmonthlyincome" || src.name == "grossincome" || src.name == "insuredAmount" || src.name == "preEMIs" || src.name == "current_year_taxable_income" || src.name == "current_year_gross_turnover" || src.name == "current_year_tax" || src.name == "current_year_depreciation" || src.name == "previous_year_taxable_income" || src.name == "current_year_income_other") {
                    var tmp = M.formatAsMoneyStr(src.value);
                    src.value = tmp ? tmp : '';
                }
            };
        }

        if (M.TOOLTIPS.obj) {
            //M.attachEvent(src, "blur", function(){document.body.removeChild(M.TOOLTIPS.obj);M.TOOLTIPS.prevTipSrc=null;});
            M.TOOLTIPS.obj.style.width = help_width + "px";
            M.TOOLTIPS.obj.style.fontSize = "11px";

            /*if(M.docHeight() < (posy+50)) {
             posy-=50;
             }
             if (posy<50) {
             posy+=25;
             }*/
            if (M.docWidth() < (250 + posx) || M.visibleWidth < (250 + posx)) {
                posx -= 270;
            }

            M.TOOLTIPS.obj.style.left = (posx + 200 + x_offset) + "px";
            M.TOOLTIPS.obj.style.top = (posy - 15 + y_offset) + "px";
        }
    },
    postloadImg: function (bindImgSrc) {
        var bl = bindImgSrc.length;
        if (!bl)
            return;
        var preSrc;
        for (var n = 0; n < bl; n++) {
            if (bindImgSrc[n].imgId == undefined || bindImgSrc[n].imgSrc == undefined)
                return;
            var i = M.byId(bindImgSrc[n].imgId);
            preSrc = i.getAttribute("src").toString();
            if (i.src && (i.getAttribute("src") == "" || preSrc.substring(preSrc.lastIndexOf("/") + 1) == "vega-loader.gif"))
                i.src = bindImgSrc[n].imgSrc;
            else
                i.setAttribute("src", bindImgSrc[n].imgSrc);
        }
    },
    preloadImg: function (srcArr) {
        var preLElem = M.byId("preloadImageSpec");
        if (preLElem == null)
            return false;
        for (var n = 0; n < srcArr.length; n++)
        {
            var i = M.DOC.createElement("img");
            if (i.src != undefined)
                i.src = srcArr[n];
            else
                i.setAttribute("src", srcArr[n]);
            if (i.alt != undefined)
                i.alt = "";
            else
                i.setAttribute("alt", "");
            preLElem.appendChild(i);
        }
        return true;
    },
    /**
     * Must Include below lines in External CSS or Internal CSS
     * --------------------------------------------------------------------------------------
     * .doc_hider{top:0;left:0;width:100%;position:absolute;display:block;z-index:10000;}
     * .above_hider{border:1px solid #E7E7E7;position:absolute;display:block;z-index:10001;}
     * .content_title{padding:3px 0px;border:1px solid #000;color:#FFF;height:20px;text-align:right;}
     * .content_title a{padding:1px 2px;border:1px solid #999;color:#F00;text-decoration:none;}
     */
    tmpInterstitial: null,
    interstitial: function (Color, Opa)
    {
        var XPND = M.create("div");
        XPND.className = "doc_hider";
        var STYle = XPND.style;
        if (Opa == undefined || (Opa != undefined && isNaN(Opa)) || !Opa)
            Opa = 100;
        if (Opa > 100)
            Opa = 100;
        if (Opa < 0)
            Opa = 0;
        if (Opa > 0 && Opa < 100) {
            if (this.offset)
                STYle.MozOpacity = (Opa / 100) + "";
            else
                STYle.filter = "alpha(opacity=" + Opa + ")";
            STYle.opacity = (Opa / 100) + "";
        }
        STYle.height = M.visibleHeight + "px";
        Color = ((Color && M.isValidColorHex(Color)) ? Color : M.randomHexColor());
        STYle.backgroundColor = Color;
        document.body.appendChild(XPND);
        XPND.remove = function () {
            document.body.removeChild(this)
        };
        M.tmpInterstitial = XPND;
        return XPND;
    },
    docOverlay: function (Color, Opa)
    {
        var XPND = M.create("div");
        XPND.setAttribute("id", "transOverlayPanel");
        XPND.className = "doc_hider";
        var STYle = XPND.style;
        STYle.backgroundColor = ((Color && M.isValidColorHex(Color)) ? Color : M.randomHexColor());
        if (Opa == undefined || (Opa != undefined && isNaN(Opa)) || !Opa)
            Opa = 100;
        if (Opa > 100)
            Opa = 100;
        if (Opa < 0)
            Opa = 0;
        if (Opa > 0 && Opa < 100)
            if (this.offset)
                STYle.MozOpacity = (Opa / 100) + "";
            else
                STYle.filter = "alpha(opacity=" + Opa + ")";
        with (STYle) {
            opacity = (Opa / 100) + "";
            height = this.docHeight() + "px";
        }
        document.body.appendChild(XPND);
        XPND.remove = function () {
            document.body.removeChild(this)
        };
        return XPND;
    },
    scrollTopValue: function () {
        return M.offset ? window.pageYOffset : document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
    },
    scrollTop: function () {
        var py = M.scrollTopValue();
        window.scrollBy(0, py - (py * 2));
    },
    createOver: function (H, W, Color, Opa, floated) {
        var XPND = M.docOverlay(Color, Opa == undefined ? 60 : Opa);
        var CNTNT = M.create("div");
        CNTNT.id = "overElementContainerBox";
        CNTNT.setAttribute("id", "overElementContainerBox");
        CNTNT.className = "above_hider";
        var height = H;
        var width = W;
        STYle = CNTNT.style;
        STYle.height = height + "px";
        STYle.width = width + "px";
        STYle.top = ((this.visibleHeight - height) / 2) + "px";
        STYle.left = ((this.visibleWidth - width) / 2) + "px";
        document.body.appendChild(CNTNT);
        var C = M.create("div");
        C.className = "content_title";
        C.appendChild(document.createTextNode("Esc(Close)"));
        var CB = M.create("a");
        CB.href = "javascript:;";
        CB.innerHTML = "X";
        CB.setAttribute("title", "Close");
        C.appendChild(CB);
        CNTNT.appendChild(C);
        C = M.create("div");
        C.style.height = (height - 48) + "px";
        C.style.overflow = "auto";
        CNTNT.appendChild(C);
        var timer = this.floatDiv("overElementContainerBox", ((this.visibleWidth - width) / 2), ((this.visibleHeight - height) / 2)).floatIt();
        var listener = function () {
            document.body.removeChild(CNTNT);
            document.body.removeChild(XPND);
            M.detachEvent(document, "keypress", listener);
            clearTimeout(timer);
        };
        C.removeout = listener;
        M.addEventListener(CB, 'click', listener);
        listener = function () {
            evO = M.objEvent(arguments.length ? arguments[0] : event);
            if (evO && evO.e.keyCode == 27) {
                document.body.removeChild(CNTNT);
                document.body.removeChild(XPND);
                M.detachEvent(document, "keypress", listener);
                clearTimeout(timer);
            }
        };
        M.addEventListener(document, 'keypress', listener);
        return C;
    },
    alertMessage: function (msg, title, color) {},
    floatDiv: function (id, sx, sy) {
        var el = this.byId(id);
        if (!el)
            return false;
        var px = document.layers ? "" : "px";
        window[id + "_obj"] = el;
        if (document.layers)
            el.style = el;
        el.cx = el.sx = sx;
        el.cy = el.sy = sy;
        el.style.position = "absolute";
        el.sP = function (x, y) {
            this.style.left = x + px;
            this.style.top = y + px;
        };
        el.floatIt = function () {
            var pX, pY;
            pX = (this.sx >= 0) ? 0 : this.offset ? innerWidth : document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.clientWidth;
            pY = this.offset ? pageYOffset : document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
            if (this.sy < 0)
                pY += this.offset ? innerHeight : document.documentElement && document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
            this.cx += (pX + this.sx - this.cx) / 8;
            this.cy += (pY + this.sy - this.cy) / 8;
            this.sP(this.cx, this.cy);
            return setTimeout(this.id + "_obj.floatIt()", 40);
        };
        return el;
    },
    getElementValue: function (elem) {
        if (!elem || (elem == undefined) || (elem.tagName == undefined))
            return false;
        var val = '';
        switch (elem.tagName.toUpperCase()) {
            case 'SELECT':
                val = M.getSelectValue(elem);
                break;
            case 'INPUT':
                switch (elem.type.toUpperCase()) {
                    case 'RADIO':
                        M.getRadioValue(elem, elem.form);
                        break;
                    case 'CHECKBOX':
                        val = this.getRadioValue(elem);
                        break;
                    case 'TEXT':
                    case 'EMAIL':
                    case 'TEL':
                    case 'BUTTON':
                    case 'HIDDEN':
                    case 'FILE':
                        val = elem.value;
                }
                break;
            case 'TEXTAREA':
                val = elem.value;
                break;
        }
        return val;
    },
    addEventListener: function (el, evname, func) {
        if (el.attachEvent)
            el.attachEvent("on" + evname, func);
        else if (el.addEventListener)
            el.addEventListener(evname, func, true);
        else
            el["on" + evname] = func;
    },
    detachEventListener: function (el, evname) {
        if (el.detachEvent)
            el.detachEvent("on" + evname, "");
        else if (el.removeEventListener)
            el.removeEventListener(evname, "", false);
        else
            el["on" + evname] = "";
    },
    removeEventListener: function (event) {
        if (event.preventDefault) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            event.returnValue = false;
            event.cancelBubble = true;
        }
    },
    objEvent: function (evt) {
        evt = evt ? evt : (event ? event : null);
        if (evt)
            return{e: evt, src: (evt.srcElement ? evt.srcElement : evt.target), form: (evt.srcElement ? evt.srcElement : evt.target).form};
        return null;
    },
    pick: function (target, src, c) {
        src.style.display = "none";
        target = document.getElementById(target);
        target.style.display = "block";
        if (c) {
            c = document.getElementById(c);
            c.onclick = function () {
                src.style.display = "block";
                target.style.display = "none";
            }
        }
    },
    animateScroll: function (target, size, coOrd) {
        target.animate = function () {
            var obj = this;
            var inc = 15, aniTime = 5;
            if (coOrd == 'y') {
                obj.height += inc;
                obj.style.height = this.height + "px";
                if (obj.height <= size)
                    setTimeout(function () {
                        obj.animate();
                    }, aniTime);
            } else if (coOrd == 'x') {
                obj.width += inc;
                obj.style.width = obj.width + "px";
                if (obj.width <= size)
                    setTimeout(function () {
                        obj.animate();
                    }, aniTime);
            }
        };
        target.animate();
    },
    createPopup: function (preview, evt) {
        var evO = M.objEvent(evt);
        if (!evO)
            return false;
        if (evO.e.pageX || evO.e.pageY) {
            posx = evO.e.pageX + 2;
            posy = evO.e.pageY;
        } else if (evO.e.clientX || evO.e.clientY) {
            posx = evO.e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            posy = evO.e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }
        if (M.W.previousSource == undefined)
            M.W.previousSource = "";
        if ((M.W.popup == undefined) || (!M.W.popup)) {
            M.W.popup = M.create("div");
            M.W.popup.className = "hlpopup";
            var xp = M.create("p");
            M.W.popup.appendChild(xp);
            var x = M.create("a");
            x.setAttribute("href", "javascript:;");
            x.innerHTML = "X";
            x.setAttribute("title", "close");
            x.onclick = function (evt) {
                document.body.removeChild(M.W.popup);
                M.W.popup = null;
            };
            xp.appendChild(x);
            xp = M.create("div");
            xp.innerHTML = preview;
            M.W.popup.appendChild(xp);
            M.W.popup.timer = null;
            M.W.popup.onmousemove = function () {
                if (this.timer != null)
                    clearTimeout(this.timer);
            };
            M.W.popup.onmouseout = function () {
                if (this.timer != null)
                    clearTimeout(this.timer);
                this.timer = setTimeout("document.body.removeChild(window.popup);window.popup=null;", 1000);
            };
            document.body.appendChild(M.W.popup);
            M.W.previousSource = evO.src;
        }
        if (evO.src != M.W.previousSource) {
            M.W.popup.getElementsByTagName('div')[0].innerHTML = preview;
            M.W.previousSource = evO.src;
        }
        M.W.popup.style.left = (posx + 10) + "px";
        M.W.popup.style.top = posy + "px";
        return true;
    },
    attachEvent: function (obj, ev, fn) {
        obj = M.byId(obj);
        if (obj.addEventListener)
            obj.addEventListener(ev, fn, true);
        else if (obj.attachEvent)
            obj.attachEvent("on" + ev, fn);
    },
    detachEvent: function (el, ev, fn) {
        if (el.detachEvent)
            el.detachEvent("on" + ev, fn);
        else if (el.removeEventListener)
            el.removeEventListener(ev, fn, false);
        else
            el["on" + evname] = "";
    },
    isWebKit: function () {
        return RegExp(" AppleWebKit/").test(M.UA);
    },
    search: function (v, a) {
        for (var i = 0; i < a.length; i++)
            if (a[i] == v)
                return i;
        return -1;
    },
    toInt: function (n) {
        return parseInt(n);
    },
    toChar: function (code) {
        return String.fromCharCode(code);
    },
    keys: [8, 9, 13, 37, 39, 46, 110],
    digit: function (evO) {
        evO = M.objEvent(evO);
        if (!evO)
            return false;
        if (evO.e.shiftKey && evO.e.keyCode == 9)
            return true
        if (evO.e.shiftKey || evO.e.keyCode == 16 || evO.e.keyCode == 110)
            return false;
        var r = (evO.e.keyCode >= 48 && evO.e.keyCode <= 57)
        if (r)
            return true
        r = (evO.e.keyCode >= 96 && evO.e.keyCode <= 105)
        if (r)
            return true
        r = (evO.e.keyCode >= 37 && evO.e.keyCode <= 40)
        if (r)
            return true
        var is = M.search(evO.e.keyCode, M.keys);
        return (is >= 0)
    },
    digitWithNegative: function (evO) {
        evO = M.objEvent(evO);
        if (!evO)
            return false;
        r = (evO.e.keyCode == 189 || evO.e.keyCode == 173 || evO.e.keyCode == 109)
        if (r)
            return true
        if (evO.e.shiftKey && evO.e.keyCode == 9)
            return true
        if (evO.e.shiftKey || evO.e.keyCode == 16)
            return false;
        var r = (evO.e.keyCode >= 48 && evO.e.keyCode <= 57)
        if (r)
            return true
        r = (evO.e.keyCode >= 96 && evO.e.keyCode <= 105)
        if (r)
            return true
        r = (evO.e.keyCode >= 37 && evO.e.keyCode <= 40)
        if (r)
            return true
        var is = M.search(evO.e.keyCode, M.keys);
        return (is >= 0)
    },
    caps: function (evO) {
        evO = M.objEvent(evO);

        if (evO.e.shiftKey == true) {
            var r = ((evO.e.keyCode >= 65 && evO.e.keyCode <= 90));
            if (r)
                return true;
            else
                return false;
        }
        //alert(evO.e.keyCode);
        if (!evO)
            return false;
        r = (evO.e.keyCode > 96 && evO.e.keyCode <= 122);
        if (r)
            return false;
        if (evO.e.shiftKey && evO.e.keyCode == 9)
            return true;
        if (evO.e.shiftKey || evO.e.keyCode == 16)
            return true;
        var r = (evO.e.keyCode == 32 || evO.e.keyCode == 127);
        if (r)
            return true;

        var r = (evO.e.keyCode >= 65 && evO.e.keyCode <= 90);
        if (r) {
            evO.src.value = evO.src.value.toUpperCase();
            return true;
        }

        var is = M.search(evO.e.keyCode, M.keys);
        return (is >= 0);
    },

    getKeyCode: function (str) {
        return str.charCodeAt(str.length - 1);
    },

    isChars: function (evO) { // this is for the enters middle name in first name or last name
        evO = M.objEvent(evO);
        var keyCode = null;
        if ((evO.e.keyCode == 0 || evO.e.keyCode == 229)) { //for android chrome keycode fix
            /**
             *  I am not able to get current value
             **/
            //keyCode = M.getKeyCode(evO.src.value);
            //keyCode = M.getKeyCode(M.byId(evO.src.id).value);

            return true;
        } else {
            keyCode = evO.e.keyCode
        }
        keyCode = parseInt(keyCode);
        if (evO.e.shiftKey == true) {
            var r = ((keyCode >= 65 && keyCode <= 90));
            if (r)
                return true;
            else
                return false;
        }
        //alert(keyCode);
        if (!evO)
            return false;
        //r=(evO.e.keyCode>96&&evO.e.keyCode<=122);
        //if(r)return false;
        if (evO.e.shiftKey && keyCode == 9)
            return true;
        if (evO.e.shiftKey || keyCode == 16)
            return true;
        var r = (keyCode == 32 || keyCode == 127);
        if (r)
            return true;

        var r = (keyCode >= 65 && keyCode <= 90);
        if (r)
            return true;

        var is = M.search(keyCode, M.keys);
        return (is >= 0);
    },
    mobile: function (evO) {
        evO = M.objEvent(evO);
        if (!evO)
            return false;
        if (evO.e.shiftKey && evO.e.keyCode == 9)
            return true
        if (evO.e.shiftKey || evO.e.keyCode == 16)
            return false;
        var r = (evO.e.keyCode >= 48 && evO.e.keyCode <= 57)
        if (r)
            return true
        r = (evO.e.keyCode >= 96 && evO.e.keyCode <= 105)
        if (r)
            return true
        r = (evO.e.keyCode >= 37 && evO.e.keyCode <= 40)
        if (r)
            return true
        if (evO.e.keyCode == 110)
            return false;
        var is = M.search(evO.e.keyCode, M.keys);
        return (is >= 0)
    },
    maxLength: function (evO, len) { //Added on 24th June
        evO = M.objEvent(evO);
        if (evO.e.keyCode == 8 || evO.e.keyCode == 37 || evO.e.keyCode == 39 || evO.e.keyCode == 9)
            return true;
        return evO.e.target.value.length < len;
    },
    floatDigit: function (evO) {
        evO = M.objEvent(evO);
        if (!evO)
            return false;
        if (evO.e.shiftKey && evO.e.keyCode == 9)
            return true
        if (evO.e.shiftKey || evO.e.keyCode == 16)
            return false;
        var r = (evO.e.keyCode >= 48 && evO.e.keyCode <= 57)
        if (r)
            return true
        r = (evO.e.keyCode >= 96 && evO.e.keyCode <= 105)
        if (r)
            return true
        r = (evO.e.keyCode == 110 || evO.e.keyCode == 190)
        if (r)
            return true
        var is = M.search(evO.e.keyCode, M.keys);
        return (is >= 0)
    },
    isAlphaNumeric: function (evO) {
        evO = M.objEvent(evO);

        if (evO.e.shiftKey == true) {
            var r = ((evO.e.keyCode >= 65 && evO.e.keyCode <= 90));
            if (r)
                return true;
            else
                return false;
        }
        if (!evO)
            return false;
        if (evO.e.shiftKey && evO.e.keyCode == 9)
            return true
        if (evO.e.shiftKey || evO.e.keyCode == 16)
            return false;
        var r = (evO.e.keyCode >= 48 && evO.e.keyCode <= 57)
        if (r)
            return true
        r = (evO.e.keyCode >= 96 && evO.e.keyCode <= 105)
        if (r)
            return true
        r = (evO.e.keyCode >= 37 && evO.e.keyCode <= 40)
        if (r)
            return true
        var r = (evO.e.keyCode >= 65 && evO.e.keyCode <= 90)
        if (r)
            return true
        var is = M.search(evO.e.keyCode, M.keys);
        return (is >= 0)
    },
    nthStandard: function (n) {
        /*n=parseInt(n);
         if(n) {
         if(n==1) n+="st";
         else if(n==2) n+="nd";
         else if(n==3) n+="rd";
         else n+="th";
         }
         return n;*/
        return M.toOrdinal(n)
    },
    toOrdinal: function (num)
    {
        if (num != undefined)
        {
            num = parseInt(num);
            if (!num)
                return num;
        } else
        {
            num = this
        }

        var n = num % 100;
        var suffix = ['th', 'st', 'nd', 'rd', 'th'];
        var ord = n > 21 ? (n < 4 ? suffix[n] : suffix[0]) : (n % 10 > 4 ? suffix[0] : suffix[n % 10]);
        return num + ord;
    },
    doDisable: function (target, action, form) {
        if (target == null)
            return;
        if (typeof (action) != "boolean")
            action = true;
        if (target.length != undefined) {
            for (var i = 0; i < target.length; i++) {
                form[target[i]].disabled = action
            }
        }
    }, //;if(form[target[i]].options!=undefined)form[target[i]].options.length=0;else if(form[target[i]].value!=undefined)form[target[i]].value=""
    hideShow: function (src) {
        src = M.byId(src);
        if (src.status == undefined)
            src.status = 0;
        if (src.status == 0) {
            src.style.display = "block";
            src.status = 1;
        } else if (src.status == 1) {
            src.style.display = "none";
            src.status = 0;
        }
    },
    create: function (n, t) {
        if (t != undefined)
            return M.DOC.createTextNode(n);
        return M.DOC.createElement(n);
    },
    slideShowVar: -1, sliderPrevThumb: -1, sync: false, sliderTimer: {main: null, opa: null},
    slideShow: function (arr)
    {
        var AB = M.byId("animIntro");
        var AT = M.byId("nav");
        var NXT = M.byId("introTmpImgNxt");
        if (AB && AT)
        {
            if (M.sliderTimer.main)
                clearTimeout(M.sliderTimer.main);
            if (M.sliderTimer.opa)
                clearTimeout(M.sliderTimer.opa);
            M.slideShowVar++;
            AB = AB.childNodes;
            AT = AT.childNodes;
            if (M.slideShowVar >= AT.length) {
                M.slideShowVar = 0;
                M.sync = true;
            }
            AB[0].src = arr[M.slideShowVar][0];
            AB[1].innerHTML = arr[M.slideShowVar][1];
            if (!M.sync && M.slideShowVar < AT.length - 1)
                NXT.src = arr[M.slideShowVar + 1][0];
            NXT.setAttribute("alt", "previous image");
            if (M.sliderPrevThumb >= 0)
                AT[M.sliderPrevThumb].className = "";
            AT[M.slideShowVar].className = "cur";
            M.sliderPrevThumb = M.slideShowVar;
            M.sliderTimer.main = setTimeout(function () {
                M.slideShow(arr);
            }, 10000);
            var opacity = function (ELEM)
            {
                if (ELEM.opaval == undefined)
                    ELEM.opaval = 0;
                ELEM.opaval = ELEM.opaval + 3;
                var opa = 0;
                if (ELEM.opaval >= 100)
                    opa = 100;
                else
                    opa = ELEM.opaval / 100;
                if (M.UA.isIE)
                    ELEM.style.filter = "alpha(opacity=" + ELEM.opaval + ")";
                else
                    ELEM.style.opacity = opa;
                if (ELEM.opaval < 100)
                    M.sliderTimer.opa = setTimeout(function () {
                        opacity(ELEM);
                    }, 50);
                else
                    ELEM.opaval = 0;
            };
            opacity(AB[0]);
        }
    },
    rateStar: function (star_parents, onstar)
    {
        if (star_parents == undefined)
            return;
        for (var i = 0; i < star_parents.length; i++)
        {
            var stars = M.byTag("b", M.byId(star_parents[i]))
            for (var n = 0; n < stars.length; n++)
            {
                stars[n].onclick = function (evt) {
                    var sp = M.byTag("b", this.parentNode)// this.parentNode.getElementsByTagName("b");
                    for (var n = 0; n < sp.length; n++)
                    {
                        if (sp[n] == this) {
                            onstar.call(this, n + 1, sp[n].parentNode.getAttribute('uid'), this.parentNode.getAttribute("id"));
                        }
                        sp[n].onclick = function () {};
                        sp[n].onmouseout = function () {};
                        sp[n].onmouseover = function () {};
                    }
                };

                stars[n].onmouseover = function (evt)
                {
                    var sp = M.byTag("b", this.parentNode)//this.parentNode.getElementsByTagName("b");
                    for (var n = 0; n < sp.length; n++)
                    {
                        //sp[n].style.backgroundPosition="left -14px";
                        sp[n].className = "hover";
                        if (sp[n] == this)
                            break;
                    }
                };

                stars[n].onmouseout = function (evt)
                {
                    var sp = M.byTag("b", this.parentNode)//this.parentNode.getElementsByTagName("b");
                    for (var n = 0; n < sp.length; n++)
                    {
                        //sp[n].style.backgroundPosition="left top";
                        sp[n].className = "";
                        if (sp[n] == this)
                            break;
                    }
                };
            }
        }
    },
    dateFormat: function () {
        var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
                timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
                timezoneClip = /[^-+\dA-Z]/g,
                pad = function (val, len) {
                    val = String(val);
                    len = len || 2;
                    while (val.length < len)
                        val = "0" + val;
                    return val;
                };
        this.masks = {
            "default": "ddd mmm dd yyyy HH:MM:ss",
            shortDate: "m/d/yy",
            mediumDate: "mmm d, yyyy",
            longDate: "mmmm d, yyyy",
            fullDate: "dddd, mmmm d, yyyy",
            shortTime: "h:MM TT",
            mediumTime: "h:MM:ss TT",
            longTime: "h:MM:ss TT Z",
            isoDate: "yyyy-mm-dd",
            isoTime: "HH:MM:ss",
            isoDateTime: "yyyy-mm-dd'T'HH:MM:ss",
            isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
        };

        this.i18n = {
            dayNames: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
        };

        // Regexes and supporting functions are cached through closure
        return function (date, mask, utc) {
            var dF = M.dateFormat;

            // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
            if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
                mask = date;
                date = undefined;
            }

            // Passing date through Date applies Date.parse, if necessary
            date = date ? new Date(date) : new Date;
            if (isNaN(date))
                throw SyntaxError("invalid date");

            mask = String(dF.masks[mask] || mask || dF.masks["default"]);

            // Allow setting the utc argument via the mask
            if (mask.slice(0, 4) == "UTC:") {
                mask = mask.slice(4);
                utc = true;
            }

            var _ = utc ? "getUTC" : "get",
                    d = date[_ + "Date"](),
                    D = date[_ + "Day"](),
                    m = date[_ + "Month"](),
                    y = date[_ + "FullYear"](),
                    H = date[_ + "Hours"](),
                    M = date[_ + "Minutes"](),
                    s = date[_ + "Seconds"](),
                    L = date[_ + "Milliseconds"](),
                    o = utc ? 0 : date.getTimezoneOffset(),
                    flags = {
                        d: d,
                        dd: pad(d),
                        ddd: dF.i18n.dayNames[D],
                        dddd: dF.i18n.dayNames[D + 7],
                        m: m + 1,
                        mm: pad(m + 1),
                        mmm: dF.i18n.monthNames[m],
                        mmmm: dF.i18n.monthNames[m + 12],
                        yy: String(y).slice(2),
                        yyyy: y,
                        h: H % 12 || 12,
                        hh: pad(H % 12 || 12),
                        H: H,
                        HH: pad(H),
                        M: M,
                        MM: pad(M),
                        s: s,
                        ss: pad(s),
                        l: pad(L, 3),
                        L: pad(L > 99 ? Math.round(L / 10) : L),
                        t: H < 12 ? "a" : "p",
                        tt: H < 12 ? "am" : "pm",
                        T: H < 12 ? "A" : "P",
                        TT: H < 12 ? "AM" : "PM",
                        Z: utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                        o: (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                        S: ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
                    };

            return mask.replace(token, function ($0) {
                return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
            });
        };
    },
    preventFB: function () {
        if (!("console" in window) || !("firebug" in console)) {
            var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml", "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];
            window.console = {};
            for (var i = 0; i < names.length; ++i)
                window.console[names[i]] = function () {};
        }
        setInterval(function () {
            if (window.console && window.console.firebug)
            {
                //
            }
        }, 5000);
    },
    popup: function (url, wn, w, h) {
        var NewChild = M.W.open(url, (wn && M.trim(wn) ? wn : "NewChild"), M.UA.offset ? M.UA.NNArgs : M.UA.IEArgs, false);
        if (NewChild.fullscreen)
            NewChild.fullscreen = true;
        if (NewChild.alwaysRaised)
            NewChild.alwaysRaised = true;
        if (NewChild.focus)
            NewChild.focus();
        if (w != undefined && w >= 50)
            NewChild.resizeTo(w, 100);
        if (w != undefined && h != undefined && h >= 50)
            NewChild.resizeTo(w, h);
        return NewChild;
    },
    rateStars: function (callBack) {
        var childs = M.byId('ratestars').childNodes;
        var over = function (evt) {
            var evO = M.objEvent(evt);
            for (var i = 0; i < childs.length; i++) {
                childs[i].style.backgroundPosition = "left -17px";
                if (childs[i] == evO.src)
                    break;
            }
        };
        var out = function (evt) {
            var evO = M.objEvent(evt);
            for (var i = 0; i < childs.length; i++) {
                childs[i].style.backgroundPosition = "left top";
                if (childs[i] == evO.src)
                    break;
            }
        };
        var clck = function (evt) {
            var evO = M.objEvent(evt);
            for (var i = 0; i < childs.length; i++) {
                if (!M.UA.offset)
                    M.detachEvent(childs[i], 'mouseout', out);
                if (childs[i] == evO.src) {
                    M.attachEvent(childs[i], 'mouseout', over);
                    if (typeof (callBack) == "function")
                        callBack(i + 1);
                    break;
                }
            }
        };
        for (var i = 0; i < childs.length; i++) {
            M.attachEvent(childs[i], "mouseover", over);
            M.attachEvent(childs[i], "mouseout", out);
            M.attachEvent(childs[i], "click", clck);
        }
    },
    popup_TM: function (url, wn, w, h) {
        var NewChild = window.open(url, wn, '');
        if (NewChild.fullscreen)
            NewChild.fullscreen = true;
        if (NewChild.alwaysRaised)
            NewChild.alwaysRaised = true;
        if (NewChild.focus)
            NewChild.focus();
        if (w != undefined && w >= 50)
            NewChild.resizeTo(w, 100);
        if (w != undefined && h != undefined && h >= 50)
            NewChild.resizeTo(w, h);
        return NewChild;
    },
    rateStars: function (callBack) {
        var childs = M.byId('ratestars').childNodes;
        var over = function (evt) {
            var evO = M.objEvent(evt);
            for (var i = 0; i < childs.length; i++) {
                childs[i].style.backgroundPosition = "left -17px";
                if (childs[i] == evO.src)
                    break;
            }
        };
        var out = function (evt) {
            var evO = M.objEvent(evt);
            for (var i = 0; i < childs.length; i++) {
                childs[i].style.backgroundPosition = "left top";
                if (childs[i] == evO.src)
                    break;
            }
        };
        var clck = function (evt) {
            var evO = M.objEvent(evt);
            for (var i = 0; i < childs.length; i++) {
                if (!M.UA.offset)
                    M.detachEvent(childs[i], 'mouseout', out);
                if (childs[i] == evO.src) {
                    M.attachEvent(childs[i], 'mouseout', over);
                    if (typeof (callBack) == "function")
                        callBack(i + 1);
                    break;
                }
            }
        };
        for (var i = 0; i < childs.length; i++) {
            M.attachEvent(childs[i], "mouseover", over);
            M.attachEvent(childs[i], "mouseout", out);
            M.attachEvent(childs[i], "click", clck);
        }
    },
    init: function (call, args, remove)
    {
        if (call == undefined)
            return;
        if ((typeof (remove) == "boolean") && remove)
            M.attachEvent(M.W, "load",
                    function ()
                    {
                        if (typeof (call) == "function")
                            call(args);
                        else
                            eval(call);
                    }
            );
        else
        {
            var prev = M.EVENT.onLoad(M.W);
            if (prev)
                M.attachEvent(M.W, "load",
                        function ()
                        {
                            prev();
                            if (typeof (call) == "function")
                                call(args);
                            else
                                eval(call)
                        }
                );
            else
                M.init(call, args, true);
        }
    }
};

//M.preventFB();

function MOption(val, text, selected)
{
    var op = document.createElement("option");
    if (op.value)
        op.value = val;
    else
        op.setAttribute("value", val);
    if (op.text != undefined)
        op.text = text;
    else
        op.setAttribute("text", text);
    if (selected)
        op.setAttribute("selected", "selected");
    op.innerHTML = text;
    return op;
}

function saveStars(stars) {
    var bdy = M.createOver(360, 600, "#000000");
    bdy.style.padding = "10px";
    bdy.style.overflow = "hidden";
    bdy.className = "formatform";
    bdy.style.backgroundColor = "#E7E7E7";
    var div = document.createElement("div");
    div.className = "rd5px1border";
    div.style.backgroundColor = "#FFF";
    bdy.appendChild(div);
    var b = document.createElement("div");
    with (b.style) {
        color = "#FFF";
        fontSize = "54px";
        textAlign = "center";
        textTransform = "uppercase";
        border = "1px solid #999";
        marginTop = "5px";
    }
    b.innerHTML = "advertisement";
    bdy.appendChild(b);
    b = document.createElement("big");
    b.innerHTML = "Rate the page";
    div.appendChild(b);
    b = document.createElement("form");
    div.appendChild(b);
    b = document.createElement("table");
    div.appendChild(b);
    var tr = b.insertRow(b.rows.length);
    var cell = tr.insertCell(tr.cells.length);
    cell.colSpan = 2;
    cell.innerHTML = "<small><i>Fields are below is optional.</i></small>";
    tr = b.insertRow(b.rows.length);
    cell = tr.insertCell(tr.cells.length);
    cell.innerHTML = "<label for=uname>Name:</label>";
    cell = tr.insertCell(tr.cells.length);
    var input = document.createElement("input");
    input.type = "text";
    input.name = "uname";
    input.setAttribute("id", "uname");
    input.maxLength = "54";
    input.className = "w200";
    cell.appendChild(input);
    tr = b.insertRow(b.rows.length);
    cell = tr.insertCell(tr.cells.length);
    cell.innerHTML = "<label for=comments>Comments:</label>";
    cell = tr.insertCell(tr.cells.length);
    var comment = document.createElement("textarea");
    comment.name = "comments";
    comment.setAttribute("id", "comments");
    comment.className = "w395 h100";
    cell.appendChild(comment);
    tr = b.insertRow(b.rows.length);
    cell = tr.insertCell(tr.cells.length);
    var btn = document.createElement("input");
    btn.type = "button";
    btn.value = "Send";
    btn.className = "btn btn-green";
    M.attachEvent(btn, "click", function () {
        var s = "page=PAGERATESTAR&stars=" + stars + "&uname=" + input.value + "&comments=" + comment.value + "&url=" + document.location;
        M.AJAX.request({
            target: 'JSONResponse',
            query: s,
            method: 'POST',
            loader: M.byId('rateStarLoader'),
            onSuccess: function (RES) {
                if (!isNaN(RES)) {
                    div.style.padding = "10px";
                    div.innerHTML = "<h1><font color=green>Thank you.</font></h1><p>Your rate successfully done.</p>";
                } else {
                    div.style.padding = "10px";
                    div.innerHTML = "<h1><font color=red>Sorry!</font></h1><p>Your rate is failed.</p>";
                    setTimeout(function () {
                        removeout();
                    }, 3000);
                }
            },
            onFailed: function (msg) {
                div.style.padding = "10px";
                div.innerHTML = "<h1 class='rd'>Sorry!</h1><p>Your rate is failed.</p><p><font color=red>" + msg + "</font></p>";
            }
        });
    });
    cell.appendChild(btn);
    cell = tr.insertCell(tr.cells.length);
    cell.setAttribute("id", "rateStarLoader");
}

//document.onscroll=function(){M.byId("feedback").innerHTML=document.body.scrollTop+"<br />"+window.pageYOffset + " px";};

// For convenience...
Date.prototype = {format: function (mask, utc) {
        return M.dateFormat(this, mask, utc)
    }};

var XML_HTTP_OBJECT = M.AJAX.XHR();
if (parent.frames.length) {
    //top.location=document.location;
} else if (top != self) {
    top.location.replace(self.location.href);
}

Number.prototype.toOrdinal = M.toOrdinal;


// Cross Browser JSON Parser {Uses M.JSONParse('json string')}
(function (A, undefined) {
    var JSONFine = function (data) {
        return /^[\],:{}\s]*$/.test(data.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]").replace(/(?:^|:|,)(?:\s*\[)+/g, ""))
    }
    var JSONDead = (function () {
        try {
            JSON.parse("{a:1}");
            return true
        } catch (x) {
            return false
        }
    })();
    A.JSONParse = (window.JSON && "function" === typeof (window.JSON.parse)) ? (JSONDead) ? function json_parse(data) {
        if (!JSONFine(data))
            throw new Error(0xFFFF, "Bad JSON string.");
        return window.JSON.parse(data)
    } : function json_parse(data) {
        return window.JSON.parse(data)
    } : function json_parse(data) {
        if (!JSONFine(data))
            throw new Error(0xFFFF, "Bad JSON string.");
        return (new Function("return " + data))()
    }
})(M);

function validateAllAjaxScanning(requestData) {
    requestData = decodeURIComponent(requestData);
    var bardOperationList = [[/<|>|;|'/g, ''], [/\(\(/g, '('], [/\)\)/g, ')']];
    if (requestData != undefined && requestData != "") {
        for (var k = 0; k < bardOperationList.length; k++) {
            if (bardOperationList[k][0].test(requestData)) {
                requestData = requestData.replace(bardOperationList[k][0], bardOperationList[k][1]);
            }
        }
    }
    return requestData;
}

$(document).ready(function () {

    //~ //window.history.pushState(null, null, window.location.href);
    //~ window.history.pushState(null, "", window.location.href);
    //~ //window.addEventListener('popstate', function(){history.pushState(null, null, location.href);});
    //~ //window.onpopstate = function(){history.go(1);}
    //~ window.onpopstate = function(){
    //~ //alert(35545); 
    //~ $.ajax({
    //~ url: '/lms-loan-al',
    //~ type: 'POST',
    //~ data: $('input[name="sent_logout"]').closest('form').serialize(),
    //~ success: function(res){
    //~ location.reload();
    //~ }
    //~ });
    //~ 
    //~ setTimeout(function(){location.reload();}, 2000);
    //~ return false;
    //~ /*window.history.pushState(null, "", window.location.href);*/
    //~ 
    //~ }

    //~ function disableBack(){window.history.forward();}
    //~ window.onload = disableBack();
    //~ window.onpageshow = function(evt){if(evt.persisted)disableBack();};

    //~ $.ajaxSetup({
    //~ beforeSend: function(xhr, settings){
    //~ settings.data = validateAllAjaxScanning(settings.data);			
    //~ }
    //~ });

    $(document).ajaxSend(function (event, xhr, settings) {
        console.log(settings);
        settings.data = validateAllAjaxScanning(settings.data);
        settings.url = validateAllAjaxScanning(settings.url);
    });
});


