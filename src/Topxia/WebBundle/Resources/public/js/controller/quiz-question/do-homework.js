define(function(require, exports, module) {
	exports.run = function() {
        $(".analysis-btn").click(function()
        {
          $(this).closest('.homework-question-actions').siblings('.homework-question-analysis').show();
          $(this).siblings('.unanalysis-btn').show();
          $(this).hide();
        });
        $(".unanalysis-btn").click(function()
        {
          $(this).closest('.homework-question-actions').siblings('.homework-question-analysis').hide();
          $(this).siblings('.analysis-btn').show();
          $(this).hide();
        })

	}
})