import TaskDetail from './task-detail';

class CourseDashboard{
    constructor() {
        this.init();
        this.timeSelectEvent();
        this.tabToggle();
        this.charts();
    }

    init(){
        this.$timeSlectBtn = $('.is-date-change');
    }

    timeSelectEvent(){
        this.$timeSlectBtn.on('click', function() {
            let type = $(this).data('type');
            let time = $(this).data('time');
            $.post(url, {
                type: type,
                time: time
            }).done(() => {
                console.log('success');
            }).fail(() => {
                console.log('error');
            })
        });
    }

    tabToggle(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            let $target = $(e.target);
            let $content = $($target.attr('href'));
            $content.trigger('init');
        })
    }

    charts(){
        let self = this;
        $('#task-data-detail').on('init', function(){
            if (self.taskDetail) return;
            self.taskDetail = new TaskDetail($('#task-data-chart'));
        })

    }
}

let courseDashboard = new CourseDashboard();