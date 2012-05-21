jQuery.noConflict();



jQuery(document).ready(function(){
/*
 * jQuery scrollToShow plugin v0.1
 *
 * Copyright (c) 2010 Rob Brackett, rob@robbrackett.com (http://robbrackett.com/)
 * Licensed under the MIT license.
 * 
 * 
 * Scrolls the window to either show all of a node or, if the node is taller 
 * than the window, to the top of the node.
 * 
 * Automatically uses the first node of a jQuery set.
 * Optionally call the static $.scrollToShow(node) with a DOM node instead.
 */
 
(function($) {
	/**
	 * Scroll to show the first element of a jQuery set.
	 * @param {String} [speed=500] The duration of the scrolling animation
	 * @param {Number} [pad=20] Space to leave above the element
	 * @param {Function} [callback] Callback to invoke after the scrolling is done
	 */
     console.log($);
	$.fn.scrollToShow = function(speed, pad, callback) {
		if (this.length) {
			$.scrollToShow(this[0], speed, pad);
		}
		return this;
	};
	
	/**
	 * Scroll to show a DOM node
	 * @param {HTML Element} node The DOM node/HTML element to scroll to
	 * @param {String} [speed=500] The duration of the scrolling animation
	 * @param {Number} [pad=20] Space to leave above the element
	 * @param {Function} [callback] Callback to invoke after the scrolling is done
	 */
	$.scrollToShow = function(node, speed, pad, callback) {
		pad = pad || 20;
		speed = speed || 500;
		var ht   = node.offsetHeight,
		    top  = $(node).offset().top,
		    wHt  = $(window).height(),
		    dest = window.pageYOffset || document.body.scrollTop || document.documentElement.scrollTop || 0;
		
		// Scroll up if below the top of the node
		if (dest > top - pad) {
			dest = top - pad;
		}
		// Scroll down if node bottom is below window bottom
		else if (dest + wHt < top + ht) {
			if (pad + ht > wHt) {
				// window can't contain node, scroll top of node
				dest = top - pad;
			}
			else {
				// Put the bottom of the node at the bottom of the window
				dest = top + ht - wHt;
			}
		}
		else {
			return;
		}	
		$([document.body, document.documentElement]).animate({scrollTop: dest}, speed, callback);
	};
	
})(jQuery);







		/*	here we loop through and hide any element
			which has the classname .aj_hidden
		*/
               
                /*jQuery(".aj-hidden").each(function(){
                    console.log(this.offsetHeight);
                  if(this.offsetHeight > 500){
                      jQuery(this).append( "<p class='closebtn2'><a class='aj-collapse' rel='" + this.id + "'>x</a></p>" );
                  }
                });
                console.log(jQuery(".aj-hidden"));*/
               
               
		jQuery(".aj-hidden").hide();
		/*	now that the element is hidden, we add a
			class which tells it to set its visibility 
			to visible.
			
			We do this because we set the visibility
			of any aj_hidden to "hidden" in our CSS.  
			This helps prevent the contents of any collapsible
			element from being visible for a spilt second
			during the initial page load.
		*/
		jQuery(".aj-hidden").addClass("aj-visible");
		jQuery(".aj-collapse").click(function() {
		  rel = jQuery(this).attr('rel');
          setTimeout( function(){
            //window.scrollBy(0, document.getElementById(rel).offsetHeight);
            jQuery( "#" + rel ).scrollToShow();  
          }, 250);
		  jQuery("#" + rel).slideToggle('fast');

		});
});
