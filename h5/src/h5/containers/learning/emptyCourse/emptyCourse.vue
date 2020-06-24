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
  data() {
    return {
      moreText: this.type === 'course_list' ? '好课' : '班级'
    }
  },
  computed:{
    emptyText(){
      if(!this.text){
        return `暂无${this.type === 'course_list' ? '课程' : '班级'}`
      }
      return  this.text
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
