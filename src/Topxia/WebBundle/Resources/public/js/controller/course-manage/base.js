define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {
        
        require('./header').run();

            $('#course_tags').select2({
            
                ajax: {
                    url: app.arguments.tagMatchUrl+'#',
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

                        $.each(data, function(index, item){

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
                initSelection : function (element, callback) {
                    var data = [];
                    $(element.val().split(",")).each(function () {
                        data.push({id: this, name: this});
                    });
                    callback(data);
                },
                formatSelection: function(item) {
                    return item.name;
                },
                formatResult: function(item) {
                    return item.name;
                },
                width: 'off',
                multiple: true,
                maximumSelectionSize: 20,
                placeholder: "请输入标签",
                width: 'off',
                multiple: true,
                createSearchChoice: function() { return null; },
                maximumSelectionSize: 20
            });

        var $perLiveMaxStudentNum = $('#perLiveMaxStudentNum').val();
        var $default_perLiveMaxStudentNum = $('#default_perLiveMaxStudentNum').val();

        var $stuNumUpperLimit = $('#stuNumUpperLimit');

        $stuNumUpperLimit.on('input',function(){
            $stuNumUpperLimitVal = $stuNumUpperLimit.val();
            if ( isNaN(Number($stuNumUpperLimitVal)) || Number($stuNumUpperLimitVal) < 0 ) {
                $('#stuNumUpperLimit_help').html("请输入大于0的数字");
                $('#stuNumUpperLimit_help').css("color","red");
                $('#stuNumUpperLimit_help').show();
            }else{
                if (Number($stuNumUpperLimitVal) < Number($default_perLiveMaxStudentNum) && Number($default_perLiveMaxStudentNum)>0) {
                    $('#stuNumUpperLimit_help').html("不能降低学员上限");
                    $('#stuNumUpperLimit_help').css("color","red");
                    $('#stuNumUpperLimit_help').show();
                }else{
                    if(Number($stuNumUpperLimitVal) > Number($perLiveMaxStudentNum)) {
                        $('#stuNumUpperLimit_help').html("超过了管理员设置的人数上限,最多"+$perLiveMaxStudentNum+"人");
                        $('#stuNumUpperLimit_help').css("color","red");
                        $('#stuNumUpperLimit_help').show();
                    }else{
                        $('#stuNumUpperLimit_help').hide();
                    }
                }
            }


        });

        var validator = new Validator({
            element: '#course-form',
            failSilently: true,
            triggerType: 'change'
        });

        validator.addItem({
            element: '[name=title]',
            required: true
        });      
        
        validator.addItem({
            element: '[name=subtitle]',
            rule: 'maxlength{max:70}'
        });

        validator.addItem({
            element: '[name=expiryDay]',
            rule: 'integer'
        });
    };

});