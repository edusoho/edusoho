define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        if($('#user-export-btn').data('count') > 20000){
            $('#user-export-btn').addClass('disabled');
        }
        var $modal = $('#user-export-form').parents('.modal');

        var $userSearchForm = $('#user-search-form');
        var roles = $userSearchForm.find('[name=roles]').val(); 
        var keywordType = $userSearchForm.find('[name=keywordType]').val();
        var keyword = $userSearchForm.find('[name=keyword]').val();
        
        var role=$('input[name="roles"]').attr('value',roles);
        var keywordTypes=$('input[name="keywordType"]').attr('value',keywordType);
        var keywords=$('input[name="keyword"]').attr('value',keyword);

        var choices=$('input[name="choices[]"]');
        var checkedChoices = new Array();

        var validator = new Validator({
            element: '#user-export-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
            // $('#user-export-btn').on('click',function(){
            //     $('#user-export-btn').button('submiting').addClass('disabled');
            // });
            //     $('#user-export-btn').submit();
            document.getElementById("user-export-form").submit();
            $modal.modal('hide');
            }
        });

    };

});