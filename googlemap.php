<?
	include "./config.php";
	
	$lat = $_GET["lat"];
	$lng = $_GET["lng"];
	$title = urldecode($_GET["title"]);
	$address = urldecode($_GET["address"]);
	
	$title = sprintf("\"%s\"", $title);
	$address  = sprintf("\"%s\"", $address );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html> 
<head> 
    <meta http-equiv="content-type" content="text/html; charset= UTF-8" /> 
    <title>IF活動場地資料，Google Map</title> 


	<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key=<? print $config["gmap_key"]; ?>" type="text/javascript"></script>
    <script type="text/javascript">
	
    var map = null;
	
    function initialize() 
	{
		if (GBrowserIsCompatible()) 
		{
			map = new GMap2(document.getElementById("map")); 
			map.addControl(new GLargeMapControl());   
			map.addControl(new GMapTypeControl());                 
			map.addControl(new GScaleControl());                 			
			map.addControl(new GOverviewMapControl());		
			map.enableScrollWheelZoom();
			map.addMapType(G_PHYSICAL_MAP); 
			
			var x = <? print $lng; ?>;
			var y = <? print $lat;?>;
			var title = <? print $title; ?>;
			var address = <? print $address; ?>;
			address = "<b>" + title + "<//b>" + "<br//>" + address;
			
			setMaker( x, y, title, address)
		}
		else
		{
			document.getElementById("map").innerHTML = '您的瀏覽器無法顯示Google Map';
		}
    }
	
	function createMarker(point, title, html)  
    { 
        var marker = new GMarker(point); 
         
        GEvent.addListener(marker, "click", 
					function()  
					{ 
						map.setCenter( point , 16 );
						marker.openInfoWindowHtml( html, { maxContent: html, maxTitle: title} ); 
					}
		); 
        return marker; 
    }
	
	function setMaker( x, y, title, address)
	{		
		point = new GLatLng(x, y);
		map.setCenter( point , 16 );		
		var marker = createMarker( point, title, address); 		
        map.addOverlay(marker);
        marker.openInfoWindowHtml(address);
	}

    </script>

</head> 
<body onload="initialize();" onunload="GUnload()"> 
<div align="center">
        <div id="map" style="width: 500px; height: 400px"> 
        </div> 
</div>

</body> 
</html>
