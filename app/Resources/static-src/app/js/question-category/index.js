class Category {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
    this.initExpand();
  }

  initEvent() {
    let adjustment;
    $('.js-sortable-list').sortable({
      distance: 20,
      itemSelector: '.js-sortable-item',
      placeholder: '<li class="js-sortable-item placeholder"></li>',
      isValidTarget: function ($item, container) {
        const $targetContainerItems = $(container.items).not('.placeholder');
        if ($targetContainerItems.length > 0) {
          if ($targetContainerItems.data('parentId') == $item.data('parentId')) {
            return true;
          }
        }
        return false;
      },
      onDragStart: function(item, container, _super) {
        const offset = item.offset(),
        pointer = container.rootGroup.pointer;
        adjustment = {
          left: pointer.left - offset.left,
          top: pointer.top - offset.top,
          reTop: container.rootGroup.relativePointer.top
        };
        _super(item, container);
      },
      onDrag: function(item, position) {
        const height = item.height();
        const depth = item[0].getAttribute('depth')
        const top = depth === '3' ? 28 : 0
        
        if (depth === '3') {
          item.css({
            left: position.left - adjustment.left,
            top: item[0].offsetTop > adjustment.reTop ? position.top - adjustment.top - top : position.top - adjustment.top + 20
          });
        } else {
          item.css({
            left: position.left - adjustment.left,
            top: position.top - adjustment.top
          });
        }
        
        $('.js-placehoder').css({
          'height': height,
        });
      },
      onDrop: (item, container, _super) => {
        const sortedItems = container.el.find('>li');
        let ids = [];
        sortedItems.each(function (i) {
          const $item = $(sortedItems.get(i));
          ids.push($item.data('id'));
        }); 
        $.post(item.closest('ul').data('sortUrl'), {
          ids: ids
        }, function (response) {
          if(!response.success) {
            cd.message({ type: 'warning', message: response.message });
          }
        });
        _super(item, container);
      }
    })
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