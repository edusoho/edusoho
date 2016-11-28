import notify from 'common/notify';

export const closeCourse = () => {
	$('body').on('click', '.js-close-course', function(evt){
		if(!confirm(Translator.trans('是否确定关闭该教学计划？'))){
			return ;
		}
		$.post($(evt.target).data('url'), function(data){
			console.log(data);
			if(data.success){
				notify('success', '关闭成功');
				location.reload();
			}else{
				notify('danger', '关闭失败：' + data.message);
			}
		});
	});
}

export const deleteCourse = () => {
	$('body').on('click', '.js-delete-course', function(evt){
		if(!confirm(Translator.trans('是否确定删除该教学计划？'))){
			return ;
		}
		$.post($(evt.target).data('url'), function(data){
			console.log(data);
			if(data.success){
				notify('success', '删除成功');
				location.reload();
			}else{
				notify('danger', '删除失败：' + data.message);
			}
		});
	});
}

export const publishCourse = () => {
	$('body').on('click', '.js-publish-course', function(evt){
		if(!confirm(Translator.trans('是否确定发布该教学计划？'))){
			return ;
		}
		$.post($(evt.target).data('url'), function(data){
			console.log(data);
			if(data.success){
				notify('success', '发布成功');
				location.reload();
			}else{
				notify('danger', '发布失败：' + data.message);
			}
		});
	});
}

export const showSettings = () => {
  $("#sortable-list").on('click','.js-item-content',event=>{
    var $this = $(event.currentTarget).closest('.js-task-manage-item');
    $this.siblings(".js-task-manage-item.active").removeClass('active').find('.js-settings-list').slideToggle();
    $this.addClass('active').find('.js-settings-list').slideToggle();
  }) ;
  // $("#sortable-list").on('click','.js-settings-item.active',event=>{
  //   return false;
  // });
}

export const TabChange = () => {
	$('[data-role="tab"]').click(function(){
	  let $this = $(this);
	  console.log($($this.data('tab-content')));
	  let $tabContent =$($this.data('tab-content')).removeClass("hidden").siblings('[data-role="tab-content"]').addClass('hidden');
		console.log($tabContent);
	});
}