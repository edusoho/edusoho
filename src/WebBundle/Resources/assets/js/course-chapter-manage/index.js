
let sortList = function($list) {
    let data = $list.sortable("serialize").get();
    $.post($list.data('sortUrl'), {ids:data}, function(response){
        let lessonNum = chapterNum = unitNum = 0;

        $list.find('.item-lesson, .item-chapter').each(function() {
            let $item = $(this);
            if ($item.hasClass('item-lesson')) {
                lessonNum ++;
                $item.find('.number').text(lessonNum);
            } else if ($item.hasClass('item-chapter-unit')) {
                unitNum ++;
                $item.find('.number').text(unitNum);
            } else if ($item.hasClass('item-chapter')) {
                chapterNum ++;
                unitNum = 0;
                $item.find('.number').text(chapterNum);
            }
        });
    });
};

let $form = $('#course-chapter-form');
let validator = $form.validate({
  rules: {
    title: 'required'
  },
  ajax: true,
  submitSuccess: function(data) {
    document.location.reload();
  }
});

// let formValidated = function(error, msg, $form) {
//   if (error) {
//       return ;
//   }
//   $('#course-chapter-btn').button('submiting').addClass('disabled');

//   $.post($form.attr('action'), $form.serialize(), function(html) {

//       let id = '#' + $(html).attr('id'),
//           $item = $(id);
//       let $parent = $('#'+$form.data('parentid'));
//       let $panel = $('.lesson-manage-panel');
//       $panel.find('.empty').remove();
//       if ($item.length) {
//           $item.replaceWith(html);
//           Notify.success(Translator.trans('信息已保存'));
//       } else {
//          if($parent.length){
//           let add = 0;
//            $parent.nextAll().each(function(){
//              if($(this).hasClass('js-chapter')){
//                 $(this).before(html);
//                 add = 1;
//                 return false;
//               }
             
//           });
//              if(add != 1 )
//                 $("#course-item-list").append(html);
           
//             let $list = $("#course-item-list");
//             sortList($list);
           
//          }else{
//             $("#course-item-list").append(html);
//             $(".lesson-manage-panel").find('.empty').remove();
//          }

//           Notify.success(Translator.trans('添加成功'));

//       }
//       $(id).find('.btn-link').tooltip();
//       $form.parents('.modal').modal('hide');
//   });

// };


