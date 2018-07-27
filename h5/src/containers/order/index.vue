<template>
  <div class="order">
    <e-loading v-if="isLoading"></e-loading>
    <e-course type="confirmOrder" :order="course" v-if="Object.keys(course).length >0"></e-course>
    <div class="order-submit-bar">
      <span class='red'> 合计: {{ course.totalPrice }} 元 </span>
      <van-button class="primary-btn submit-btn" 
        @click="handleSubmit"
        size="small">提交订单</van-button>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import eCourse from '@/containers/components/e-course/e-course.vue';
import Api from '@/api'

export default {
  components: {
    eCourse
  },
  data () {
    return {
      course: {}
    }
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    }),
  },
  created () {
    Api.confirmOrder({
      data: {
        targetType: 'course',
        targetId: this.$route.params.id
      }
    }).then(res => {
      this.course = Object.assign({}, res)
    })
  },
  methods: {
    handleSubmit () {
      this.$router.push({
        name: 'pay',
        query: {
          id: this.$route.params.id
        }
      })
    }
  }
}
</script>
