	function ShowLatLng() 
	{
		if (GBrowserIsCompatible()) 
		{		
			var geocoder = new GClientGeocoder();
			
			geocoder.getLatLng(
				document.place.address.value,
				function(point) 
				{
					if (!point) 
					{
						alert(document.place.address.value + " 無法轉換成經緯度");
						document.place.lat.value = "";
						document.place.lng.value = "";
					} 
					else 
					{
						document.place.lat.value = point.x;
						document.place.lng.value = point.y;
					}
				}
			);
		}
	}	

	