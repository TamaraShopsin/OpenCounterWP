
jQuery.fn.extend({    



    magic_arrows: function(options) {

        that = this;
        this.each(function(item) {    

        jQuery(window).resize(function(e) {


                var browser_width = jQuery(window).width();
                var browser_height = jQuery(window).height();
                var selector = '.'+jQuery(that).attr('class') + ' a ';
                //console.log(selector);
                if( browser_width < 1111 ){ 
                    if(jQuery(that).hasClass('alignleft')){
                        //console.log('left pixel');
                        jQuery(selector).css('left', '15px');                    
                        jQuery(selector).css('position', 'fixed');                                      
                    }
                    if(jQuery(that).hasClass('alignright')){
                        console.log('right pixel');
                        jQuery(selector).css('right', '15px');  
                        jQuery(selector).css('position', 'fixed');                                      
                    }                              
                }
                else{
                    if(jQuery(that).hasClass('alignleft')){
                        //console.log('left percent');
                        jQuery(selector).css('left', '15%');
                        jQuery(selector).css('position', 'fixed');                                      

                    }
                    if(jQuery(that).hasClass('alignright')){
                        console.log('right percent');
                        jQuery(selector).css('right', '15%');                    
                        jQuery(selector).css('position', 'fixed');                                      
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
    jQuery('.alignright, .alignleft').magic_arrows({selector: '#content'});
});