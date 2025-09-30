export default {
  methods: {
    refreshChoice(res) {
      if (res) {
        this.$nextTick(() => {
          this.question[0] = res
          this.refreshKey = !this.refreshKey
        })
        return
        
      }
      const obj = this.exerciseInfo.submittedQuestions
      this.$nextTick(() => {
        this.question = obj.filter(item => item.questionId+'' === this.itemdata.id)
        this.refreshKey = !this.refreshKey
      })
    }
    
  },
};
