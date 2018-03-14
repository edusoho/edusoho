class CourseAd {
  constructor({ element, courseUrl }) {
    this.$element = $(element);
    this.courseUrl = courseUrl;
    this.init();
  }

  init() {
    let html = '';

    $.get(this.courseUrl).then((data) => {
      console.log(data);
      data.map((item) => {
        html =  html + this.template(item.id, item.cover, item.title);
      });

      this.$element.find('.modal-body').html(html);
    });
  }

  isWxAndroidBrowser() {
    let ua = navigator.userAgent.toLowerCase();
    return /android/.test(ua) && /micromessenger/i.test(ua);
  }

  isWxPreviewType () {
    return this.$element.parent('.js-open-course-wechat-preview').length > 0;
  }

  template(id, cover, title) {
    return `<div class="modal-img">
        <a href="/course_set/${id}">
          <img class="img-responsive" src="${cover.middle}" alt="">
        </a>
        <div class="title"><a class="link-dark" href="/course_set/${id}">${title}</a></div>
      </div>`;
  }

  show() {
    if (this.isWxPreviewType()) {
      return;
    }

    if (this.isWxAndroidBrowser()) {
      document.getElementById('viewerIframe').contentWindow.document.getElementById('lesson-player').style.display = 'none';

      this.$element.on('hide.bs.modal', () => {
        document.getElementById('viewerIframe').contentWindow.document.getElementById('lesson-player').style.display = 'block';
      });
    }

    this.$element.modal({
      backdrop: false
    });
  }

  hide() {
    this.$element.modal('hide');
  }
}

export default CourseAd;