define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var TagChooser = require('tag-chooser');
    var TagTreeChooser = require('tag-tree-chooser');
    exports.run = function() {
        _initTagTreeChooser();
        var chooserTreeForCategories;
        function _initTagTreeChooser()
        {
            chooserTreeForCategories = new TagTreeChooser({
              element: '#categories-chooser',
              sourceUrl: $('#categories-chooser').data('sourceUrl'),
              queryUrl: $('#categories-chooser').data('queryUrl'),
              matchUrl: $('#categories-chooser').data('matchUrl'),
              maxTagNum: 1
            });

            chooserTreeForCategories.on('change', function(tags) {
                var tagIds = [];
                $.each(tags, function(i, tag) {
                    tagIds.push(tag.id);
                });
                $('input[name=subjectIds]').val(tagIds.join(','));
            });

        }

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
            onItemValidated: function(error, message, elem) {
                if(elem.val() == '') {
                    elem.next().html('<span class="text-danger">必须选择科目!</span>');
                    elem.next().attr('style', 'display:block');
                    return false;
                }

                $.ajax({
                   type: "GET",
                   url: elem.data('url'),
                   data: {value:elem.val()},
                   async: false,
                   success: function(result){
                        if(!result.success) {
                            elem.next().html('<span class="text-danger">'+result.message+'</span>');
                            elem.next().attr('style', 'display:block');
                            return false;
                        }
                   }
                });
            }
        });
        $('input[name=subjectIds]').on('change', function(){
            $(this).next('.help-block').attr('display', 'none');
        });
    };

});