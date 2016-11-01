class TaskShow {
    constructor(props) {
        this.initPlugin();
    }

    initPlugin() {
        $('[data-toggle="tooltip"]').tooltip();

        $('#task-group').perfectScrollbar({wheelSpeed:50});
        $("#task-group").scrollTop(30);
        $("#task-group").perfectScrollbar('update');
    }
}

new TaskShow();
