import './select2';

$.extend($.fn.select2.defaults, {
    formatSelectionTooBig: function (limit) {
        return Translator.trans('validate.tag_number_exceeds_limit', { limit });
    }
});