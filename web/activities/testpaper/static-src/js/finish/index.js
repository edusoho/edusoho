class Testpaper {
  constructor() {
    this.$form  = $('#step3-form');
    window.ltc.on('getContent', (msg) => {
      this.initScoreSlider(msg.context.score);
    });

    $('#finish-type').on('selectChange', function(e, value){
      if ('score' == value) {
        $('#score-condition').show();
      }
    });
  }

  initScoreSlider(score) {
    $('.js-score-total').text(score);
    let passScore = Math.round(score * $('#score-condition').data('pass'));
    score = parseInt(score);

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

    if(this.scoreSlider) {
      this.scoreSlider.updateOptions(option);
    }else {
      this.scoreSlider = noUiSlider.create(scoreSlider, option);
      scoreSlider.noUiSlider.on('update', function(values, handle ){
        let rate = values[handle]/score;
        let percentage = (rate*100).toFixed(0);
        $('.noUi-tooltip').text(`${percentage}%`);
        $('.js-score-tooltip').css('left',`${percentage}%`);
        $('.js-passScore').text(Math.round(percentage / 100 * score ));
        $('#finish-data').val(percentage/100);   
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