import FileAllChoose from '../file-chooser/file-all-choose';

const fileChoose = new FileAllChoose($('#chooser-upload-panel'));

const onSelectFile = file => {
  if (file.length && file.length > 0) {
    let minute = parseInt(file.length / 60);
    let second = Math.round(file.length % 60);
    $("#minute").val(minute);
    $("#second").val(second);
    $("#length").val(minute * 60 + second);
  }

  let $metas = $('[data-role="metas"]');
  let idEle = $metas.data('idsClass');
  let listEle = $metas.data('listClass');

  $.get('/attachment/file/'+ file.id +'/show', function(html){

    $('.'+listEle).append(html);
    $('.'+idEle).val(file.id);
    $('.modal').modal('hide');
    
    $('.'+listEle).siblings('.js-upload-file').hide();
  })


};

fileChoose.on('select', onSelectFile);

