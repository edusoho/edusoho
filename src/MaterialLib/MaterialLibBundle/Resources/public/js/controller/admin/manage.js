define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");
    var Widget = require('widget');

    exports.run = function() {

        var MaterialWidget = Widget.extend({
            attrs: {
                renderUrl: ''
            },
            events: {
                'submit': 'submitForm',
                'click .nav-tabs li': 'onClickNav'
            },
            setup: function() {
                this.set('renderUrl', this.element.find('#materials-table').data('url'));
                this.renderTable();
                this._initHeader();
            },
            onClickNav: function(event)
            {
                var $target = $(event.currentTarget);
                $target.closest('.nav').find('.active').removeClass('active');
                $target.addClass('active');
                $target.closest('.nav').find('[name=type]').val($target.data('value'));
                event.preventDefault();
            },
            submitForm: function(event)
            {
                this.renderTable();
                event.preventDefault();
            },
            renderTable: function()
            {
                var $table = this.element.find('#materials-table');
                $.get(this.get('renderUrl'), this.element.serialize(), function(resp){
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