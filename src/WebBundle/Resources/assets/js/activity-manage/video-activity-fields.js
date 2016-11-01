
const $parentIframe = $(window.parent.document).find('#task-manage-content-iframe');


const $iframe = $("iframe");

$("#iframe").height($iframe.contents().find('body').height());
$parentIframe.height($parentIframe.contents().find('body').height());

//TODO validate