define(function(require, exports, module) {
	var $ = require('jquery');
	require('jquery.autocomplete');

	var onReady = function(options){

		$('#adv-add-modal').on('shown', function() {
            var $this = $(this).html('');
			var position = $(this).data('position');
			var type = $('#adv_position').val();
            $.get($this.data('modal').options.url+type, function(response) {
                $this.html(response);
            });
        });

		$('.edit-adv').click(function(){
			$('#adv-edit-modal').data('url', $(this).data('url'));

		});	

		$('#adv-edit-modal').on('shown', function(){
			var $this = $(this).html('');
            $.get($this.data('url'), function(response) {
                $this.html(response);
            });
		});

		$('.adv-publish-course, .adv-publish-term').click(function(){
			$('#adv-publish-modal').data('url', $(this).data('url'));
		});
		$('.adv-publish-home, .adv-publish-tag').click(function(){
			$('#adv-publish-modal').data('url', $(this).data('url'));
		});

		$('#adv-publish-modal').on('shown', function() {
            var $this = $(this).html('');
            $.get($this.data('url'), function(response) {
                $this.html(response);
            });
        });

        $('.adv-publish-edit').click(function(){
        	$('#adv-publish-edit-modal').data('url', $(this).data('url'));
        });

        $('#adv-publish-edit-modal').on('show', function() {
            var $this = $(this).html('');
            $.get($this.data('url'), function(response) {
                $this.html(response);
            });
        });

		$('.adv_delete').click(function(){
			if(!confirm('确定要删除吗?')) {
				return false;
			}
		});

		$('.publish_adv_delete').click(function(){
			if(!confirm('确定要删除吗?')) {
				return false;
			}
		});

		$('body').on('click', '#jingdong-collect', function() {
			var url = $('#form_url').val();
			if(!url){
				alert('url错误!');
				return false;
			}
			$.get('/admin/adv/jiongdong/collect', {url:url}, function(data){
				if(data.error){
					alert(data.error);
					return;
				}
				$('#form_linkTitle').val(data.linkTitle);
				$('#form_itemId').val(data.itemId);
				$('#form_img').val(data.img);
				$('.control-group').find('img').attr('src', '/files/'+data.img);
				$('.control-group').show();
			});
		});

		$('body').on('click', '#taobao-collect', function() {
			var url = $('#form_linkPath').val();
			if(!url){
				alert('url错误!');
				return false;
			}

			$.get('/admin/adv/taobao/collect', {url:url}, function(data){
				if(data.error){
					alert(data.error);
					return;
				}
				$('#form_linkTitle').val(data.linkTitle);
				$('#form_img').val(data.img);
				$('.control-group').find('img').attr('src', '/files/'+data.img);
				$('.control-group').show();
			});
		});

	}

	exports.bootstrap = function(options) {
		$(onReady(options));
	};
});
