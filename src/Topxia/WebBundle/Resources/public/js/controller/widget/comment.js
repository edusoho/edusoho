define(function(require, exports, module) {

	var Widget = require('widget');

	var CommentWidget = Widget.extend({
		events: {
			'submit .comment-widget-form': 'createComment',
			'click .comment-delete-btn': 'deleteComment',
		},

		setup: function() {
			var element = this.element,
				objectType = element.data('objectType'),
				objectId = element.data('objectId');

			$.get(element.data('initUrl'), {objectType:objectType, objectId:objectId}, function(response){
				element.html(response);
			});

		},

		createComment: function(e) {
			e.preventDefault();

			var element = this.element,
				$form = element.find('.comment-widget-form');
			$.post($form.attr('action'), $form.serialize(), function(response) {
				element.find('.media-list').prepend(response);
				$form.find('textarea').val('');
			});
		},
		deleteComment: function(e) {
			if (!confirm('真的要删除该评论吗？')) {
				return ;
			}

			var $btn = $(e.target),
				$comment = $btn.parents('.comment');

			$.post($btn.data('url'), function() {
				$comment.remove();
			},'json');

		}
	});


	module.exports = CommentWidget;
	});