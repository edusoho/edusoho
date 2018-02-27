define(function(require, exports, module) {

  exports.run = function() {

    $('#tokenGeneratorBtn').click(
      function() {
        $('.distributorRegisterUrl').val('');
        $.post(
          $('#tokenGeneratorForm').attr('action'),
          $('#tokenGeneratorForm').serialize(),
          function(data) {
            $('.distributorRegisterUrl').val(document.origin + '/distributor_register?token=' + data.token);
          }
        );
      }
    );

    $('.mockSelector').change(
      function() {
        $('.tagContent').hide();
        displayedTabContent = $('.mockSelector').val();
        $('.tagContent.' + displayedTabContent).show();
      }
    );

    $('.sendedType').change(
      function() {
        if ($('.sendedType').val() != '') {
          $('.sendedData').val('');
          $.post(
            $('.sendedType').data('url'), { type: $('.sendedType').val() },
            function(data) {
              $('.sendedData').val(data);
            },
            'text'
          );
        }
      }
    );

    $('.sendBtn').click(
      function() {
        if ($('.sendedType') != '') {
          $('.sendResult').html('');
          $.post(
            $('.sendBtn').data('url'), { type: $('.sendedType').val() },
            function(data) {
              $('.sendResult').html(data.result);
            }
          );
        }
      }
    );

    $('.sendMarketingBtn').click(
      function() {
        $('.sendMarketingResult').html('');
        $.post(
          $('.sendMarketingBtn').data('url'), { 'url': $('.defaultUrl').val(), 'body': $('.sendedMarketingData').val() },
          function(data) {
            $('.sendMarketingResult').html(data.result);
          }
        );
      }
    );

    $('.defaultUrl').val('/callback/marketing?ac=orders.accept');
  };

});