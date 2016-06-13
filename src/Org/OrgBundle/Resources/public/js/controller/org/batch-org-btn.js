define(function(require, exports, module) {
    var Notify = require("common/bootstrap-notify");
    exports.run = function() {

        var initDom = function() {
            var formId = $('#batch-update-org').data('formId');
            $("#" + formId).find('thead tr').prepend('<th><input type="checkbox" id="batch-select" name="batch-select"></th>');
            $("#" + formId).find('tbody tr').prepend('<td><input type="checkbox" name="select"></td>');
        }

        var getCheckstatus = function(ischeck) {
            var status = true;
            $("#batch-select").parents('table').find('input[name="select"]').each(function() {
                if ($(this).prop("checked") === ischeck) {
                    status = false;
                    return;
                }
            });
            return status;
        }

        initDom();

        $("#batch-select").on('click', function() {
            if ($("#batch-select").prop('checked')) {
                $("#batch-select").parents('table').find('input[name="select"]').prop("checked", true)
            } else {
                $("#batch-select").parents('table').find('input[name="select"]').prop("checked", false)
            }
        });

        $("#batch-select").parents('table').on('click', 'input[name="select"]', function() {
            $("#batch-select").prop('checked', getCheckstatus(false));
        })

        $("#batch-update-org").on('click', function(e) {
            if (getCheckstatus(true)) {
                Notify.warning('请先选择数据');
                e.stopImmediatePropagation();
            }
        })
    };
});