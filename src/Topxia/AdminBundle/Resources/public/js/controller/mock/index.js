define(function(require, exports, module) {

  exports.run = function() {

    $('#tokenGeneratorBtn').click(
      function() {
        $('.generatedToken').val('');
        $.post(
          $('#tokenGeneratorForm').attr('action'),
          $('#tokenGeneratorForm').serialize(),
          function(data) {
            $('.generatedToken').val(data.token);
          }
        );
      }
    );
  };

});