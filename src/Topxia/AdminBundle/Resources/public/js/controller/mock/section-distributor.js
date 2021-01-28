define(function(require, exports, module) {

  exports.run = function() {

    $('.sendedType').change(
      function() {
        if ($('.sendedType').val() != '') {
          $('.sendedData').val('');
          $.post(
            $('.sendedType').data('url'), { type: $('.sendedType').val() },
            function(data) {
              $('.sendedData').val(data);
              $('.sendCount').html('(共' + $.parseJSON(data).length + '条)');
            },
            'text'
          );
        }
      }
    );

    $('.tokenGeneratorBtn').click(
      function() {
        $('.distributorGeneratorUrl').val('');
        var distributorDiv = $(this).parents('.subTagContent');
        distributorDiv.find('.distributorGeneratorUrl').val('');
        $.post(
          distributorDiv.find('.tokenGeneratorForm').attr('action'),
          distributorDiv.find('.tokenGeneratorForm').serialize(),
          function(data) {
            let domain = document.domain ? document.domain : document.origin;
            distributorDiv.find('.distributorGeneratorUrl').val(domain + '/' + distributorDiv.find('.distributorGeneratorUrl').data('baseUrl') + '?token=' + data.token);
          }
        );
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