// export default class StudentDetail{
//     constructor($chart) {
//         this.courseId = $chart.data('courseId');
//         //test
//         $.ajax({
//             type: "GET",
//             beforeSend: function(request) {
//                 request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
//                 request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
//             },
//             data: {},
//             url: '/api/course/' + this.courseId + '/report/student_detail',
//             success: function(resp) {
//                console.log(resp);
//             }
//         });
//     }
// }

export default class StudentDetail{
    constructor($chart) {
        this.$chart = $chart;
        this.$form = $($chart.data('form'));
        this.init();
    }

    init(){
        let self = this;
        this.update();
        this.legend();
        this.$chart.on('click', '.pagination a', function(){
            let $this = $(this);
            let url = $this.attr('href');
            self.update(url);
            return false;
        });

        this.$form.find('select').change(function(){
            self.update();
        });

        let $nameSearch = this.$form.find('.js-name-search');
        $nameSearch.on('keypress',function(event){
            if (13 === event.keyCode) {
                self.update();
                return false;
            }
        });

        $nameSearch.next().on('click',function(){
            self.update();
        })
    }

    update(url = ''){
        let self = this;
        let data = this.$form.serialize();
        url = url||this.$chart.data('url')+'?'+data;
        $.get(url,function(html){
            self.$chart.html(html);
            $("[data-toggle='popover']").popover();
        });
    }

    legend(){
        let self = this;
        this.$chart.on('click', '.js-legend-btn',function() {
            let $this = $(this);
            self.$chart.find($this.data('barClass')).parent().toggleClass('hide');
        })
    }
}