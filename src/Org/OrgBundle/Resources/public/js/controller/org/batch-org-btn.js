define(function(require, exports, module) {
    var Notify = require("common/bootstrap-notify");
    exports.run = function() {

        var initDom = function() {
            var formId = $('#batch-update-org').data('formId'); 
            var generate =  $('#batch-update-org').data('generate'); 
            if(generate,generate === false){
                return;
            }
            $("#" + formId).find('thead tr').prepend('<th><input type="checkbox"  data-role="batch-select" id="batch-select"></th>');
            $("#" + formId).find('tbody tr').prepend('<td><input type="checkbox"  data-role="batch-item"></td>');
        }

        var getCheckstatus = function(ischeck) {
            var status = true;
            $("#batch-select").parents('table').find("[data-role='batch-item']").each(function() {
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
                $("#batch-select").parents('table').find("[data-role='batch-item']").prop("checked", true)
            } else {
                $("#batch-select").parents('table').find("[data-role='batch-item']").prop("checked", false)
            }
        });

        $("#batch-select").parents('table').on('click', "[data-role='batch-item']", function() {
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