define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Cookie = require('cookie');

  exports.run = function () {

    var $form = $("#message-search-form"),
      $modal = $form.parents('.modal'),
      $table = $("#course-table");

    $('#chooser-items').on('click', function (e) {
      let courseIds = Cookie.get('couponSelectCourseIds').split(',');
      let length = courseIds.length;
      if ($('#coupon-course-select-table').length == 1 && length>0) {
        $.post($(this).data('url'), {ids: courseIds}, function(data){
          $('#coupon-course-select-table').find('tbody').html(data);
          Notify.success(Translator.trans('admin.course.choose_success_hint'));
        });
      }
      $modal.modal('hide');
    });

    $modal.on('hidden.bs.modal', function (e) {
      let courseIds = Cookie.get('couponSelectCourseIds').split(',');
      let length = courseIds.length;
      courseIds.splice(0, courseIds.length);
      Cookie.set('couponSelectCourseIds', courseIds);
      if (length<1) {
        $('.js-course-radios').button('reset');
        $('.js-choose-course').hide();
      }
    });

    let deleteVacancy = function(array) {
      $.each(array, function(index, value){
        if (value == '' || value == null) {
          array.splice(index, 1);
        };
      });
      return array;
    };

    var pushArrayValue = function(array, targetValue){
      var isExist = false;
      $.each(array, function(index, value){
        if (value == targetValue) {
          isExist = true;
          return;
        };
      });

      if (!isExist && !isNaN(targetValue)) {
        array.push(targetValue);
      };
    };

    var popArrayValue = function(array, targetValue){
      $.each(array, function(index, value){
        if (value == targetValue) {
          array.splice(index, 1);
        };
      });
    };

    let initChecked = function(array)
    {
      var length = $('.batch-item').length;
      var checked_count = 0;
      courseIds = deleteVacancy(array);

      $('#selected-count').text(array.length);

      $.each(array, function(index, value) {
        $('#batch-item-'+value).prop('checked', true);
      });

      $('.batch-item').each(function(){
        if ($(this).is(':checked')) {
          checked_count++;
        };

        if (length == checked_count) {
          $('.batch-select').prop('checked', true);
        } else {
          $('.batch-select').prop('checked', false);
        }
      });
    };

    $('.courses-list').on('click', '.pagination li', function() {
      var url = $(this).data('url');

      if (typeof(url) !== 'undefined') {
        $.post(url, $form.serialize(),function(data){
          $('.courses-list').html(data);
            initChecked(Cookie.get('couponSelectCourseIds').split(','));
        });
      }
    });

    $('#search').on('click',function(){
      $.post($form.attr('action'), $form.serialize(), function(data){
        $('.courses-list').html(data);
      });
    });

    var courseIds = new Array();
    $('.js-selected-item').each(function(index, el){
      pushArrayValue(courseIds, $(el).data('id'));
    });

    Cookie.set('couponSelectCourseIds', courseIds);

    if (Cookie.get('couponSelectCourseIds').length > 0) {
      initChecked(Cookie.get('couponSelectCourseIds').split(','));
    };

    $('.courses-list').on('click', '.batch-select',function() {
      var $selectdElement = $(this);

      if (Cookie.get('couponSelectCourseIds').length > 0) {
        courseIds = deleteVacancy(Cookie.get('couponSelectCourseIds').split(','));
      };

      if ($selectdElement.prop('checked') == true) {
        $('.batch-item').prop('checked', true);
        $('.batch-item').each(function(index, el){
          pushArrayValue(courseIds, $(this).val());
        });
      } else {
        $('.batch-item').prop('checked', false);
        $('.batch-item').each(function(index, el){
          popArrayValue(courseIds, $(this).val());
        });
      }

      $('#selected-count').text(courseIds.length);
      Cookie.set('couponSelectCourseIds', courseIds);

    });

    $('.courses-list').on('click', '.batch-item',function() {
      var length = $('.batch-item').length;
      var checked_count = 0;

      if (Cookie.get('couponSelectCourseIds').length > 0) {
        courseIds = deleteVacancy(Cookie.get('couponSelectCourseIds').split(','));
      };

      if ($(this).prop('checked') == true) {
        pushArrayValue(courseIds, $(this).val());
      } else {
        popArrayValue(courseIds, $(this).val());
      }

      $('.batch-item').each(function(){
        if ($(this).is(':checked')) {
          checked_count++;
        };

        if (length == checked_count) {
          $('.batch-select').prop('checked', true);
        } else {
          $('.batch-select').prop('checked', false);
        }
      });

      $('#selected-count').text(courseIds.length);
      Cookie.set('couponSelectCourseIds', courseIds);
    });

    $('#clear-cookie').click(function(){
      courseIds = Cookie.get('couponSelectCourseIds').split(',');
      courseIds.splice(0, courseIds.length);
      Cookie.set('couponSelectCourseIds', courseIds);
      $('#selected-count').text(0);
      $('input[type=checkbox]').prop('checked', false);
    });

  };
})