define(function(require, exports, module) {

    exports.run = function () {

        $("input[name='wechat_acctsetup_setup[level]']").change(function () {
            var element = $(this);
            if (element.val() == 'level_authsub_ordinserv' || element.val() == 'level_authserv') {
                $("#adv").show();
            } else {
                $("#adv").hide();
        }/**/
    });
};
});