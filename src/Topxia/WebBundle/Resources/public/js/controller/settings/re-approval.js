define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var $table = $('#cancel-table');

        $table.on('click', '#cancel-approval', function(){
            
            if (!confirm('确定修改实名信息并重新认证？')) {  return ; }

            $("#cancel-approval-form").submit();

        });
    };

});