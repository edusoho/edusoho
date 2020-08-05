export default class Manage {
  constructor(element) {
    this.$element = $(element);
    this._event();
  }

  _event() {
    let self = this;

    $('body').on('click', '[data-position]', function(e) {
      let $this = $(this);

      self.position = $this.data('position');
      self.type = $this.data('type');
    });
    this._collapse();
  }

  _collapse() {
    this.$element.on('click', '.js-toggle-show', (event) => {
      let $this = $(event.currentTarget);
      $this.toggleClass('toogle-hide');
      let $chapter = $this.closest('.task-manage-item');
      let until = $chapter.hasClass('js-task-manage-chapter') ? '.js-task-manage-chapter' : '.js-task-manage-chapter,.js-task-manage-unit';
      let $hideElements = $chapter.nextUntil(until);

      if ($this.hasClass('js-toggle-unit')) {
        $hideElements.toggleClass('unit-hide');
      } else {
        $hideElements = $hideElements.not('.unit-hide');
      }

      $hideElements.stop().animate({ height: 'toggle', opacity: 'toggle' }, 'fast');
    });
  }
}