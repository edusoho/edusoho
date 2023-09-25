export default {
  methods: {
		isShowFooterShardow() {
      // 模式不为练习 并且不是最后一题,并且为答题模式
      const lastQuestion = this.showShadow !== this.itemdata.id
      if (this.mode === '' && lastQuestion && this.canDo) {
        return true;
      } else if (this.mode === '' && lastQuestion && !this.canDo && this.parentType !== 'material' ) {
        // 模式不为练习，不是最后一题，是解析模式，并且题型不为材料题
        return true;
      }
      
      // 只有练习才有 isExercise --- 是不是练习解析页
      if (this.isExercise) {
        // 不是最后一题，练习模式为测验。并且不是材料题
        if (this.mode === 'exercise' && lastQuestion && this.parentType !== 'material') {
          return true;
        } else if (this.mode === 'exercise' && lastQuestion && this.parentType === 'material') {
          // 是练习解析页，不是最后一题，是材料题返回false
          return false;
        }
      } 

      // 是练习模式 并且为答题模式
      if (this.mode === 'exercise' && this.canDo) {
        // 为一题一答模式，不是最后一题，一题一答做题（true为可以选择，false为不可选，表示已提交）有没有提交
        if (this.exerciseMode === '1' && lastQuestion && this.disabledData) {
          return true
        } 
        // 一题一答，不是材料题，不是最后一题
        if (this.exerciseMode === '1' && lastQuestion && this.parentType !== 'material') {
          return true
        }

        if ( this.exerciseMode === '0' && lastQuestion && this.canDo ) {
          return true
        }
      }
    },
  },
};
