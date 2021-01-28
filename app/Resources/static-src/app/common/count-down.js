export const countDown = ($dom, num, url) => {
  $dom.text(num);
  if (--num > 0) {
    setTimeout(() => {
      countDown($dom, num, url);
    }, 1000);
  } else {
    window.location.href = url;
  }
};
