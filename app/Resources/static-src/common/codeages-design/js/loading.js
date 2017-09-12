let template = (loadingClass = '') => {
  return `<div class="cd-loading ${loadingClass}">
            <div class="loading-content">
              <div></div>
              <div></div>
              <div></div>
            </div>
          </div>`;
}

$(document).ajaxSend(function(a, b, c) {
  console.log(a, b, c);

  let url = c.url;

  let $dom = $(`[data-url="${url}"]`);

  if (!$dom.data('loading')) {
    return;
  };
  
  let loading;
  if ($dom.data('loading-class')) {
    loading = template($dom.data('loading-class'));
  } else {
    loading = template();
  }

  let loadingBox = $($dom.data('target') || $dom);
  loadingBox.append(loading);
  
});


