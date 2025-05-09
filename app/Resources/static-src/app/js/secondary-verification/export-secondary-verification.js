export default class ExportSecondaryVerification {
  constructor({ buttonSelector, formSelector, requestUrlBase, modalSelector = '#modal', errorToastSelector = '#errorToast' }) {
    this.buttonSelector = buttonSelector;
    this.formSelector = formSelector;
    this.requestUrlBase = requestUrlBase;
    this.modalSelector = modalSelector;
    this.errorToastSelector = errorToastSelector;

    this.bindExportEvent();
  }

  bindExportEvent() {
    $(this.buttonSelector).on('click', async (e) => {
      e.preventDefault();
      const $form = $(this.formSelector);
      const rawParams = Object.fromEntries(new FormData($form[0]));
      rawParams.start = rawParams.start || 0;

      const params = Object.fromEntries(
        Object.entries(rawParams).filter(([_, v]) => v !== undefined)
      );

      try {
        const query = new URLSearchParams(params).toString();
        const url = `${this.requestUrlBase}?${query}`;
        const verificationResponse = await fetch(url);

        if (!verificationResponse.ok) {
          throw new Error(`HTTP error! status: ${verificationResponse.status}`);
        }

        const html = await verificationResponse.text();
        $(this.modalSelector)
          .html(html)
          .modal('show')
          .on('shown.bs.modal', () => {
            $('#verificationForm').validate(); // 可根据实际情况配置
          });
      } catch (error) {
        console.error('Export failed:', error);
        $(this.errorToastSelector).toast('show').find('.toast-body').text(error.message);
      }
    });
  }
}
