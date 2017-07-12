export default class StudentDetail{
    constructor($chart) {
        this.courseId = $chart.data('courseId');
        //test
        $.ajax({
            type: "GET",
            beforeSend: function(request) {
                request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
                request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
            },
            data: {},
            url: '/api/course/' + this.courseId + '/report/student_detail',
            success: function(resp) {
               console.log(resp);
            }
        });
    }
}