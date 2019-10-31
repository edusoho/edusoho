<template>
  <div class="course-detail__head pos-rl">
    <div class="course-detail__head--img">
      <img :src="cover" alt="">
    </div>
    <countDown
      v-if="seckillActivities && counting && !isEmpty && seckillActivities.status === 'ongoing'"
      :activity="seckillActivities"
      @timesUp="expire"
      @sellOut="sellOut">
    </countDown>
    <tagLink :tagData="tagData"></tagLink>
  </div>
</template>
<script>
import countDown from '@/containers/components/e-marketing/e-count-down/index';
import tagLink from '@/containers/components/e-tag-link/e-tag-link';
import Api from '@/api';
import qs from 'qs';

export default {
  components: {
    countDown,
    tagLink,
  },
  data() {
    return {
      counting: true,
      isEmpty: false,
      tagData: { // 分销标签数据
        earnings: 0,
        isShow: false,
        link: '',
        className: 'course-tag',
        minDirectRewardRatio: 0,
      },
      bindAgencyRelation: {}, // 分销代理商绑定信息
    };
  },
  props: {
    cover: {
      type: String,
      default: '',
    },
    price: {
      type: String,
      default: '',
    },
    classroomId: {
      type: Number,
      default: 0,
    },
    seckillActivities: {
      type: Object,
      default: null,
    }
  },
  methods: {
    expire() {
      this.counting = false;
    },
    sellOut() {
      this.isEmpty = true
      this.$emit('goodsEmpty')
    },
    showTagLink() {
      Api.hasDrpPluginInstalled().then(res => {
        if (!res.Drp) {
          this.tagData.isShow = false;
          return;
        }

        Api.getAgencyBindRelation().then(data => {
          if (!data.agencyId) {
            this.tagData.isShow = false;
            return;
          }
          this.bindAgencyRelation = data;
          this.tagData.isShow = true;
        })
      })
    },
    initTagData() {
      Api.getDrpSetting().then(data => {
        this.drpSetting = data;
        this.tagData.minDirectRewardRatio = data.minDirectRewardRatio;

        let params = {
          type: 'classroom',
          id: this.classroomId,
          merchant_id: this.drpSetting.merchantId,
        };

        this.tagData.link = this.drpSetting.distributor_template_url + '?' + qs.stringify(params);
        const earnings = (this.drpSetting.minDirectRewardRatio / 100) * this.details.price;
        this.tagData.earnings = (Math.floor(earnings * 100) / 100).toFixed(2);
      });
    },
  },
  created() {
    this.showTagLink();
    this.initTagData();
  }
}
</script>
