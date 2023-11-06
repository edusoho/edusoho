import { Toast } from 'vant';

export const isOpen = (kind) => {
  switch (kind) {
    case 'test':
      Toast.fail('题库已关闭');
      break;
    case 'classroom':
      Toast.fail('班级已关闭');
      break;
    case 'lesson':
      Toast.fail('课程已关闭');
      break;
  }
}