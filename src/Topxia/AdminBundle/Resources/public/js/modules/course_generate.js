define(function(require, exports, module) {

	var $ = require('jquery');
	require('jquery.ui.sortable')($);
	require('jquery.form')($);

	exports.bootstrap = function(options) {
		$(function(options) {
			var $lessonList = $("#stack-items");

			$("#stack-items").sortable({
				placeholder: "ui-state-highlight",
				update: function(e, ui) {
					$("#stack-items > li").each(function(index, item) {
						$(item).find('.index').html(index + 1);
					});
				}
			});

			$('#generate-form').ajaxForm({
				dataType: 'json',
				beforeSubmit: function() {
					$('#generate-form').find('input[type=submit]').val('正在生成课程，请稍等。。。').attr('disabled', 'disabled');
				},
				success: function(data) {
					$('#generate-form').find('.actions').hide();
					var courseUrl = '/course/' + (data.uri ? data.uri : data.id);
					var html = '<div class="done-state">课程已经成功生成!</div>';
					html += '<div>';
					html += ' <a href="' + courseUrl + '" class="Button mhl" target="_blank">查看课程</a>';
					html += ' <a href="' + courseUrl + '/admin/picture" class="Button mhl" target="_blank">设置课程图标</a>';
					html += ' <a href="' + $('#return-url').val() + '" class="Button mhl">继续导入课程</a>';
					html += '</div>';

					$('#generate-form').append(html);
				},
				error: function() {
					alert('课程生成失败，可能是课程域名已被使用！');
					$('#generate-form').find('input[type=submit]').val('生成课程').removeAttr('disabled');
				}
			});

			$('#flip-btn').click(function(){

				$("#stack-items > li").detach().each(function(i, $item){
					$("#stack-items").prepend($item);
				});

				$("#stack-items > li").each(function(index, item) {
					$(item).find('.index').html(index + 1);
				});

			});

			$('#smart-sort-btn').click(function() {
				var items = {};
				var seqs = [];

				$("#stack-items > li").each(function(index, item) {
					var title = $(item).find('input').val();
					var result = /.*?(\d+)$/.exec(title);
					if (result) {
						var seq = parseInt(result[1], 10);
						if (!items[seq]) {
							items[seq] = $(item).detach();
							seqs.push(seq);
						}
					}
				});

				if (seqs.length === 0) {
					alert('喔！让我休息一会儿，你自己排吧！');
					return ;
				}

				seqs.sort(function(a,b) {
					return a-b;
				});

				var others = $("#stack-items > li").detach();
				$.each(seqs, function(i, seq) {
					$("#stack-items").append(items[seq]);
				});
				$("#stack-items").append(others);

				$("#stack-items > li").each(function(index, item) {
					$(item).find('.index').html(index + 1);
				});


			});

		});
	};

});
