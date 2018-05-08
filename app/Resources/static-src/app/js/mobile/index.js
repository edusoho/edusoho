$('.js-mobile-item').waypoint(function(){
  $(this).addClass('active');
},{offset:500});

$('.es-mobile .btn-mobile').click(function(){
  $('html,body').animate({
    scrollTop: $($(this).attr('data-url')).offset().top + 50
  },300);
});

