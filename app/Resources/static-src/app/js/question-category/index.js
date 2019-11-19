import notify from 'common/notify';

class Category {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
    this.initExpand();
  }

  initEvent() {
    $('.js-category-body').on('click', '.delete-btn', function() {
      var countUrl = $(this).data('countUrl');
      var postUrl = $(this).data('url');

      $.get(countUrl, function(result) {
        var count = result.questionCount;
        if (count > 0) {
          cd.confirm({
            title: Translator.trans('question_bank.question_category.delete_confirm_title'),
            content: Translator.trans('question_bank.question_category.delete_confirm_hint', {count: count}),
            okText: Translator.trans('site.confirm'),
            cancelText: Translator.trans('site.cancel'),
          }).on('ok', () => {
            $.post(postUrl, function () {
              window.location.reload();
            }).error(function(error){
              cd.message({type: 'danger', message: Translator.trans('admin.category.delete_fail')});
            });
          }).on('cancel', () => {
          });
        } else {
          cd.confirm({
            title: Translator.trans('question_bank.question_category.delete_confirm_title'),
            content: Translator.trans('admin.category.delete_hint'),
            okText: Translator.trans('site.confirm'),
            cancelText: Translator.trans('site.cancel'),
          }).on('ok', () => {
            $.post(postUrl, function () {
              window.location.reload();
            }).error(function(error){
              cd.message({type: 'danger', message: Translator.trans('admin.category.delete_fail')});
            });
          }).on('cancel', () => {
          });
        }
      });
    });
  }

  initExpand() {
    $('.js-expand').click(function() {
      var $parentNode = $(this).parents('.js-row');
      if ($parentNode.hasClass('row-collapse')) {
        $parentNode.removeClass('row-collapse').addClass('row-expand');
        $(this).children('.es-icon').removeClass('es-icon-chevronright').addClass('es-icon-keyboardarrowdown');
        $parentNode.next('ul.list-table').find('>li').slideDown();
      } else if ($parentNode.hasClass('row-expand')) {
        $parentNode.removeClass('row-expand').addClass('row-collapse');
        $(this).children('.es-icon').removeClass('es-icon-keyboardarrowdown').addClass('es-icon-chevronright');
        $parentNode.next('ul.list-table').find('>li').slideUp();
      }        
    });
  }
}

var category = new Category();