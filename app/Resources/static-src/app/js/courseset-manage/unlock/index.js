class Unlock {
  constructor() {
    this.init();		
  }

  init(){
    $('#courseSync-btn').click(function(){
      var $form = $('#courseSync-form');
      $.post($form.attr('action'), $form.serialize(), function(resp){
        console.log(resp);
        if(resp.success){
          cd.message({ type: 'success', message: Translator.trans('course_set.manage.unlock_success_hint')});
          $('#modal').modal('hide');
          location.reload();
        }else{
          cd.message({ type: 'danger', message: Translator.trans('course_set.manage.unlock_failure_hint')+ resp.message});
        }
      });
    });
  }
}

new Unlock();