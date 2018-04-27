define(function(require, exports, module) {

  exports.run = function() {

    $('.sendMarketingBtn').click(
      function() {
        $('.sendMarketingResult').html('');
        $.post(
          $('.sendMarketingBtn').data('url'), { 'url': $('.marketingUrl').val(), 'body': $('.sendedMarketingData').val() },
          function(data) {
            $('.sendMarketingResult').html(data.result);
          }
        );
      }
    );

    $('.marketingUrl').val('/callback/marketing?ac=orders.accept');
  };

});