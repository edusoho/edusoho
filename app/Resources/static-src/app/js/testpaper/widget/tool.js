export const initScrollbar = ()=> {
	$('.js-task-testpaper-body-iframe').perfectScrollbar();
  $('.js-panel-card').perfectScrollbar();
}

export const testpaperCardFixed =()=> {
	let $testpaperCard = $(".js-testpaper-card");
	if($testpaperCard.length<=0) {
		return;
	}
	let testpaperCard_top = $testpaperCard.offset().top;
	$(window).scroll(function(event) {
			let scrollTop = $(window).scrollTop();
			if (scrollTop >= testpaperCard_top) {
				$testpaperCard.addClass('affix')
			} else {
				$testpaperCard.removeClass('affix');
			}
	});
}

export const testpaperCardLocation = () => {
	$('.js-btn-index').click((event)=>{
		let $btn = $(event.currentTarget).addClass('doing');
    $btn.siblings('.doing').removeClass('doing');
    let $current = $($btn.data('anchor'));
		let $testpaperBodyIframe = $('.js-task-testpaper-body-iframe');
		//iframe中的滚动事件
		if($testpaperBodyIframe.length>0) {
			$(".js-task-testpaper-body-iframe").scrollTop($current.offset().top);
			$(".js-task-testpaper-body-iframe").perfectScrollbar('update');
		}
		else {
			$("body").scrollTop($current.offset().top);
		}
	})
}

export const onlyShowError = ()=> {
	$('#showWrong').change((event)=>{
		let $current =$(event.currentTarget);
		$('.js-answer-notwrong').each(function (index,item) {  
			let $item = $($(item).data('anchor')).slideToggle();
			// let $itemParent = $item.closest('js-testpaper-question-block');
			// if($itemParent.find('.js-testpaper-question:visible').length<=0) {
			// 	$itemParent.hide();
			// }else {
			// 	console.log('ok');
			// 	$itemParent.show();
			// }
		});
	})
}