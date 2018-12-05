<template>
  <div class="e-coupon">
    <div class="e-coupon__header">
      <span class="e-coupon__price">{{ item.rate }}<span class="fsm">元</span></span>
      <a href="" class="coupon-button">领券</a>
    </div>
    <div class="e-coupon__middle"></div>
    <div class="e-coupon__bottom text-overflow">
      可用范围：{{ scopeFilter(item.targetType) }}
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      item: {
        type: Object,
        default: {}
      },
    },
    data() {
      return {
        scope: '指定班级，如前端基础入...'
      }
    },
    methods: {
      scopeFilter(type) {
        if (type === 'class') {
          return '全部班级';
        } else if (type === 'course') {
          return '全部课程';
        }
        return '全站';
      }
    }
  }
</script>
