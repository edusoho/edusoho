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
      let countUrl = $(this).data('countUrl');
      let postUrl = $(this).data('url');

      $.get(countUrl, function(result) {
        let count = result.questionCount;
        let content = count > 0 ? Translator.trans('question_bank.question_category.delete_confirm_hint', {count: count}) : Translator.trans('admin.category.delete_hint');
        cd.confirm({
          title: Translator.trans('question_bank.question_category.delete_confirm_title'),
          content: content,
          okText: Translator.trans('site.confirm'),
          cancelText: Translator.trans('site.cancel'),
        }).on('ok', () => {
          $.post(postUrl, function() {
            window.location.reload();
          }).error(function(error) {
            cd.message({type: 'danger', message: Translator.trans('admin.category.delete_fail')});
          });
        }).on('cancel', () => {
        });
      });
    });
  }

  initExpand() {
    $('.js-toggle-show').on('click', (event) => {
      let $this = $(event.target);
      let $sort = $this.closest('.js-sortable-item');
      $sort.children('.js-sortable-list').animate({
        height: 'toggle',
        opacity: 'toggle'
      }, "normal");
      this.toggleIcon($this, 'cd-icon-add', 'cd-icon-remove');
    });
  }

  toggleIcon($icon, $expandIconClass, $putIconClass) {
    if ($icon.hasClass($expandIconClass)) {
      $icon.removeClass($expandIconClass).addClass($putIconClass);
    } else {
      $icon.removeClass($putIconClass).addClass($expandIconClass);
    }
  }
}

new Category();