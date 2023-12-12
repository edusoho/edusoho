<template>
   <div class="flex flex-col items-center">
    <img class="hide-exercise" src="static/images/exercise/hide-exerice.png" />
    <div class="text-text-7 font-medium">{{typeContent}}</div>
    <div class="text-12 text-text-6" style="margin-top: 6px;">{{ $t('closed.cannotLearn') }}</div>
    <a @click="goLearn" class="goLearn mt-36">{{ $t('closed.goLearnPage') }}</a>
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
          return $t('closed.course');
        case 'classroom':
          return $t('closed.classroom');
        case 'exercise':
          return $t('closed.exercise')
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