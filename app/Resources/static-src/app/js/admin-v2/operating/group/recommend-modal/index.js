import notify from 'common/notify';

class Recommend {
  constructor($form) {
    this.$form = $form;
    this.init();
  }

  init() {
    this.initValidator();
    this.initEvent();
  }

  initValidator() {
    this.validator = this.$form.validate({
      rules: {
        number: {
          required: true,
          digits: true,
          min: 0,
          max: 10000,
        },
      },
    });
  }

  initEvent() {
    $('#group-recommend-btn').click(e => {
      if (!this.validator.form()) {
        return;
      }
      $(e.currentTarget).button('submiting').addClass('disabled');
      $.post(this.$form.attr('action'), this.$form.serialize(), html => {
        this.$form.parents('.modal').modal('hide');
        notify('success', Translator.trans('admin.group.recommend_success_hint'));
        const $tr = $(html);
        $('#' + $tr.attr('id')).replaceWith($tr);

        if ($tr.data('sort') != null) {
          const $tbody = $('#group-recommend-table').find('tbody');
          const trs = $tbody.find('tr').sort((a, b) => {
            return $(a).data('sort') - $(b).data('sort');
          });

          $tbody.find('tr').remove();
          for (let tr in trs) {
            if (!isNaN(parseInt(tr, 10))) {
              $(trs[tr]).appendTo($tbody);
            }
          }
        }
      }).error(function () {
        notify('danger', Translator.trans('admin.group.recommend_fail_hint'));
      });
    });
  }
}

new Recommend($('#group-recommend-form'));