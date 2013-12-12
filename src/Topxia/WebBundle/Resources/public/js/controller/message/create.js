define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var Notify = require('common/bootstrap-notify');
    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {

    $("#message_receiver").select2({

        createSearchChoice:function(term, data) 
        { 
            if ($(data).filter(function() { return this.name.localeCompare(term)===0; }).length===0) 
                { return {id:term, name:term}; } 
        },
        ajax: {
            url: app.arguments.followingMatchByNickname+'#',
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
        multiple: false,
        placeholder: "请输入收信人昵称",
        maximumSelectionSize: 20

    });

        var validator = new Validator({
            element: '#message-create-form'
        });

        validator.addItem({
            element: '[name="message[receiver]"]',
            required: true,
            rule: 'remote'
        });

        validator.addItem({
            element: '[name="message[content]"]',
            required: true,
            rule: 'maxlength{max:500}',
            errormessageMaxlength: '想要说的话不能大于500个字'
        });
        
        $('#message-create-btn').on('click','',function(){
            var $self = $(this);
            $self.attr('disabled','disabled');
            setTimeout(function() {
                $self.attr('disabled',false);
             }, 3000);
        });

    };

});