define(function(require, exports, module) {

  exports.run = function() {

    $('.selectedType').change(
      function() {
        var sendMsg = $('.' + $('.selectedType').val()).html();
        $('.sendOtherMsg').val(sendMsg);
        $('.doc').val($('.' + $('.selectedType').val() + '_doc').html());
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

        $.post(
          $url, { 'data': $postData },
          function(data) {
            $('.result').html(JSON.stringify(data));
          }
        );
      }
    );
  };

});