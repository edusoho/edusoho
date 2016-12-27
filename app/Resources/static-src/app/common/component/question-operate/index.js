export default class QuestionOperate {
  constructor($form, $modal) {
    this.$form = $form;
    this.$modal = $modal;
    this.initEvent();
  }

  initEvent() { 
    this.$form.on('click','[data-role="item-delete-btn"]',event=>this.deleteQuestion(event));
    this.$form.on('click','[data-role="replace-item"]',event=>this.replaceQuestion(event));
    this.$form.on('click','[data-role="preview-btn"]',event=>this.previewQuestion(event));
  }

  replaceQuestion(event) {
    let $target = $(event.currentTarget);
    let excludeIds = [];
    let $tbody = this.$form.find("tbody:visible");

    $tbody.find('[name="questionIds[]"]').each(function(){
      excludeIds.push($(this).val());
    })

    this.$modal.data('manager', this).modal();
    $.get($target.data('url'), {excludeIds: excludeIds.join(','), type: $tbody.data('type')}, html => {
      this.$modal.html(html);
    });
  }

  deleteQuestion(event) {
    let $target = $(event.currentTarget);
    let id = $target.closest('tr').data('id');
    let $tbody =  $target.closest('tbody');
    $tbody.find('[data-parent-id="'+id+'"]').remove();
    $target.closest('tr').remove();
    this.refreshSeqs($tbody);
  }

  previewQuestion(event) {
    event.preventDefault();
    window.open($(event.currentTarget).data('url'), '_blank', "directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
  }

  refreshSeqs($tbody) {
    let seq = 0;
    let $tr = $tbody.find('tr');
    $tr.each(function(index,item) {
      let $tr = $(item);
      $tr.find('td.seq').html(seq+1);
    });
    this.$form.find('[name="questionLength"]').val($tr.length > 0 ? $tr.length : null );
  }
}