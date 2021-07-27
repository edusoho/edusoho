import i18n from '@/lang';

const getDisplayStyle = (data, listObj) => {
  let showStudentStr = '';
  const status = listObj.showNumberData;
  if (status === 'join') {
    showStudentStr = `<span class="switch-box__state">
                        <p class="iconfont icon-people">${data.studentNum}</p>
                      </span>`;
  } else if (status === 'visitor') {
    showStudentStr = `<span class="switch-box__state">
                        <p class="iconfont icon-visibility">${data.hitNum}</p>
                      </span>`;
  } else {
    showStudentStr = '';
  }
  // const price =
  //   data.price === '0.00'
  //     ? '<p style="color: #408FFB">免费</p>'
  //     : `<p style="color: #ff5353">¥ ${data.price}</p>`;

  const price = getPriceDisplay(data, 'h5');

  if (listObj.typeList === 'classroom_list') {
    return {
      id: data.id,
      hasCertificate: data.hasCertificate,
      targetId: data.targetId,
      goodsId: data.goodsId,
      specsId: data.specsId,
      imgSrc: {
        url: data.cover.middle || '',
        className: 'e-course__img',
      },
      header: data.title,
      middle: {
        value: data.courseNum,
        html: `<div class="e-course__count">${i18n.t('filters.totalOfTwoCourses', { number: data.courseNum })}</div>`,
        vipHtml: `<span class="e-course__count">${i18n.t('filters.totalOfTwoCourses', { number: data.courseNum })}</span><span class="e-course__count" style="color: #e8Ab2b;">${i18n.t('filters.vipJoinForfree')}</span>`,
      },
      bottom: {
        value: data.price || data.studentNum,
        html: `<span class="switch-box__price">${price}</span>${showStudentStr}`,
      },
    };
  }
  return {
    id: data.id,
    goodsId: data.courseSet.goodsId,
    specsId: data.specsId,
    hasCertificate: data.hasCertificate,
    imgSrc: {
      url: data.courseSet.cover.middle || '',
      className: 'e-course__img',
    },
    header: data.courseSetTitle,
    middle: {
      value: data.title,
      html: `<div class="e-course__project text-overflow">
                  <span>${data.title}</span>
                </div>`,
      vipHtml: `<span class="e-course__count">${data.title}</span><span class="e-course__count" style="color: #e8Ab2b;">${i18n.t('filters.vipJoinForfree')}</span>`,
    },
    bottom: {
      value: data.price || data.studentNum,
      html: `<span class="switch-box__price">${price}</span>${showStudentStr}`,
    },
  };
};

const getNewDisplayStyle = (data, listObj, platform) => {
  const price = getPriceDisplay(data, platform);
  const type = listObj.typeList;

  if (type === 'classroom_list') {
    return getClassRoomDisplay(data, listObj, price);
  }
  if (type === 'item_bank_exercise') {
    return getItemBankDisplay(data, listObj, price);
  }
  return getCourseDisplay(data, listObj, price);
};

const getPriceDisplay = (data, platform) => {
  const { amount, currency, coinAmount, coinName } = data.price2;
  const dataPrice = Number(amount);
  const primaryColor = {
    app: '#20B573',
    h5: '#408FFB',
  };
  let price;
  if (dataPrice > 0 && currency === 'coin') {
    price = `<span style="color: #ff5353">${coinAmount} ${coinName}</span>`;
  } else if (dataPrice > 0 && currency === 'RMB') {
    price = `<span style="color: #ff5353">¥ ${amount}</span>`;
  } else {
    price = `<span style="color:${primaryColor[platform]}">${i18n.t('filters.free')}</span>`;
  }
  return price;
};

const getClassRoomDisplay = (data, listObj, price) => {
  return {
    id: data.id,
    hasCertificate: data.hasCertificate,
    targetId: data.targetId,
    goodsId: data.goodsId,
    specsId: data.specsId,
    studentNum: listObj.classRoomShowStudent ? data.studentNum : null,
    imgSrc: {
      url: data.cover.middle || '',
      className: '',
    },
    header: data.title,
    middle: {
      value: data.courseNum,
      html: `<span>${i18n.t('filters.totalOfTwoCourses', { number: data.courseNum })}</span>`,
    },
    bottom: {
      value: data.price,
      html: `<span>${price}</span>`,
    },
  };
};

const getCourseDisplay = (data, listObj, price) => {
  return {
    id: data.id,
    goodsId: data.courseSet.goodsId,
    specsId: data.specsId,
    hasCertificate: data.hasCertificate,
    studentNum: listObj.showStudent ? data.studentNum : null,
    imgSrc: {
      url: data.courseSet.cover.middle || '',
      className: '',
    },
    header: data.courseSetTitle,
    middle: {
      value: data.title,
      html: ` <span>${data.title}</span>`,
    },
    bottom: {
      value: data.price,
      html: `<span>${price}</span>`,
    },
  };
};

const getItemBankDisplay = (data, listObj, price) => {
  return {
    id: data.id,
    hasCertificate: data.hasCertificate,
    studentNum: listObj.showStudent ? data.studentNum : null,
    imgSrc: {
      url: data.cover.middle || '',
      className: '',
    },
    header: data.title,
    middle: {
      value: '',
      html: ` <span></span>`,
    },
    bottom: {
      value: data.price,
      html: `<span>${price}</span>`,
    },
  };
};

const getstudyItemBankDisplay = data => {
  return {
    id: data.itemBankExercise.id,
    studentNum: null,
    imgSrc: {
      url: data.itemBankExercise.cover.middle || '',
      className: '',
    },
    header: data.itemBankExercise.title,
    middle: {
      value: data.completionRate,
      html: ` <class class="completionRate">答题率${data.completionRate}％</class>`,
    },
    bottom: {
      value: data.masteryRate,
      html: `<class class="masteryRate">掌握率${data.masteryRate}％</class>`,
    },
  };
};

const courseListData = (data, listObj, uiStyle = 'old', platform = 'h5') => {
  // h5和app用了新版ui,小程序还是用旧版ui
  switch (listObj.type) {
    case 'price':
      if (uiStyle !== 'old') {
        return getNewDisplayStyle(data, listObj, platform);
      }
      return getDisplayStyle(data, listObj);

    case 'confirmOrder':
      return {
        imgSrc: {
          url: data.cover.middle || '',
          className: 'e-course__img',
        },
        header: data.title,
        middle: '',
        bottom: {
          value: data.coinPayAmount,
          html: `<span class="switch-box__price">
                  <p style="color: #ff5353">¥ ${data.coinPayAmount}</p>
                </span>`,
        },
      };
    case 'rank':
      if (listObj.typeList === 'classroom_list') {
        return {
          id: data.id,
          goodsId: data.goodsId,
          specsId: data.specsId,
          hasCertificate: data.hasCertificate,
          targetId: data.targetId,
          imgSrc: {
            url: data.cover.middle || '',
            className: 'e-course__img',
          },
          header: data.title,
          middle: '',
          bottom: {
            value: data.courseNum,
            html: `<div class="e-course__count">${i18n.t('filters.totalOfTwoCourses', { number: data.courseNum })}</div>`,
          },
        };
      }
      if (listObj.typeList === 'item_bank_exercise') {
        return getstudyItemBankDisplay(data);
      }
      return {
        id: data.id,
        goodsId: data.courseSet.goodsId,
        specsId: data.specsId,
        hasCertificate: data.hasCertificate,
        imgSrc: {
          url: data.courseSet.cover.middle || '',
          className: 'e-course__img',
        },
        header: data.courseSetTitle,
        middle: {
          value: data.title,
          html: `<div class="e-course__project text-overflow">
                  <span>${data.title}</span>
                </div>`,
        },
        bottom: {
          value: data.progress.percent,
          html: `<div class="rank-box">
                  <div class="progress round-conner">
                    <div class="curRate round-conner"
                      style="width:${parseInt(data.progress.percent)}%">
                    </div>
                  </div>
                  <span>${parseInt(data.progress.percent)}%</span>
                </div>`,
        },
      };
    default:
      return 'empty data';
  }
};

export default courseListData;
