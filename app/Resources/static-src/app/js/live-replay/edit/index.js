let $tags = $('#tags');
console.log('ddddd');
$tags.select2({
  ajax: {
    url: $tags.data('url'),
    dataType: 'json',
    quietMillis: 500,
    data (term, page) {
      return {
        q: term,
        page_limit: 10
      };
    },
    results (data) {
      console.log(data);
      return {
        results: data.map((item) => {
          return { id: item.id, name: item.name };
        })
      };
    }
  },
  initSelection (element, callback) {
    callback(callback($(element).data('selected')));
  },
  formatSelection (item) {
    return item.name;
  },
  formatResult (item) {
    return item.name;
  },
  formatNoMatches: function() {
    return Translator.trans('validate.tag_required_not_found_hint');
  },
  formatSearching: function() {
    return Translator.trans('site.searching_hint');
  },
  multiple: true,
  maximumSelectionSize: 20,
  placeholder: Translator.trans('course_set.manage.tag_required_hint'),
  width: 'off',
  createSearchChoice () {
    return null;
  }
});

$('#submit-btn').on('click', function (e){
  $('#submit-btn').button('loading');
  $.post($('#replay-form').attr('action'), $('#replay-form').serialize(), function (response) {
    if (response) {
      $('#submit-btn').parents('.modal').modal('hide');
    } else {
      $('.js-delete-btn').button('reset');
    }
  });
});