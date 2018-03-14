echo.init();

const carousel = () => {

  const $this = $('#autumn-carousel .carousel-inner .item');

  for (let i = 0; i < $this.length; i++) {
    if (i == 0) {
      const html = '<li data-target="#autumn-carousel" data-slide-to="0" class="active"></li>';
      $this.parents('.carousel-inner').siblings('.carousel-indicators').append(html);
    }else {
      const html = '<li data-target="#autumn-carousel" data-slide-to="'+i+'"></li>';
      $this.parents('.carousel-inner').siblings('.carousel-indicators').append(html);
    }
  }
};
carousel();