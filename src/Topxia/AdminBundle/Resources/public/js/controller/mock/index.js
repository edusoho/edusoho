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
  };

});