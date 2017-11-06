class Confirm {
  constructor(props) {
    Object.assign(this, {
      title: '',
      content: '',
      confirmText: 'confirm',
      cancelText: 'cancel',
      confirmClass: 'btn cd-btn cd-btn-flat-danger cd-btn-lg',
      cancelClass: 'btn cd-btn cd-btn-flat-default cd-btn-lg',
      dialogClass: 'cd-modal-dialog cd-modal-dialog-sm',
      confirmType: '',
      confirmUrl: '',
    }, props);

    this.init();
  }

  init() {
    let html = this.template();
    let $modal = $(html);

    this.initEvent($modal);

    $('body').append($modal);
    $modal.modal({
      backdrop: 'static',
      keyboard: false,
      show: true
    });
  }

  initEvent($modal) {
    $modal.on('hidden.bs.modal', () => {
      $modal.remove();
    });

    $modal.on('click', '[data-toggle="cd-confirm-btn"]', event => this.confirm(event, $modal));
  }

  confirm(event, $modal) {
    let $this = $(event.currentTarget);
    let url = $this.data('url');

    if (!url) {
      return;
    }

    if (this.confirmType) {
      let promise = $.ajax({
        type: this.confirmType,
        url,
      }).always(() => {
        $modal.modal('hide');
      })

      this.ajax && this.ajax(promise);

    } else {
      window.location = url;
    }
  }

  template() {
    let modalHeader = this.title ? `
      <div class="modal-header">
        <h4 class="modal-title">${this.title}</h4>
      </div>
    ` : '';

    let modalBody = `
      <div class="modal-body">
        <div class="cd-pb24 cd-text-gray-dark">
          ${this.content}
        </div>
      </div>
    `;

    let confirmBtn = `
      <button class="${this.confirmClass}" type="button" data-toggle="cd-confirm-btn" data-url="${this.confirmUrl}">
        ${this.confirmText}
      </button>
    `;

    let modalFooter = `
      <div class="modal-footer">
        <button class="${this.cancelClass}" type="button" data-dismiss="modal">
          ${this.cancelText}
        </button>
        ${confirmBtn}
      </div>
    `;

    return `
      <div class="modal fade">
        <div class="modal-dialog ${this.dialogClass}">
          <div class="modal-content">
            ${modalHeader}
            ${modalBody}
            ${modalFooter}
          </div>
        </div>
      </div>
    `;

  }
}

function confirm(props) {
  return new Confirm(props);
}

export default confirm;