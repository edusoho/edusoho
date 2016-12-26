export const deleteQuestion = ($form) =>{

  $form.on('click','[data-role="item-delete-btn"]',event => {
    let $target = $(event.currentTarget);
    console.log('delete');
    let id = $target.closest('tr').data('id');
    $target.closest('tbody').find('[data-parent-id="'+id+'"]').remove();
    $target.closest('tr').remove();
    $('tbody:visible tr').each(function(index,item) {
      console.log($(item));
      let $tr = $(item);
      $tr.find('td.seq').html(index+1);
    });
  })
}

export const replaceQuestion = ($form,$modal) => {
  $form.on('click','[data-role="replace-item"]',event => {
    let $target = $(event.currentTarget);
    let excludeIds = [];
    let type = $("tbody:visible").data('type');

    $("tbody:visible").find('[name="questionIds[]"]').each(function(){
      excludeIds.push($(this).val());
    })

    console.log(type);

    $modal.data('manager', this).modal();
    $.get($target.data('url'), {excludeIds: excludeIds.join(','), type: type}, function(html) {
      $modal.html(html);
    });
  });
}

export const previewQuestion = ($form) => {
  $form.on('click','[data-role="preview-btn"]',event => {
    event.preventDefault();
    window.open($(event.currentTarget).data('url'), '_blank', "directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");  
  });
}