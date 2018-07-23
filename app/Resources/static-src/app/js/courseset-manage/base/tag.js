export const initTags = () => {
  const $tags = $('#tags');
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
            return { id: item.name, name: item.name };
          })
        };
      }
    },
    initSelection (element, callback) {
      const data = [];
      $(element.val().split(',')).each(function () {
        data.push({
          id: this,
          name: this
        });
      });
      callback(data);
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
};