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

        // $('.js-task-detail-search').prev().bind('keypress',function(event){
        //     if (13 === event.keyCode) {
        //         let value = $(this).val();
        //         let url = self.$chart.data('url') + '?title=' + value;
        //         self.update(url);
        //     }
        // });
        //
        // $('.js-task-detail-search').on('click',function(){
        //     let value = $(this).prev().val();
        //     let url = self.$chart.data('url') + '?title=' + value;
        //     self.update(url);
        // })
    }

    update(url = ''){
        let self = this;
        if (!url) {
            url = self.$chart.data('url');
        }

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