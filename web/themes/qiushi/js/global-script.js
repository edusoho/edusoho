define(function (require, exports, module) {

	$(".direct-step").on('click','.next',function(){
		var $this = $(this);
		$this.parents('.direct-step').removeClass('active');
		$($this.data('url')).addClass('active');
		if($this.hasClass('step-btn-lg')) {
			$(".direct-nav").show();
		}
		if($this.data('url') == '#step-2') {
			$(".direct-nav #btn-step-2").addClass('btn-warning').removeClass('btn-default')
			.siblings('.btn-warning').removeClass('btn-warning').addClass('btn-default');
		}
		if($this.data('url') == '#step-3') {
			$(".direct-nav #btn-step-3").addClass('btn-warning').removeClass('btn-default')
			.siblings('.btn-warning').removeClass('btn-warning').addClass('btn-default');
		}
		if($this.data('url') == '#step-4') {
			$(".direct-nav #btn-step-4").addClass('btn-warning').removeClass('btn-default')
			.siblings('.btn-warning').removeClass('btn-warning').addClass('btn-default');
		}

	});

	$(".direct-nav").on('click', '.btn',function(){
		var $this = $(this);
		if($this.hasClass('btn-default')) {
			$this.addClass('btn-warning').removeClass('btn-default');
			$this.siblings('.btn-warning').removeClass('btn-warning').addClass('btn-default');
			$('.direct-step').removeClass('active');
			$($this.data('url')).addClass('active');
		}
		
	});
});