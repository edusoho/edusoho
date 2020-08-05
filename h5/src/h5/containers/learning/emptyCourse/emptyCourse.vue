<template>
  <div class="empty-course">
    <img class="empty-course__img" src="static/images/courseEmpty.png" alt="">
    <p class="empty-course__text">{{ emptyText }}</p>
    <div v-if="hasButton" class="empty-course__btn" @click="jumpBack">+ 更多{{ moreText }}等您加入</div>
  </div>
</template>

<script>
import store from '@/store'

export default {
  props: {
    hasButton: {
      type: Boolean,
      default: true
    },
    type: {
      type: String,
      default: 'course'
    },
    text:{
      type: String,
      default: ""
    }
  },
  // data() {
  //   return {
  //     moreText: this.type === 'course_list' ? '好课' : (this.type === 'classroom_list' ? '班级' : '题库')
  //   }
  // },
  computed:{
    emptyText(){
      if(!this.text){
        return `暂无${this.type === 'course_list' ? '课程' : '班级'}`
      }
      return '暂无' + this.text;
    },
    moreText(){
      const type=this.type;
      if(type === 'course_list'){
        return '好课'
      }else if(type === 'classroom_list'){
        return '班级'
      }else {
        return '题库'
      }
    }
  },
  methods: {
    jumpBack() {
      this.$router.push({
        name: 'find',
        query: {
          redirect: 'find'
        }
      })
    },
    getEmptyText(){
      return `暂无${this.type === 'course_list' ? '课程' : '班级'}`
    }
  }
}

</script>
