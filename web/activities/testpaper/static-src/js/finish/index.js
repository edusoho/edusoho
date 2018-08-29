class Testpaper {
  constructor() {
    this.$form  = $('#step3-form');
    $('#condition-select').on('change',event=>this.changeCondition(event));
    window.ltc.on('getContent', (msg) => {
      console.log(msg);
      this.initScoreSlider(msg.context.score, msg.context.passScore);
    });
  }

  changeCondition(event) {
    let $this = $(event.currentTarget);
    let value = $this.find('option:selected').val();
    value!='score' ? $('.js-score-form-group').addClass('hidden') : $('.js-score-form-group').removeClass('hidden');
  }

  getItemsTable(url, testpaperId) {
    $.post(url, {testpaperId:testpaperId},function(html){
      $('#questionItemShowTable').html(html);
      $('#questionItemShowDiv').show();
    });
  }

  initScoreSlider(score, passScore) {
    $('.js-score-total').text(score);
    passScore = passScore ? passScore : Math.ceil(score * 0.6);
    score = parseInt(score);
    passScore = parseInt(passScore);

    let scoreSlider = document.getElementById('score-slider');
    let option = {
      start: passScore,
      connect: [true, false],
      tooltips: [true],
      step: 1,
      range: {
        'min': 0,
        'max': score
      }
    };
    console.log(option);
    if(this.scoreSlider) {
      this.scoreSlider.updateOptions(option);
    }else {
      this.scoreSlider = noUiSlider.create(scoreSlider, option);
      scoreSlider.noUiSlider.on('update', function( values, handle ){
        $('.noUi-tooltip').text(`${(values[handle]/score*100).toFixed(0)}%`);
        $('.js-score-tooltip').css('left',`${(values[handle]/score*100).toFixed(0)}%`);
        $('.js-passScore').text(parseInt(values[handle]));
        $('input[name="finishScore"]').val(parseInt(values[handle]));
      });
    }
    
    let tooltipInnerText = Translator.trans('activity.testpaper_manage.pass_score_hint', {'passScore': '<span class="js-passScore">'+passScore+'</span>'});
    let html = `<div class="score-tooltip js-score-tooltip"><div class="tooltip top" role="tooltip" style="">
      <div class="tooltip-arrow"></div>
      <div class="tooltip-inner ">
        ${tooltipInnerText}
      </div>
      </div></div>`;
    $('.noUi-handle').append(html);
    $('.noUi-tooltip').text(`${(passScore/score*100).toFixed(0)}%`);
    $('.js-score-tooltip').css('left',`${(passScore/score*100).toFixed(0)}%`);
  }

}

new Testpaper();