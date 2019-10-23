define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
	var Validator = require('bootstrap.validator');
	require('jquery.bootstrap-datetimepicker');
	require('common/validator-rules').inject(Validator);
    require('jquery.form');

	exports.run = function (){
        $('#fixedDay').addClass('disabled').attr('disabled', true);

      $('.save-btn').click(function () {
        $('.save-btn').addClass('disabled');
      });

		$form = $('#coupon-generate-form');

        $form.find('[name="type"]:checked').trigger('change');

        $form.find('[name="courseId"]:checked').trigger('change');
        $form.find('[name="classroomId"]:checked').trigger('change');
        $form.find('[type="checkbox"]:checked').trigger('change');

        var validator = new Validator({
            element: '#coupon-generate-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    $form.find('.save-btn').removeClass('disabled');
                    return false;
                }

                if ($form.find("[data-type='channel']:checked").length === 0) {
                    Notify.danger(Translator.trans('notify.channel_select'));
                    return false;
                }

                $form.find('.save-btn').button('loading');

                $.post($form.attr('action'), $form.serialize())
                .success(function(response) {

                    var pageSize = 500,
                        total = response.num,
                        url = response.url;
                    var pageCount = Math.ceil(total / pageSize);

                    $('.progress').removeClass('hidden');

                    for (var i=1; i<=pageCount; i++){
                        rollCreateBatch(url, pageSize);
                    }

                }).fail(function (xhr, status, error){
                    Notify.danger(xhr.responseJSON.error.message);
                })
            }
        });

        $form.on('change', '[type="checkbox"]', function (e) {
            var target = $('[type="checkbox"]').is(':checked');

            if (target) {
                $('[name="channel"]').val(1);
            } else {
                $('[name="channel"]').val('');
            }
        });

        $form.on('click', '.all-items-input', function(e){
            $('#course-display').hide();
            resetRadioValue();
        });       

        $form.on('click', "[name='page_detail']", function (e) {
            $form.find("[data-channels='page_detail']").prop('checked', $(this).is(':checked'));
        });   
        
        $form.on('click', "[data-channels='page_detail']", function (e) {
            if ($form.find("[data-channels='page_detail']:checked").length > 0) {
                $form.find("[name='page_detail']").prop('checked', true);
            } else {
                $form.find("[name='page_detail']").prop('checked', false);
            }
        });   
        
        $form.on('change', '[name=type]', function(e) {
            var type = $(this).val();
            var minus = $('.minus-rate');
            var discount = $('.discount-rate');

            if (type == 'minus') {
                minus.show();
                discount.hide();
		        validator.addItem({
		        	element: '[name="minus-rate"]',
		        	required: true,
		        	rule:'currency min{min:0.01}'
		        });
                validator.removeItem('[name="discount-rate"]');
            } else if (type == 'discount') {
                discount.show();
                minus.hide();
		        validator.addItem({
		        	element: '[name="discount-rate"]',
		        	required: true,
		        	rule:'max{max:9.99} min{min:0.01} currency'
		        });
                validator.removeItem('[name="minus-rate"]');
            }
        });

        $form.on('change', '[name=deadlineMode]', function(e) {
          var deadlineMode = $(this).val();
    
          if (deadlineMode == 'time') {
            validator.addItem({
              element: '[name="deadline"]',
              required: true,
              rule: 'deadline_date_check'
            });
            validator.removeItem('#fixedDay');
            $('#fixedDay').addClass('disabled').attr('disabled', true);
            $('[name="deadline"]').removeClass('disabled').attr('disabled', false);

          } else if (deadlineMode == 'day') {
            validator.addItem({
              element: '[name="fixedDay"]',
              required: true,
              rule: 'max{max:9999} min{min:1} positive_integer',
              display: Translator.trans('coupon.fixed_day'),
              errormessageRequired: Translator.trans('coupon.fixed_day.message')
            });
            validator.removeItem('[name="deadline"]');
            $('[name="deadline"]').addClass('disabled').attr('disabled', true);
            $('#fixedDay').removeClass('disabled').attr('disabled', false);
          }
        });
        
        validator.addItem({
            element: '#name',
            required: true
        });

        validator.addItem({
        	element: '[name="prefix"]',
        	required: true,
        	rule: 'remote alphanumeric'
        });

        validator.addItem({
        	element: '[name="generatedNum"]',
        	required: true,
        	rule: 'max{max:100} min{min:1} positive_integer'
        });

        validator.addItem({
        	element: '[name="digits"]',
        	required: true,
        	rule: 'max{max:15} min{min:5} positive_integer',
        });

        validator.addItem({
          element: '[name="deadline"]',
          required: true,
          rule: 'deadline_date_check'
        });

        validator.addItem({
            element: '[name="channel"]',
            required: true,
        });

        $form.on('change', '[name=targetType]', function(e) {
            var type = $(this).val();
            if (type == 'fullDiscount') {
                validator.addItem({
                    element: '[name="fullDiscountPrice"]',
                    required: true,
                    rule:'currency min{min:0.01}'
                });
               
            }else{
                 validator.removeItem('[name="fullDiscountPrice"]');
            }

        });        
        $form.find('[name=type]:checked').change();

        $("#coupon-deadline").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        }).on('hide', function(){
            validator.query('#coupon-deadline').execute();
        });

       $('#coupon-deadline').prop('autocomplete', 'off');


    var resetRadioValue = function () {
          $('#choose-course-input').val('');
          $('#choose-classroom-input').val('');
          $('#choose-vip-input').val('');
        }

        var rollCreateBatch = function(url, pageSize) {

            $.post(url, {generateNum: pageSize})
            .success(function(response) {
                $('[role="progressbar"]').css('width',response.percent+'%')
                                         .attr('aria-valuenow', response.percent)
                                         .html(response.percent+'%');

                if (response.goto != '') {
                    window.location.href = response.goto;
                }
            }).fail(function (xhr, status, error){
                Notify.danger(xhr.responseJSON.error);
            })
        }
	};
});