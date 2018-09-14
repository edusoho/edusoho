define(function(require, exports, module) {

  exports.run = function() {

    $('.selectedType').change(
      function() {
        $('.doc').attr('disabled', true);
        var sendMsg = $('.' + $('.selectedType').val()).html();
        $('.sendOtherMsg').val(sendMsg);
        $('.doc').val($('.' + $('.selectedType').val() + '_doc').html());
        $('.api-url').val('');
        $('.api-user-id').val('');

        if ($('.doc').val().indexOf('api-url-editable: true') != -1) {
          $apiInfo = $.parseJSON($('.' + $('.selectedType').val() + '_apiInfo').html());
          $('.api-url').val($apiInfo['apiUrl']);
        }

        if ($('.doc').val().indexOf('api-login: true') != -1) {
          $('.api-user-id').val(1);
        }
      }
    );

    $('.selectedType').change();

    $('.sendOtherBtn').click(
      function() {
        $('.result').html('获取信息中...');
        $apiInfo = $.parseJSON($('.' + $('.selectedType').val() + '_apiInfo').html());
        $url = $('.sendOtherBtn').data('url-version-' + $apiInfo.apiVersion);

        $postData = $.parseJSON($('.sendOtherMsg').val());
        let copiedFields = ['apiMethod', 'apiUrl', 'apiAuthorized'];
        for (let index = 0; index < copiedFields.length; index++) {
          const element = copiedFields[index];
          $postData[element] = $apiInfo[element];
        }

        if ($('.doc').val().indexOf('api-url-editable: true') != -1) {
          $postData['apiUrl'] = $('.api-url').val();
        }

        if ($('.doc').val().indexOf('api-login: true') != -1) {
          $postData['apiUserId'] = $('.api-user-id').val();
        }

        $.post(
          $url, { 'data': $postData },
          function(data) {
            if (typeof data.result.detailedMsg == 'undefined') {
              $('.result').html(JSON.stringify(data));
            } else {
              $('.result').html(data.result.detailedMsg);
            }

            if ($('.doc').val().indexOf('api-authorized: true') != -1) {
              $('.generatedToken').html('');
              $.post(
                $('.generatedToken').data('url'), {},
                function(data) {
                  $('.generatedToken').html(data.result);
                }
              );
            }
          }
        );
      }
    );
  };

});