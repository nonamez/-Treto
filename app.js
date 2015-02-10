var ALERT   = jQuery('#alert');
var HEIGHTS = [];

jQuery(document).ready(function() {
	jQuery.getJSON('app.php?get_images', function(images) { 
		generateImageList(images);
	});
	
	jQuery('#img-upload-button-browse').click(function() {
		jQuery('#img-upload-input-file').click();
	});
	
	jQuery('#img-upload-input-file').change(function() {
		document.getElementById('img-upload-input-fake-file').value = this.value.replace(/^.*\\/, ''); // remove 'C:\fakepath\'
		document.getElementById('img-upload-button-upload').removeAttribute('disabled');
	});
	
	jQuery('#img-upload-button-upload').click(function() {
		jQuery(this).button('loading');
		
		ALERT.fadeOut();
		
		jQuery('#img-upload').submit();
	});
});

function generateImageList(images) {
	ALERT.fadeOut();
		
	if (images.length > 0) {
		jQuery.each(images, function(key, value) {
			jQuery('<img/>').attr({
				'src': value,
				'class': 'img-responsive img-thumbnail'
			}).appendTo('#images');
		});

		arrange(200);
	} else
		ALERT.fadeIn();

	jQuery('#img-upload-button-upload').button('reset');
}

// Based on http://blog.vjeux.com/2012/image/image-layout-algorithm-google-plus.html

function getHeight(images, width) {
	width -= images.length * 5;
	
	var h = 0;
	
	for (var i = 0; i < images.length; ++i)
		h += jQuery(images[i]).width() / jQuery(images[i]).height();
	
	return width / h;
}

function setHeight(images, height) {
	HEIGHTS.push(height);
	
	for (var i = 0; i < images.length; ++i) {
		jQuery(images[i]).css({
			width: height * jQuery(images[i]).width() / jQuery(images[i]).height(), 
			height: height
		});		
	}
}

function arrange() {
	var size = window.innerWidth - 50;
	
	var n = 0;
	
	var images = jQuery('#images').find('img');
	
	w: while (images.length > 0) {
		for (var i = 1; i < images.length + 1; ++i) {
			var slice = images.slice(0, i);
			
			var h = getHeight(slice, size);
			
			if (h < 200) {
				setHeight(slice, h);
				
				n++;
				
				images = images.slice(i);
				
				continue w;
			}
		}
	
		setHeight(slice, Math.min(200, h));
		
		n++;
		
		break;
	}
}

