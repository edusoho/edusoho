$('body').on('event-report', function(e, name){
  let $obj = $(name);
  let postData = $obj.data();
  $.post($obj.data('url'), postData)
})

$('body').trigger('event-report','#event-report');