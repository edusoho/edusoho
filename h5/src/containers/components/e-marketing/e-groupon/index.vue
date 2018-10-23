<template>
  <div class="e-groupon">
    <div class="e-groupon__tag" v-if="tag.length">{{ tag }}</div>
    <div class="e-groupon__image-container" :class="{ 'e-groupon__image-empty': !activity.cover }">
      <img v-if="activity.cover" class="e-groupon__image" :src="activity.cover" alt="">
    </div>
    <div class="e-groupon__context">
      <div class="context-title text-overflow">{{ activity.name || '拼团活动' }}</div>
      <div class="context-sale">
        <span class="context-sale__sale-price">{{ activity.memberPrice || '00.00' }} 元</span>
        <span v-if="activity.originPrice" class="context-sale__origin-price">原价{{ activity.originPrice }} 元</span>
        <a class="context-sale__shopping" href="javascript:;">去拼团</a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'e-groupon',
  props: {
    activity: {
      type: Object,
      default: {}
    },
    tag: {
      type: String,
      default: '',
    }
  },
  data () {
    return {

    }
  }
}
</script>
