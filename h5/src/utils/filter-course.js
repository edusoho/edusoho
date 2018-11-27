const courseListData = (data, listObj) => {
  switch (listObj.type) {
    case 'price':
      const showStudentStr = listObj.showStudent ?
        `<span class="switch-box__state"><p style="color: #B0BDC9">
          ${data.studentNum}人在学</p></span>` : '';

      if (listObj.typeList === 'class_list') {
        return {
          id: data.id,
          targetId: data.targetId,
          imgSrc: {
            url: data.cover.middle || '',
            className: 'e-class__img'
          },
          header: data.title,
          middle: {
            value: data.courseNum,
            html: `<div class="e-course__count">共 ${data.courseNum} 门课程</div>`
          },
          bottom: {
            value: data.price || data.studentNum,
            html: `<span class="switch-box__price">
                    <p style="color: #ff5353">¥ ${data.price}</p>
                  </span>${showStudentStr}`
          }
        };
      }
      return {
        id: data.id,
        imgSrc: {
          url: data.courseSet.cover.middle || '',
          className: 'e-course__img'
        },
        header: data.courseSetTitle,
        middle: {
          value: data.title,
          html: `<div class="e-course__project text-overflow">
                  <span>${data.title}</span>
                </div>`
        },
        bottom: {
          value: data.price || data.studentNum,
          html: `<span class="switch-box__price">
                    <p style="color: #ff5353">¥ ${data.price}</p>
                </span>${showStudentStr}`
        }
      };
    case 'confirmOrder':
      return {
        imgSrc: {
          url: data.cover.middle || '',
          className: 'e-course__img'
        },
        header: data.title,
        middle: '',
        bottom: {
          value: data.coinPayAmount,
          html: `<span class="switch-box__price">
                    <p style="color: #ff5353">¥ ${data.coinPayAmount}</p>
                </span>`
        }
      };
    case 'rank':
      if (listObj.typeList === 'class_list') {
        return {
          id: data.id,
          targetId: data.targetId,
          imgSrc: {
            url: data.cover.middle || '',
            className: 'e-class__img'
          },
          header: data.title,
          middle: '',
          bottom: {
            value: data.courseNum,
            html: `<div class="e-course__count">共 ${data.courseNum} 门课程</div>`
          }
        };
      }
      return {
        id: data.id,
        imgSrc: {
          url: data.courseSet.cover.middle || '',
          className: (listObj.typeList === 'course_list') ? 'e-course__img' : 'e-class__img'
        },
        header: data.courseSetTitle,
        middle: {
          value: data.title,
          html: `<div class="e-course__project text-overflow">
                  <span>${data.title}</span>
                </div>`
        },
        bottom: {
          value: data.progress.percent,
          html: `<div class="rank-box">
                  <div class="progress round-conner">
                    <div class="curRate round-conner"
                      style="width:${data.progress.percent}%">
                    </div>
                  </div>
                  <span>${data.progress.percent}%</span>
                </div>`
        }
      };
    default:
      return 'empty data';
  }
};
export default courseListData;
