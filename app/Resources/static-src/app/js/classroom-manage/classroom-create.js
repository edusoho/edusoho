const classroomCreate = ()=>{
  if($('#create-classroom').val() != ''){
    if($('#showable-open').data('showable')==1){
      $('#showable-open').attr('checked','checked');
      if($('#buyable-open').data('buyable')==1){
        $('#buyable-open').attr('checked','checked');
      }else{
        $('#buyable-close').attr('checked','checked');
      }
    }
    else{
      $('#showable-close').attr('checked','checked');
      if($('#buyable-open').data('buyable')==1){
        $('#buyable-open').attr('checked','checked');
      }
      else{
        $('#buyable-close').attr('checked','checked');
      }
      $('#buyable').attr('hidden','hidden');
    }
  }
  $('#showable-close').click(function(){
    $('#buyable').attr('hidden','hidden');
  });
  $('#showable-open').click(function(){
    $('#buyable').removeAttr('hidden');
  });
  $('#classroom_tags').select2({

    ajax: {
      url: app.arguments.tagMatchUrl + '#',
      dataType: 'json',
      quietMillis: 100,
      data: function (term, page) {
        return {
          q: term,
          page_limit: 10
        };
      },
      results: function (data) {
        var results = [];
        $.each(data, function (index, item) {

          results.push({
            id: item.name,
            name: item.name
          });
        });

        return {
          results: results
        };

      }
    },
    initSelection: function (element, callback) {
      var data = [];
      $(element.val().split(',')).each(function () {
        data.push({
          id: this,
          name: this
        });
      });
      callback(data);
    },
    formatSelection: function (item) {
      return item.name;
    },
    formatResult: function (item) {
      return item.name;
    },
    width: 'off',
    multiple: true,
    maximumSelectionSize: 20,
    placeholder: Translator.trans('classroom.manage.tag_required_hint'),
    createSearchChoice: function () {
      return null;
    }
  });
};
export default classroomCreate();