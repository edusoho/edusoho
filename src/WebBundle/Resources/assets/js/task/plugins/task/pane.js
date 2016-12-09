class TaskPane {
  constructor(option) {
    this.plugin= option.plugin;
    this.toolbar = this.plugin.toolbar;
    this.$element = option.element;
    this.init();
  }

  init() {
    this.$element.perfectScrollbar();
  }

  show() {
    this.toolbar.showPane(this.plugin.code);
  }

}
export default TaskPane;
