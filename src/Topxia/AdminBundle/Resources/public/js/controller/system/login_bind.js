define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var validator = new Validator({
                element: '#login_bind-form'
            });

        $('[name=enabled]').change(function(e) {
            var radio = e.target.value;
            if (radio == '1') {

                $('[name=weibo_enabled]').change(function(f){
                    var weibo_radio = f.target.value;
                    if (weibo_radio == '1'){
                        validator.addItem({
                            element: '[name="weibo_key"]',
                            required: true,
                            errormessageRequired: '请输入App Key'
                        });
                        validator.addItem({
                            element: '[name="weibo_secret"]',
                            required: true,
                            errormessageRequired: '请输入App Secret'
                        })    
                    } else {
                        validator.removeItem('[name="weibo_key"]');
                        validator.removeItem('[name="weibo_secret"]');
                    }
                })

                $('[name=qq_enabled]').change(function(g){
                    var qq_radio = g.target.value;
                    if (qq_radio == '1'){
                        validator.addItem({
                            element: '[name="qq_key"]',
                            required: true,
                            errormessageRequired: '请输入App ID'
                        });
                        validator.addItem({
                            element: '[name="qq_secret"]',
                            required: true,
                            errormessageRequired: '请输入App Secret'
                        })    
                    } else {
                        validator.removeItem('[name="qq_key"]');
                        validator.removeItem('[name="qq_secret"]');
                    }
                })

                $('[name=renren_enabled]').change(function(h){
                    var renren_radio = h.target.value;
                    if (renren_radio == '1'){
                        validator.addItem({
                            element: '[name="renren_key"]',
                            required: true,
                            errormessageRequired: '请输入App Key'
                        });
                        validator.addItem({
                            element: '[name="renren_secret"]',
                            required: true,
                            errormessageRequired: '请输入App Secret'
                        })    
                    } else {
                        validator.removeItem('[name="renren_key"]');
                        validator.removeItem('[name="renren_secret"]');
                    }
                })

            } 
        });

        $('input[name="enabled"]:checked').change();
        $('input[name="weibo_enabled"]:checked').change();
        $('input[name="qq_enabled"]:checked').change();
        $('input[name="renren_enabled"]:checked').change();

    };

});