define(function(require, exports, module) {

  let distributor = require('/bundles/topxiaadmin/js/controller/mock/section-distributor');
  let marketing = require('/bundles/topxiaadmin/js/controller/mock/section-marketing');
  let other = require('/bundles/topxiaadmin/js/controller/mock/section-other');

  exports.run = function() {

    distributor.run();
    marketing.run();
    other.run();

    $('.tagContentSelector').change(
      function() {
        $('.tagContent').hide();
        displayedTabContent = $('.tagContentSelector').val();
        $('.tagContent.' + displayedTabContent).show();
      }
    );

    $('.subTagContentSelector').change(
      function() {
        $('.subTagContent').hide();
        displayedTabContent = $('.subTagContentSelector').val();
        $('.subTagContent.' + displayedTabContent).show();
      }
    );
  };

});