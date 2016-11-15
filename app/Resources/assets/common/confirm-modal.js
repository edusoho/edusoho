function confirmModal(element, cfg = {}) {
    const $el = element;
    let config = {};
    if ($el instanceof jQuery) {
        config = {
            modal: $el.data('target')|| '#confirm-modal',
            cancelText: $el.data('cancelText') || '取消',
            confirmText: $el.data('confirmText') || '确认',
            confirmUrl: $el.data('confirmUrl') || '',
            confirmClass: $el.data('confirmClass') || 'danger',
            confirmType: $el.data('confirmType') || 'post',
            content: $el.data('content') || '',
            icon: $el.data('icon') || 'info-o',
            iconColor: $el.data('iconColor') || 'info'
        }
    } else {
        config = cfg;
    }
    
    function init() {
        fill();
        $(config.modal).on('click','.js-btn-submit', submitEvent);
    }

    function submitEvent() {
        const $this = $(this);
        if (config.confirmType === 'post') {
            $.post(config.confirmUrl,function(){
                $("#confirm-modal").modal('hide');
                location.reload();
            })
        }else {
            
        }
    }

    function template() {

        const confirmIcon = `<i class="crm-icon crm-icon-${config.icon} color-${config.iconColor}"></i>`;
        const confirmContent = config.content;
        const cancelBtn = `<button class="btn btn-lg btn-default" data-dismiss="modal">
                            ${config.cancelText}
                            </button>`;
        let submitBtn = '';                 
        if (config.confirmText) {
            submitBtn = 'post' === config.confirmType ?
                        `<button class="btn btn-lg btn-${config.confirmClass} js-btn-submit" data-url="${config.confirmUrl}">
                        ${config.confirmText}</button>`　: 
                        `<a class="btn btn-lg btn-${config.confirmClass} js-btn-submit" target="_blank" href="${config.confirmUrl}">
                        ${config.confirmText}</a>`;    
        }
        return {
            confirmIcon: confirmIcon,
            confirmContent: confirmContent,
            cancelBtn: cancelBtn,
            submitBtn: submitBtn
        }
    }

    function fill() {
        $(config.modal).on('show.bs.modal',function (e) {
            const $this = $(this);

            $this.html(
                `<div class="modal-dialog modal-confirm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">
                                <span aria-hidden="true">×</span><span class="sr-only">Close</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="confirm-icon">${template().confirmIcon}</div>
                            <div class="confirm-content">${template().confirmContent}</div>
                        </div>
                        <div class="modal-footer">
                            ${template().cancelBtn}
                            ${template().submitBtn}
                        </div>
                    </div>
                </div>`
            )
        })
        
    }

    return {
        init : init
    }
}

export default confirmModal;






