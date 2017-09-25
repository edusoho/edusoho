export const buyBtn  = ($element) => {
  $element.on('click', event => {
    $.post($(event.currentTarget).data('url'), resp => {
      if (typeof resp === 'object') {
        window.location.href = resp.url;
      } else {
        $('#modal').modal('show').html(resp);
      }

    });
  });
};


export const exitBtn = ($element) => {
 $element.on('click', event => {
   cd.confirm({
     title:Translator.trans('confirm.warn'),
     content: Translator.trans('confirm.exit.tip'),
     confirm() {
       $.post($(event.currentTarget).data('url'), resp => {
         window.location.href = resp.url;
       });
     }
   });
 });

};