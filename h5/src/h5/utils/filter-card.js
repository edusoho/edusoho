import { formatTimeByNumber } from '@/utils/date-toolkit';
const canShowSubtitle = target => {
  return target.task.seq !== target.seq;
};

const canTimeShow = task => {
  const type = task.type;
  const showList = ['audio', 'video', 'live'];
  return showList.includes(type);
};

const getTime = target => {
  return `时长: ${formatTimeByNumber(target.task.length)}`;
};

const getTitleHtml = (hasSubTitle, target) => {
  if (hasSubTitle) {
    return `<span>课时${target.number}</span>${target.task.title}`;
  }
  return `${target.task.title}`;
};

const getItemBankHtml = data => {
  if (data.targetType === 'item_bank_chapter_exercise') {
    return `${data.target.assessment.description}`;
  }
  return `${data.target.module.title}-${data.target.assessment.name}`;
};

const getTask = data => {
  const isSupport = data.target.task.type !== 'flash';
  const hasSubTitle = canShowSubtitle(data.target);
  return {
    link: {
      courseId: data.target.course.id,
      type: data.target.task.type,
      taskId: data.target.task.id,
      classroomId: data.target.classroom?.id,
    },
    type: data.target.task.type,
    top: {
      isShow: data.target.course.displayedTitle,
      html: `<span>${data.target.course.displayedTitle}</span>
      <i class="iconfont icon-arrow-right"></i>`,
    },
    content: {
      left: {
        subTitle: {
          isShow: hasSubTitle,
          subhtml: `课时${data.target.number}: ${data.target.title}`,
          html: getTitleHtml(hasSubTitle, data.target),
        },
        dec: {
          isShow: canTimeShow(data.target.task),
          html: ` <span class="live-content__time">${getTime(
            data.target,
          )}</span>`,
        },
      },
      right: {
        isShow: `${isSupport}`,
      },
    },
    bottom: {
      isShow: data.target.classroom,
      html: `<span>${data.target.classroom?.title || ''}</span>
               <i class="iconfont icon-arrow-right"></i>`,
    },
  };
};

const getItemBank = data => {
  return {
    link: {
      assessmentId: data.target.assessment?.id,
      moduleId: data.target.module?.id,
      answerRecordId: data.target.answerRecord?.id,
      exerciseId: data.target.exercise?.id,
    },
    type: data.targetType,
    top: {
      isShow: data.target.exercise.title,
      html: `<span>${data.target.exercise.title}</span>
      <i class="iconfont icon-arrow-right"></i>`,
    },
    content: {
      left: {
        subTitle: {
          isShow: false,
          subhtml: ``,
          html: getItemBankHtml(data),
        },
        dec: {
          isShow: false,
          html: ` `,
        },
      },
      right: {
        isShow: true,
      },
    },
    bottom: {
      isShow: false,
      html: ``,
    },
  };
};
const cardDataList = data => {
  const itemBank = [
    'item_bank_chapter_exercise',
    'item_bank_assessment_exercise',
  ];
  if (itemBank.includes(data.targetType)) {
    return getItemBank(data);
  }
  return getTask(data);
};

export default cardDataList;
