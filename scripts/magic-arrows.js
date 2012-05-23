jQuery.fn.extend({    

    magic_arrows: function(options) {

        that = this;
        this.each(function(item) {    

        jQuery(window).resize(function(e) {

return;
                var browser_width = jQuery(window).width();
                var browser_height = jQuery(window).height();
                var selector = '.'+jQuery(that).attr('class') + ' a ';
                //console.log(selector);
				if( browser_width < 600 ){
                    for(var a=0;a<that.length;a++){
                    	var mything = that[a];
                    	var selector = '.'+jQuery(mything).attr('class') + ' a ';
	                    if(jQuery(mything).hasClass('alignleft')){
	                        //console.log('left pixel');
	                        jQuery(selector).css('left', '15px');
	                        jQuery(selector).css('right', '');
	                        jQuery(selector).css('position', 'absolute');
	                    }
   	             		if(jQuery(mything).hasClass('alignright')){
                        	//console.log('right pixel');
	                        jQuery(selector).css('left', '');
	                        jQuery(selector).css('right', '15px');
                        	jQuery(selector).css('position', 'fixed');                                      
                        }
                    }
                }
				else if( browser_width < 750 ){
                    for(var a=0;a<that.length;a++){
                    	var mything = that[a];
                    	var selector = '.'+jQuery(mything).attr('class') + ' a ';
	                    if(jQuery(mything).hasClass('alignleft')){
	                        //console.log('left pixel');
	                        jQuery(selector).css('left', '15px');
	                        jQuery(selector).css('right', '');
	                        jQuery(selector).css('position', 'absolute');
	                    }
   	             		if(jQuery(mything).hasClass('alignright')){
                        	//console.log('right pixel');
                        	jQuery(selector).css('left', '590px');
	                        jQuery(selector).css('right', '');
                        	jQuery(selector).css('position', 'absolute');                                      
                        }
                    }
                }
                else if( browser_width < 1111 ){ 
                    /*if(jQuery(that).hasClass('alignleft')){
                        //console.log('left pixel');
                        jQuery(selector).css('left', '15px');                    
                        jQuery(selector).css('position', 'absolute');                                      
                    }
                    if(jQuery(that).hasClass('alignright')){
                        console.log('right pixel');
                        jQuery(selector).css('right', '15px');
                        jQuery(selector).css('position', 'absolute');                                      
                    }*/
                    for(var a=0;a<that.length;a++){
                    	var mything = that[a];
                    	var selector = '.'+jQuery(mything).attr('class') + ' a ';
	                    if(jQuery(mything).hasClass('alignleft')){
	                        //console.log('left pixel');
	                        jQuery(selector).css('left', '15px');
	                        jQuery(selector).css('right', '');
	                        jQuery(selector).css('position', 'absolute');
	                    }
   	             		if(jQuery(mything).hasClass('alignright')){
                        	//console.log('right pixel');
                        	jQuery(selector).css('left', '');
                        	jQuery(selector).css('right', '10%');
                        	jQuery(selector).css('position', 'fixed');                                      
                        }
                    }
                }
                else{
                    for(var a=0;a<that.length;a++){
                    	var mything = that[a];
                    	var selector = '.'+jQuery(mything).attr('class') + ' a ';
	                    if(jQuery(mything).hasClass('alignleft')){
	                        //console.log('left percent');
	                        jQuery(selector).css('left', '15%');
	                        jQuery(selector).css('right', '');
	                        jQuery(selector).css('position', 'fixed');                                      
	                    }
	                    if(jQuery(mything).hasClass('alignright')){
	                        //console.log('right percent');
	                        jQuery(selector).css('right', '15%');
	                        jQuery(selector).css('left', '');
	                        jQuery(selector).css('position', 'fixed');
	                    }
                    }

                }
            });
        });

//
//        this.each(function(item) {    
//            that = this;
//            var content_height = jQuery(options.selector).height();
//            var content_width = jQuery(options.selector).width();
//            
//            jQuery(window).resize(function(e) {
//
//                var browser_width = jQuery(window).width();
//                var browser_height = jQuery(window).height();
//
//                //console.log('.'+jQuery(that).attr('class'));
//                //console.log(options.selector);
//                
//                o = overlaps( '.'+jQuery(that).attr('class'), jQuery(options.selector) );
//                console.log(o);
//                if( o ){                  
//                    console.log('overlap');
//                }
//                
//            });
//        });
//        

    }
    
});



jQuery(document).ready(function(){
    
    
    
    /* typing left and right arrow keys jumps to previous and next pages */
  jQuery(document.body).bind("keyup", function(event){
    console.log(event);
    if(event.which == 37){   // Left arrow key
        window.location = jQuery( ".alignleft" ).children()[0].href;
    }
    else if(event.which == 39){   // Right arrow key
        window.location = jQuery( ".alignright" ).children()[0].href;
    }
});  
    
    //jQuery('.alignright, .alignleft').magic_arrows({selector: '#content'});

/* JavaScript that should appear on the page somewhere where jQuery is also being used */
/* inside jQuery.ready(document) {  } if you can find one */


jQuery("#sidebar").mouseover( function() {
   /* mouse on top of specialnav */
   jQuery("#sidebar").css( { "left": "0" } );
} );

jQuery("#sidebar").mouseout( function() {
   /* mouse on top of specialnav */
   jQuery("#sidebar").css( { "left": "-130px" } );
} );

//jQuery(window).on("resize", function() {
//  var contentWidth = jQuery("#page").width();
//  var body = jQuery(document.body);
//  if (window.innerWidth < contentWidth) {
//    body.addClass("svelte-window");
//  }
//  else {
//    body.removeClass("svelte-window");
//  }
//});

    
});

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
               
               /* if it is > 500px in height when displayed, add a closing button at the bottom */
                /* if it is < 150px in height, move the closing button up */
              
                jQuery(".aj-hidden").each(function(){
                    this.style.display = "block";
                    if(this.offsetHeight > 500){
                      jQuery(this).append( "<p class='closebtn2'><a class='aj-collapse' rel='" + this.id + "'>x</a></p>" );
                    }
                    else if(this.offsetHeight < 150){
                      this.firstChild.style.marginTop = "-10px";
                    }
                    this.style.display = "none";
                    jQuery(this).hide();
                });
               
		//jQuery(".aj-hidden").hide();
                
                
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

