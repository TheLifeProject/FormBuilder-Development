function fb_toggleLayer( whichLayer )
{
  var elem, vis;
  if( document.getElementById ) // this is the way the standards work
    elem = document.getElementById( whichLayer );
  else if( document.all ) // this is the way old msie versions work
      elem = document.all[whichLayer];
  else if( document.layers ) // this is the way nn4 works
    elem = document.layers[whichLayer];
  vis = elem.style;
  // if the style.display value is blank we try to figure it out here
  if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)
    vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';
  vis.display = (vis.display==''||vis.display=='block')?'none':'block';
}

/* Disable the form once submitted to prevent multiple hits */
function fb_disableForm(theform) {

	updateReferrerInfo();
	
	
	if (document.all || document.getElementById) {
		for (i = 0; i < theform.length; i++) {
		var tempobj = theform.elements[i];
		if (tempobj.type.toLowerCase() == "submit" || tempobj.type.toLowerCase() == "reset")
		tempobj.disabled = true;
		}
		return true;
	}
	else {
		alert("The form is currently processing. Please be patient.");
		return false;
	}
}

/* ajax.Request */
function fb_ajaxRequest(url,submitdata,fieldname) {
	jx.load(
		url + '?' + submitdata, 
	    function(data){
		      fb_getResponse(data, fieldname);
		}
	);
}

/* ajax.Response */
function fb_getResponse(data, fieldname) {
	document.getElementById(fieldname).innerHTML = data;
}

