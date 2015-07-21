define(function(require, exports, module) {

  exports.run = function() {
    tabActive();
    carousel();
    outline();
    tooltip();
    ajaxTeacherPage();
    classTab();
    ajaxCoursePage();
    goTop();
    courseNav();
  }

  var Share=require('../js/share');
  Share.create({
      selector: '.es-share',
      icons: 'itemsAll',
      display: 'dropdownWithIcon'
  });

  $("#es-sns").on('click', '#favorite-btn', function() {
      var $btn = $(this);
      $.post($btn.data('url'), function(){
          $btn.hide();
          $("#unfavorite-btn").show();
      });
  });

  $("#es-sns").on('click', '#unfavorite-btn',function() {
      var $btn = $(this);
      $.post($btn.data('url'), function(){
          $btn.hide();
          $("#favorite-btn").show();
      });
  });

  $('.announcement-list').on('click', '[data-role=delete]', function(){
    if (confirm('真的要删除该公告吗？')) {
        $.post($(this).data('url'), function(){
            window.location.reload();
        });
    }
    return false;
  });

  var courseNav = function() {

    var $body = $(document.body);

    $body.scrollspy({
      target: '.course-nav',
      offset: 40
    });

    $(window).on('load', function () {
      $body.scrollspy('refresh');
    });

    $('#course-nav').affix({
      offset: {
        top: 400
      }
    });

    $('#course-nav').on('click', 'ul a', function(event) {
      event.preventDefault();
      var position = $($(this).data('anchor')).offset();
      var top = position.top - 30;
      $(document).scrollTop(top);
    });

  }

  var goTop = function() {
    $("#gotop").click(function() {
      return $("body,html").animate({
        scrollTop: 0
      }, 800), !1
    });
  }

  var classTab = function() {
    if ( $(".class-tab .tab-pane").length == 0) {
      $(".class-tab .options a").removeClass('sub');
    }
  }

  var ajaxTeacherPage = function() {
    $("#teacher-list").on('click', '.show-more a', function(){

      var url = $(this).attr('data-url');

      $.ajax({
        url: url,
        dataType: 'html',
        success: function(html) {
          var html = $('#teacher-list .row .col-md-6,#teacher-list .show-more', $(html)).fadeIn('slow');

          $(".show-more").remove();
          $('#teacher-list .row').append(html);
        }
      });
    });
  }

  var ajaxCoursePage = function() {
    $(".course-list").on('click', '.show-more a', function(){

      var url = $(this).attr('data-url');

      $.ajax({
        url: url,
        dataType: 'html',
        success: function(html) {
          var html = $('.course-list .course-item,.course-list .show-more', $(html)).fadeIn('slow');

          $(".show-more").remove();
          $('.course-list').append(html);
        }
      });

    });
  }
  
  var tooltip = function() {
    $(".tooltop").hover(function(){
      $(this).tooltip('show');
    })
  }

  var tabActive = function() {
    $(".class-tab .options a, #course-nav li").click(function(){
      $(this).addClass("active").siblings().removeClass("active");
    });
  }

  var carousel = function() {
    var $this = $(".poster .carousel-inner .item");

    for (var i = 0; i < $this.length; i++) {
      if (i == 0) {
        var html = '<li data-target="#carousel" data-slide-to="0" class="active"></li>';
        $this.parents(".carousel-inner").siblings(".carousel-indicators").append(html);
      }else {
        var html = '<li data-target="#carousel" data-slide-to="'+i+'"></li>';
        $this.parents(".carousel-inner").siblings(".carousel-indicators").append(html);
      }
    }
  }

  var outline = function() {

    var chapter = $(".chapter");
    console.log($(".chapter"));
    var toggleBtn = chapter.find(" > .icon");


    if(toggleBtn.hasClass('icon-minus')) {
      chapter.data('toggle', true);
      
    }else {
      chapter.data('toggle', false);
    }

    $("#main").on('click', ".chapter", function() {
      $(this).nextUntil(".chapter").animate({
        height:'toggle'
      });

      var btn = $(this);

      if(btn.data('toggle') && btn.nextUntil(".chapter").height()) {
        btn.find(" > .icon").addClass('icon-plus').removeClass('icon-minus');
        btn.data('toggle', false);

      } else {
        btn.find(" > .icon").addClass('icon-minus').removeClass('icon-plus');
        btn.data('toggle', true);
      }
    });

  }


});