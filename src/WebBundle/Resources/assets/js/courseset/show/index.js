$('#freeprogress').easyPieChart({
    easing: 'easeOutBounce',
    trackColor: '#ebebeb',
    barColor: '#46c37b',
    scaleColor: false,
    lineWidth: 10,
    size: 145,
    onStep: function(from, to, percent) {
        if(Math.round(percent) == 100) {
            $(this.el).addClass('done');
        }
        $(this.el).find('.percent').html('学习进度'+ '<br><span class="num">'+ Math.round(percent)+ '%</span>');
    }
});

$('#orderprogress-plan').easyPieChart({
    easing: 'easeOutBounce',
    trackColor: '#ebebeb',
    barColor: '#fd890c',
    scaleColor: false,
    lineWidth: 10,
    size: 145,
});

$('#orderprogress').easyPieChart({
    easing: 'easeOutBounce',
    trackColor: 'transparent',
    barColor: '#46c37b',
    scaleColor: false,
    lineWidth: 10,
    size: 145,
    onStep: function(from, to, percent) {
        if(Math.round(percent) == 100) {
            $(this.el).addClass('done');
        }
        $(this.el).find('.percent').html('学习进度'+ '<br><span class="num">'+ Math.round(percent)+ '%</span>');
    }
});