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

    $('.sendedType').change(
      function() {
        if ($('.sendedType') != '') {
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
  };

});