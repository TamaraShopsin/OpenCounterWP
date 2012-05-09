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

jQuery(window).on("resize", function() {
  var contentWidth = jQuery("#page").width();
  var body = jQuery(document.body);
  if (window.innerWidth < contentWidth) {
    body.addClass("svelte-window");
  }
  else {
    body.removeClass("svelte-window");
  }
});

    
});