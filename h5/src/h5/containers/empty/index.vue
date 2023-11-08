<template>
   <div class="flex flex-col items-center">
    <img class="hide-exercise" src="static/images/exercise/hide-exerice.png" />
    <div class="text-text-7 font-medium">当前{{typeContent}}练习已关闭</div>
    <div class="text-12 text-text-6 mt-6">无法继续学习</div>
    <a @click="goLearn" class="goLearn mt-36">回到学习页</a>
  </div>
</template>
<script>
export default {
  data(){
    return {
      kind: ''
    }
  },
  computed:{
    typeContent() {
      switch (this.kind) {
        case 'course':
          return '课程';
        case 'classroom':
          return '班级';
        case 'exercise':
          return '题库'
        default:
          return ''
      }
    }
  },
  mounted() {
    if(this.$route.query.type) {
      this.kind = this.$route.query.type
    }
  },
  methods: {
    goLearn() {
      this.$router.push('/my/courses/learning')
    }
  }
}
</script>
<style scoped>
.hide-exercise {
  display: flex;
  width: 236px;
  height: 236px;
  justify-content: center;
  align-items: center;
  margin-top: 160px;
}

.goLearn {
  padding: 5px 15px;
  text-align: center;
  border-radius: 18px;
  background: #5DB85D;
  color: #fff;
}
</style>