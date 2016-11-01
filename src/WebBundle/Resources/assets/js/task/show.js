class TaskShow {
    constructor(props) {
        this.initPlugin();
    }

    initPlugin() {
        $('[data-toggle="tooltip"]').tooltip();
        // var container = document.getElementById('task-group');
        // var Ps = require('perfect-scrollbar.js');
        
        // Ps.initialize(container, {
        //   wheelSpeed: 2,
        //   wheelPropagation: true,
        //   minScrollbarLength: 20
        // });

        $('#task-group').perfectScrollbar({wheelSpeed:50});
        $("#task-group").scrollTop(30);
        $("#task-group").perfectScrollbar('update');
    }
}

new TaskShow();
