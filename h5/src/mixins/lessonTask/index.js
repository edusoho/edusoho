/* eslint-disable no-loop-func */
export default {
  methods: {
    // 处理六大题型数据
    sixType(type, item, lastAnswer) {
      if (type !== 'essay' && type !== 'fill') {
        // 刷新页面或意外中断回来数据会丢失，因此要判断本地是否有缓存数据，如果有要把数据塞回
        const answer = lastAnswer ? lastAnswer[item.id] : [];
        return { item, answer };
      }
      if (type === 'essay') {
        const answer = lastAnswer ? lastAnswer[item.id] : [''];
        return { item, answer };
      }

      if (type === 'fill') {
        const { stem, index } = this.fillReplce(item.stem, 0);

        item.stem = stem;
        item.fillnum = index;

        const answer = lastAnswer ? lastAnswer[item.id] : new Array(index).fill('');

        return { item, answer };
      }
      return '';
    },
    // 处理六大题型数据
    analysisSixType(type, item) {
      let answer = [];
      if (type !== 'fill' && type !== 'essay') {
        //   由于后台返回的是string格式，前端需要用number格式才能回显。周一让后台统一改为number
        item.answer.forEach((num, index) => {
          item.answer[index] = Number(num);
        });
        answer = item.answer;
        if (item.testResult) {
          item.testResult.answer.forEach((num, index) => {
            item.testResult.answer[index] = Number(num);
          });
          // 为了回显，这里传给子组件的answer要是正确答案和学员选择答案的合集，因为都要选中。
          answer = Array.from(
            new Set([...item.answer, ...item.testResult.answer])
          );
        }
      }

      if (type === 'essay') {
        answer = item.testResult ? item.testResult.answer : [];
      }

      if (type === 'fill') {
        const { stem, index } = this.fillReplce(item.stem, 0);
        item.stem = stem;
        item.fillnum = index;

        answer = item.testResult ? item.testResult.answer : new Array(index).fill('');
      }

      return { item, answer };
    },
    // 处理富文本，并统计填空题的空格个数
    fillReplce(stem, index = 0) {
      const reg = /\[\[.+?\]\]/;
      while (reg.exec(stem) !== null) {
        stem = stem.replace(reg, () => {
          index += 1;
          return `<span class="fill-bank">(${index}）</span>`;
        });
      }
      return { stem, index };
    }
  }
};
