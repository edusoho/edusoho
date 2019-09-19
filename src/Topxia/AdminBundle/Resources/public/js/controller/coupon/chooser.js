define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function (){
    let $form = $("#coupon-search-form");
    this.$modal  = $form.parents('.modal');
    let self = this;
    this.$attachmentModal = $('#attachment-modal', window.parent.document);
    $form.submit(function (e) {
      e.preventDefault();
      $.get($form.attr('action'), $form.serialize(), function (html) {
        self.$modal.html(html);
      });
    });

    this.$attachmentModal.on('shown.bs.modal', () => {
      this.$modal.modal('hide');
    });
    this.$attachmentModal.on('hidden.bs.modal', () => {
      this.$modal.modal('show');
      this.$attachmentModal.html('');
      $('body').addClass('modal-open');
    });

    $('.js-resource-list').click(function (e) {
      self.$attachmentModal.modal().data('manager', this);
      $.post($(this).data('url'), function (html) {
        self.$attachmentModal.html(html);
      });
    });

    $('.js-chooser').click(function (e) {
      let type = $(this).data('type');
      let batch = $(this).data('batch');
      let content = '';
      $(`#${type}_user_batchId`, window.parent.document).val($(this).data('batchId'));
      let $tbody = $(`.js-${type}-user-content`, window.parent.document).find('tbody');
      if(batch.couponContent == 'multi'){
        if(batch.targetType == 'course'){
          content = Translator.trans('coupon.target_type.multi_course');
        }else if(batch.targetType == 'classroom'){
          content = Translator.trans('coupon.target_type.multi_classroom');
        }
      }else{
          content = batch.couponContent;
      }
      let html = `<tr>
                   <td>${batch.name}</td>
                   <td>${batch.prefix}</td>
                   <td> <span>${ $(this).data('content') }</span><br><span class="text-muted text-sm">${ content }</span></td>
                   <td><a href="javascript:;" class="js-remove-item" >删除</a></td>
                   </tr>`;
      $tbody.html(html);
      self.$modal.modal('hide');

    });
	}
});