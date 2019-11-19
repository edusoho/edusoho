import notify from 'common/notify';
import { toggleIcon } from 'app/common/widget/chapter-animate';

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
    $('.js-toggle-show').on('click', (event) => {
      let $this = $(event.target);
      let $sort = $this.closest('.js-sortable-item');
      $sort.nextUntil('.js-sortable-item').animate({
        height: 'toggle',
        opacity: 'toggle'
      }, "normal");
      toggleIcon($sort, 'cd-icon-add', 'cd-icon-remove');
    });
  }
}

var category = new Category();