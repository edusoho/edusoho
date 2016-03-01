define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");
    var Widget = require('widget');

    exports.run = function() {

        var MaterialWidget = Widget.extend({
            attrs: {
                renderUrl: ''
            },
            events: {

            },
            setup: function() {
                this.set('renderUrl', this.element.find('#materials-table').data('url'));
                this.renderTable();
                this._initHeader();
            },
            renderTable: function()
            {
                var $table = this.element.find('#materials-table');
                $.get(this.get('renderUrl'), function(resp){
                    $table.find('tbody').html(resp);
                });
            },
            _initHeader: function()
            {
                //init timepicker
                $("#startDate").datetimepicker({
                    autoclose: true,
                }).on('changeDate',function(){
                    $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
                });

                $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));

                $("#endDate").datetimepicker({
                    autoclose: true,
                }).on('changeDate',function(){

                    $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));
                });

                $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
            }
        });

        new MaterialWidget({
            element: '#materials-form'
        });
        
    }

});