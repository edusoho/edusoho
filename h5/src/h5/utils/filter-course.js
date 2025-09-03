import i18n from '@/lang';

const getDisplayStyle = (data, listObj) => {
  let showStudentStr = '';
  const status = listObj.showNumberData;

  if (status === 'join') {
    showStudentStr = `<span class="text-12 text-text-3">
                        ${data.studentNum}人在学
                      </span>`;
  } else if (status === 'visitor') {
    showStudentStr = `<span class="text-12 text-text-3">
                        ${data.hitNum}人浏览
                      </span>`;
  } else {
    showStudentStr = '';
  }

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
      },
      bottom: {
        value: data.price || data.studentNum,
        html: `<span class="text-12">${price}</span>${showStudentStr}`,
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
    },
    bottom: {
      value: data.price || data.studentNum,
      html: `<span class="text-12">${price}</span>${showStudentStr}`,
    },
    videoMaxLevel: data.videoMaxLevel,
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

  if (data.hidePrice !== '1') {
    if (dataPrice > 0 && currency === 'coin') {
      price = `<span class="font-bold" style="color: #FF7A34">${coinAmount} ${coinName}</span>`;
    } else if (dataPrice > 0 && currency === 'RMB') {
      price = `<span class="text-14 font-bold" style="color: #FF7A34">¥ ${amount}</span>`;
    } else {
      price = `<span class="font-bold text-14" style="color: #FF7A34">${i18n.t('filters.free')}</span>`;
    }
  } else {
    price = ``
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
  if (data.hidePrice !== '1' && (data.originPrice !== data.price)) {
    price = `
      <div class="text-14" style="color: #FF7A34;">¥ ${data.price}</div>
      <s style="font-size: 12px;margin: 3px 0 0 -2px;color: #86909C;transform: scale(0.83);">¥ ${data.originPrice}</s>
    `
  }

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
      html: `<div style="display: flex">${price}</div>`,
    },
    videoMaxLevel: data.videoMaxLevel,
    hidePrice: data.hidePrice,
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
    bindTitle: data.bindTitle,
    id: data.itemBankExercise.id,
    studentNum: null,
    imgSrc: {
      url: data.itemBankExercise.cover.middle || '',
      className: '',
    },
    header: data.itemBankExercise.title,
    middle: {
      value: data.completionRate,
      html: ` <class class="completionRate">${i18n.t('filters.answerRate')}${data.completionRate}％</class>`,
    },
    bottom: {
      value: data.masteryRate,
      data,
      html: `<class class="masteryRate">${i18n.t('filters.accuracy')}${data.masteryRate}％</class>`,
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
          html: `<span class="text-12">
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
            data,
            html: `<div class="e-course__count">
              ${i18n.t('filters.totalOfTwoCourses', { number: data.courseNum })}
              <span style="color: #E5E6EB;">|</span>
              <span>已学${data.learningProgressPercent}%</span>
            </div>`,
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
          data,
          html: `<div class="text-text-3 text-12">
                  <span>共${data.compulsoryTaskNum}课时</span>
                  <span style="color: #E5E6EB;">|</span>
                  <span>已学${data.learnedCompulsoryTaskNum}课时</span>
                </div>`,
        },
      };
    default:
      return 'empty data';
  }
};

export default courseListData;
