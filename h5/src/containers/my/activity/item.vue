<template>
  <div class="marketing-groupon activity-cell" @click="viewDetail">
    <div class="activity-cell__head">
      <span class="head-left" :class="activity.type">{{ type2symbol[activity.type] }}</span>
      <span class="head-right" :class="activity.result">{{ type2label[activity.type] + status2label[activity.result] }}</span>
    </div>
    <div class="activity-cell__body">
      <div class="marketing-groupon__image-container" :class="{ 'marketing-groupon__image-empty': !activity.cover }">
        <img v-if="activity.cover" class="marketing-groupon__image" :src="activity.cover">
      </div>
      <div class="marketing-groupon__context e-groupon__context">
        <div class="context-title text-overflow">{{ activity.name }}</div>
        <div class="context-sale">
          <span v-if="payAmount" class="context-sale__sale-price">{{ payAmount }}元</span>
          <span v-if="originPrice" class="context-sale__origin-price">{{ originPrice }}元</span>
          <span class="context-sale__shopping">{{ type2label[activity.type] }}详情</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const type2label = {
  cut: '砍价',
  groupon: '拼团',
  seckill: '秒杀',
};

const type2symbol = {
  cut: '砍',
  groupon: '团',
  seckill: '秒',
};

const status2label = {
  success: '成功',
  ongoing: '进行中',
  fail: '失败',
};

export default {
  name: 'activity-item',
  props: {
    activity: {
      type: Object,
      default: () => {
        return {};
      },
    },
  },
  data () {
    return {
      type2symbol,
      type2label,
      status2label,
    };
  },
  computed: {
    payAmount() {
      return this.activity.rule && this.activity.rule.payAmount;
    },
    originPrice() {
      return this.activity.rule && this.activity.rule.originPrice;
    },
    type() {
      return this.activity.type;
    },
  },
  methods: {
    viewDetail(e) {
      // const { activityId, courseId } = activity;
    },
  }
}
</script>

<style lang="scss" scoped>
</style>
