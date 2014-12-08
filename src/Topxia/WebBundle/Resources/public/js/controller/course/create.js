define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var TagChooser = require('tag-chooser');
    var TagTreeChooser = require('tag-tree-chooser');
    exports.run = function() {
        var type = $('[name=type]').val();
        _initTagTreeChooser();
        var chooserTreeForCategories;
        function _initTagTreeChooser()
        {
            chooserTreeForCategories = new TagTreeChooser({
              element: '#categories-chooser',
              sourceUrl: $('#categories-chooser').data('sourceUrl'),
              queryUrl: $('#categories-chooser').data('queryUrl'),
              matchUrl: $('#categories-chooser').data('matchUrl'),
              maxTagNum: type === 'package' ? 15 : 1
            });

            chooserTreeForCategories.on('change', function(tags) {
                var tagIds = [];
                $.each(tags, function(i, tag) {
                    tagIds.push(tag.id);
                });
                console.log(tagIds);
                $('input[name=subjectIds]').val(tagIds);
            });

        }

        Validator.addRule('checkSubjectAvailable', function(options, commit) {
           $.ajax({
              type: "GET",
              url: options.element.data('url'),
              data: {value:options.element.val()},
              async: false,
              success: function(result){
                   if(!result.success) {
                       options.element.next().html('<span class="text-danger">'+result.message+'</span>');
                       options.element.next().attr('style', 'display:block');
                       return false;
                   } else {
                        commit('true');
                   }
              }
           });
        });

        var validator = new Validator({
            element: '#course-create-form',
            triggerType: 'change',
            onFormValidated: function(error){

                if (error) {
                    return false;
                }

                $('#course-create-btn').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="title"]',
            required: true
        });

         validator.addItem({
            element: 'input[name=subjectIds]',
            required: true,
            display: '学科',
            rule: 'checkSubjectAvailable'
        });
    };

});