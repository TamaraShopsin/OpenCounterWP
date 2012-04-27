var ThinBox = {
	modals: [],
	remove: function() {
		$(this.modals[0]).data('thinbox').remove();
		return false;
	},
	resize: function(width,height) {
		$(this.modals[0]).data('thinbox').resize(width,height);
		return false;
	},
	onShow: function(self) {}
};
(function($){
	var defaultSettings = {
		width: '417px',
		height: '250px',
		margin: '0 auto',
		top: '10%',
		clickClose: true,
		fullHeight: false,
		animateResize: true,
		showCloseButton: true,
		fullHeightPadding: '30',
		thinboxModalBG: 'thinboxModalBG',
		thinboxModalContent: 'thinboxModalContent',
		thinboxModalContentBG: 'thinboxModalContentBG',
		thinboxModalCloseButton: 'thinboxModalCloseButton',
		thinboxModalContentBGColor: '#000',
		thinboxModalContentBGOpacity: '40',
		//closeButton: '/ui/images/icons/tb-close-button.gif',
		onShow: function() { }
	};
	
	var Thinbox = function(element,options) {
		if(element==null) {
			var singleMode = true;
		} else {
			var singleMode = false;
			element = $(element);
		}
		var self = this;
		this.settings = $.extend(defaultSettings,options||{});
		
		this.init = function() {
			if(!singleMode) {
				self.bindClick();
			}
		};
		
		this.bindClick = function() {
			element.bind('click',function(){
				self.showModal(element);
				return false;
			});
		};
		
		this.getContentElement = function() {
			return $("#"+self.settings.thinboxModalContent);
		};
		
		this.setupModal = function(vars) {
			var thinboxBG = $('<div/>').attr('id',self.settings.thinboxModalBG);
			var thinboxModalContent = $('<div/>').attr('id',self.settings.thinboxModalContent).addClass(self.settings.thinboxModalContent);
			var thinboxModalContentBG = $('<div/>').attr('id',self.settings.thinboxModalContentBG);
			
			$(thinboxBG).css({
				'height': $(window).height(),
				'width': $(window).width(),
				'position': 'fixed',
				'display': 'none',
				'top': '0',
				'left': '0'
			}).appendTo('body');
			
			$(thinboxModalContentBG).css({
				'height': $(window).height(),
				'width': $(window).width(),
				'position': 'absolute',
				'top': '0',
				'left': '0',
				'background': self.settings.thinboxModalContentBGColor,
				'opacity': '.'+self.settings.thinboxModalContentBGOpacity,
				'filter': 'alpha(opacity='+self.settings.thinboxModalContentBGOpacity+')',
				'zIndex': '999999'
			}).appendTo(thinboxBG);
			
			var boxHeight = self.settings.height;
			var boxWidth = self.settings.width;
			if(vars.isImage) {
				boxHeight = '';
				boxWidth = vars.thinboxImage.width;
				if(boxWidth>950) {
					boxWidth = 950;
					$(vars.thinboxContent).css({
						'height': 'auto',
						'width': 950
					});
				}
				if(vars.thinboxImage.height>500) {
					$(vars.thinboxContent).css({
						'height': 500,
						'width': 'auto'
					});
				}
			}
			
			$(thinboxModalContent).css({
				'height': boxHeight,
				'width': boxWidth,
				'margin': self.settings.margin,
				'top': self.settings.top,
				'position': 'relative',
				'zIndex': '9999999'
			}).html(vars.thinboxContent).appendTo(thinboxBG);
			
			if(vars.isImage && vars.imageDesc) $(vars.imageDesc).appendTo(thinboxModalContent);
			
			if(vars.isImage && self.settings.showCloseButton) {
				var imageCloseButton = '<a class="jsThinboxClose '+self.settings.thinboxModalCloseButton+'">X</a>';
				$(imageCloseButton).appendTo(thinboxModalContent);
			}
			
			if(vars.inIframe) {
				$(thinboxModalContent).css({
					'overflow': 'hidden'
				});
			}
			
			if(self.settings.clickClose) {
				$(thinboxBG).bind('click',function(){
					self.remove(); 
				 });
			
				$(thinboxModalContent).bind('click',function(event){  
					if(event.stopPropagation) event.stopPropagation();
					else event.cancelBubble = true;
				});
			}
			
			if(self.settings.fullHeight) {
				self.resizeHeight();
			}
			
			$('.jsThinboxClose',thinboxModalContent).bind('click',function() {
				self.remove();
				return false;
			});
			
			$(window).bind('resize',function(){
				self.resizeBG();
				self.resizeBG(); /* Dirty hack for scrollbars */
				if(self.settings.fullHeight) self.resizeHeight();
			});
			
			$(thinboxBG).show();
			self.settings.onShow(self);
			
			if($.browser.msie && $.browser.version<=6) window.location = "#";
		};
		
		this.showModal = function(element,url) {
			if(url!=null) {
				var href = url;
			} else {
				var href = $(element).attr("href")||$(element).attr("alt")||'';
			}
			var imageUrl = /\.(jpe?g|png|gif|bmp)/gi;
			var isImage = false;
			if(href.substr(0,1)=="#") {
				var thinboxContent = $(href).html();
				var inIframe = false;
			} else if(href.match(imageUrl)) {
				var thinboxImage = new Image();
				var thinboxContent = $("<img>").attr("src",href);
				var imageDesc = $('<p/>').html($($('img',element).attr('title')).html()||"&nbsp;");
				var inIframe = false;
				var isImage = true;
			} else if(href=='') {
				 return false;
			} else {
				var thinboxContent = $("<div style='height:100%;width:100%;background-color:#fff;'><div style='width:100%;padding:4px;background-color:#ddd;text-align:left;'>BETA VERSION<input type='button' value='X' onclick='ThinBox.remove()' style='float:right;margin-right:30px;'/></div><br/><br/><div style='padding:20px;'>OpenCounter is in beta<br/><br/>this will be more of a warning and will be styled soon</div></div>");
				//thinboxContent.attr("src",href);
				var inIframe = true;
			}
			
			var vars = {
				'thinboxContent': thinboxContent,
				'inIframe': inIframe,
				'thinboxImage': thinboxImage,
				'imageDesc': imageDesc,
				'isImage': isImage
			}
			
			if(isImage) {
				thinboxImage.onload = function() {
					self.setupModal(vars);
				}
				thinboxImage.src = href;
			} else {
				this.setupModal(vars);
			}
			
			return false;
		};
		
		this.resizeBG = function() {
			$('#'+self.settings.thinboxModalBG).css({
				'height':$(window).height(),
				'width': $(window).width()
			});
			$('#'+self.settings.thinboxModalContentBG).css({
				'height':$(window).height(),
				'width': $(window).width()
			});
		};
		
		this.resizeHeight = function() {
			var height = parseInt($(window).height())-(parseInt(self.settings.fullHeightPadding)*2);
			$('#'+self.settings.thinboxModalContent).css({
				'height': height,
				'top': self.settings.fullHeightPadding+'px'
			});
		}
		
		this.remove = function() {
			$('#'+self.settings.thinboxModalBG).remove();
			return false;
		};
		
		this.resize = function(width,height) {
			var newSizes = {
				'height': height+'px',
				'width': width+'px'
			};
			if(self.settings.animateResize) {
				$('#'+self.settings.thinboxModalContent).animate(newSizes);
			} else {
				$('#'+self.settings.thinboxModalContent).css(newSizes);
			}
		}
		
		this.init();
	};
	$.fn.thinbox = function(options) {
		ThinBox.options = options;
		return this.each(function(){
			var element = $(this);
			if(element.data('thinbox')) return;
			var thinbox = new Thinbox(this,options);
			element.data('thinbox',thinbox);
		});
	};
	
	$(document).ready(function(){
		ThinBox.modals = $("a[rel*='thinbox'],a.thinbox,input.thinbox").thinbox({
			onShow: function() { ThinBox.onShow(self); }
		});
	});
	
	ThinBox.open = function(url,options) {
		settings = $.extend(defaultSettings,options||{});
		thinbox = new Thinbox(null,settings);
		ThinBox.modals.push($(document).data('thinbox',thinbox));
		thinbox.showModal('',url);
	}
})(jQuery);
