jQuery(function($){
	var feedbackList;
	$(document).bind('submitting#feedbackform#window.un', function(event, data){
		data.document = window.parent.document.documentElement.outerHTML;
		data.debug = window.parent.usernoise_debug;
	});
	$.extend(usernoise.window, {
		moveToDiscussion: function(id){
			$('#button-back').fadeIn(100);
			$('#viewport').animate({left: '-421px'}, 150);
		},
		moveToCommentForm: function(id){
			moveToDiscussion(id);
		},
		moveToFeedback:function(){
			$('#viewport').animate({left: '0px'}, 150, function(){
					$('#button-back').fadeOut(100);
					$(document).trigger('movedtofeedback#window.un');
			});

		}
	});
	
	$(document).bind('itemselected#feedbacklist#window.un', function(event, id){
		usernoise.window.moveToDiscussion(id);
	});
	$(document).bind('itemcommentsselected#feedbacklist#window.un', function(event, id){
		usernoise.window.moveToCommentForm(id);
	});
	
	
	function FeedbackList($block){
		var self = this;
		var $block = $block;
		var $list = $block.find('#feedback-list');
		var nextPage = 2;
		var $loadMoreButton = $('#load-more-feedback a');
		var $ajaxLoader = $('#feedback-list-loader');
		var numPages = parseInt($list.attr('data-pages'));
		var currentType = 'idea';
		var $header = $block.find('h3');
		
		function likeHandler(event){
			var $link = $(this);
			$.post(pro.ajaxurl, {
				action: 'unpro_like',
				id: $(this).attr('href').replace('#like-', '')
			}, function(response){
				$link.text(response);
				$link.addClass('un-liked');
				$link.attr('disabled', 'disabled');
				$link.unbind('click');
			});
			event.preventDefault();
			return false;
		}
		
		function loadMoreClickHandler(){
			loadItems(false);
			return false;
		}
		
		function loadItems(removeExisting){
			if (removeExisting)
				nextPage = 1;
			$loadMoreButton.hide();
			$ajaxLoader.show();
			$loadMoreButton.parent().show();
			$.get(pro.ajaxurl, 
				{action: 'unpro_get_feedback', page: nextPage, type: currentType}, function(response){
					response = usernoise.helpers.parseJSON(response);
				if (removeExisting){
					$list.find('li.feedback').remove();
				}
				$(response.html).insertBefore($loadMoreButton.parent()).find('.likes').click(likeHandler);
				$ajaxLoader.hide();
				$list.find('li').click(function(){
					selectItem($(this).find('>a.feedback-title').attr('href').replace('#feedback-', ''));
					return false;
				});
				nextPage = response.next_page;
				if (nextPage)
					$loadMoreButton.show();
				else
					$loadMoreButton.parent().hide();
				createOrUpdateScrollbar();
			});
			return false;
		}
		
		function selectItem(item){
			$list.find('li').removeClass('selected');
			$list.find('li#feedback-' + item).addClass('selected');
			self.selectedFeedbackId = item;
			$(document).trigger('itemselected#feedbacklist#window.un', self.selectedFeedbackId);
		}

		function typeSelectedHandler(event, type){
			$list.find('li.feedback').remove();
			currentType = type;
			$header.text(pro['popular_' + currentType]);
			loadItems(true);
		}
		
		function createOrUpdateScrollbar(){
			$('#feedback-list .viewport').css('height', ($('#window').height() - $('#feedback-list-block h3').height() - 3) + "px");
			if ($('#feedback-list').data('tsb'))
				$('#feedback-list').tinyscrollbar_update('relative');
			else
				$('#feedback-list').tinyscrollbar();
		}

		$loadMoreButton.click(loadMoreClickHandler);
		createOrUpdateScrollbar();
		$list.find('.likes:not(.un-liked)').click(likeHandler);
		$list.find('li').click(function(event){
			if (event.bubbles)
				selectItem($(this).find('>a.un-feedback-title').attr('href').replace('#feedback-', ''));
			return true;
		});

		$('#window').resize(function(){
			if($(this).is(':visible'))
				createOrUpdateScrollbar()
		});
		$(document).bind('typeselected#feedbackform#window.un', typeSelectedHandler);
		$('#button-back').click(function(){
			usernoise.window.moveToFeedback();
			return false;
		});
		$(document).bind('movedtofeedback#window.un', function(){
			$list.find('.un-feedback').removeClass('selected');
		});
		loadItems(true);
	}
	function CommentForm(){
		var self = this;
		var $formWrapper = $('#comment-form-wrapper');
		var $cancelButton = $('#cancel-comment');
		var $submitButton = $('#submit-comment');
		var $form = $formWrapper.find('form');
		var $errorMessage = $('#comment-errors');
		var $errorsWrapper = $('#comment-errors-wrapper');
		var $loader = $('#un-comment-loader');
		var $name = $('#un-comment-name');
		var $email = $('#un-comment-email');
		var $comment = $('#un-comment');
		
		$form.find('.text').unAutoPlaceholder();
		$cancelButton.click(function(){self.cancel(); return false;});
		$form.submit(submitHandler);
		$submitButton.click(function(){$form.submit(); return false;});
		$('#facebox .popup').resize(function(){
			if ($(this).is(':visible'))
				updateScrollbar();
		});
		
		function submitHandler(){
			$errorMessage.width($form.width() - buttonsWidth() - 
				($errorMessage.outerWidth(true) - $errorMessage.innerWidth()) - 24);
			$errorMessage.css('visibility', 'hidden');
			$loader.show();
			$.post(pro.ajaxurl, {action: 'unpro_submit_comment', 
				name: $name.val(), 
				email: $email.val(),
				comment: $comment.val(),
				post_id: feedbackList.selectedFeedbackId
				}, function(response){
					response = usernoise.helpers.parseJSON(response);
					if (response.success){
						pro.discussion.addComment(response.html, response.count);
						$loader.hide();
						$form[0].reset();
						self.cancel();
					} else {
						showErrors(response.errors);
					};
				});
			return false;
		}
		
		function updateScrollbar(){
			$('#comment-list .viewport').css('height', $('#facebox .popup').height() + "px");
			$('#comment-list').tinyscrollbar_update('relative');
		}
		function showErrors(errors){
			$loader.hide();
			$errorMessage.css('visibility', 'visible').fadeIn('fast').text(errors);
		}
		
		function buttonsWidth(){
			return $cancelButton.outerWidth(true) + $submitButton.outerWidth(true);
		}
		
		$.extend(this, {
			show: function(){
				$formWrapper.css('bottom', '-' + $formWrapper.height() + "px").show();
				$formWrapper.animate({bottom: 0}, 150);
			},
			cancel: function(){
				$formWrapper.animate({bottom: '-' + $formWrapper.height() + "px"}, 150, function(){
					$(document).trigger('closedcommentform#window.un');
				});
			}
		});
		
	}
	function Discussion(){
		var self = this;
		var $block = $('#feedback-discussion');
		var $listWrapper = $block.find('#comment-list');
		var $list = $block.find('#comment-list ul');
		var $leaveCommentButton = $('#leave-a-comment');
		var $commentsLoader = $('#comments-loader');
		var $countLabel = $('#comment-count');
		var $commentList = $('#comment-list ul');
		
		var $leaveCommentWrapper = $('#leave-a-comment-wrapper2');
		$('#window').resize(function(){
			if ($(this).is(':visible'))
				updateHeight();
		});
		$leaveCommentButton.click(showCommentForm);
		
		$(document).bind('closedcommentform#window.un', function(){
				$leaveCommentWrapper.animate({bottom: 0}, 150);
		});
		$listWrapper.tinyscrollbar();
		updateHeight();
		self.commentForm = new CommentForm();
		$(document).bind('movedtofeedback#window.un', self.commentForm.cancel);
		$(document).bind('itemselected#feedbacklist#window.un', feedbackSelectedHandler);

		
		function showCommentForm(){
			$leaveCommentWrapper.animate({bottom: "-" + $leaveCommentWrapper.height() + "px"}, 150,
				self.commentForm.show);
			return false;
		}
		function feedbackSelectedHandler(event, itemId){
			self.commentForm.cancel();
			$commentsLoader.show();
			$listWrapper.hide(); 
			$list.html('');
			$.post(pro.ajaxurl, {action: 'unpro_get_comments', post_id: itemId}, commentsLoadedHandler);
		}
		function commentsLoadedHandler(response){
			response = usernoise.helpers.parseJSON(response);
			$list.html(response.comments);
			$commentsLoader.hide();
			$countLabel.text(response.count);
			$listWrapper.fadeIn('fast');
			$listWrapper.tinyscrollbar();
		}
		function updateHeight(){
			$('#feedback-discussion, #comment-list').height(($('#window').height()) + 'px');
			$('#comment-list .viewport').height($('#window').height() - $leaveCommentWrapper.outerHeight() + "px");
			$('#comment-list').tinyscrollbar_update('relative');
		}
		$.extend(self, {
			addComment: function(html, count){
				$commentList.find('li.blank-slate').remove();
				$commentList.append($(html));
				$listWrapper.tinyscrollbar_update('bottom');
				$countLabel.html(count);
			}
		})
	}
	if (pro.enable_discussions){
		feedbackList = new FeedbackList($('#feedback-list-block'));
		pro.discussion = new Discussion();
	}
});