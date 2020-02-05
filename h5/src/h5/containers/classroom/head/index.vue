<template>
  <div class="course-detail__head pos-rl">
    <div class="course-detail__head--img">
      <img :src="cover" alt="">
    </div>
    <countDown
      v-if="seckillActivities && counting && !isEmpty && seckillActivities.status === 'ongoing'"
      :activity="seckillActivities"
      @timesUp="expire"
      @sellOut="sellOut"/>
    <tagLink :tag-data="tagData"/>
  </div>
</template>
<script>
import countDown from '&/components/e-marketing/e-count-down/index'
import tagLink from '&/components/e-tag-link/e-tag-link'
import Api from '@/api'
import qs from 'qs'
import { mapState } from 'vuex'
export default {
  components: {
    countDown,
    tagLink
  },
  props: {
    cover: {
      type: String,
      default: ''
    },
    price: {
      type: String,
      default: ''
    },
    classroomId: {
      type: String,
      default: 0
    },
    seckillActivities: {
      type: Object,
      default: null
    }
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
        minDirectRewardRatio: 0
      },
      bindAgencyRelation: {} // 分销代理商绑定信息
    }
  },
  computed: {
    ...mapState(['DrpSettings']),
  },
  created() {
    this.initTagData()
  },
  methods: {
    expire() {
      this.counting = false
    },
    sellOut() {
      this.isEmpty = true
      this.$emit('goodsEmpty')
    },
    showTagLink() {
      Api.hasDrpPluginInstalled().then(res => {
        if (!res.Drp) {
          this.tagData.isShow = false
          return
        }

        Api.getAgencyBindRelation().then(data => {
          if (!data.agencyId) {
            this.tagData.isShow = false
            return
          }
          this.bindAgencyRelation = data
          this.tagData.isShow = true
        })
      })
    },
    initTagData() {
      if(Object.keys(this.DrpSettings).length){
        this.tagData.minDirectRewardRatio = this.DrpSettings.minDirectRewardRatio

        const params = {
          type: 'classroom',
          id: this.classroomId,
          merchant_id: this.DrpSettings.merchantId
        }

        this.tagData.link = this.DrpSettings.distributor_template_url + '?' + qs.stringify(params)
        const earnings = (this.DrpSettings.minDirectRewardRatio / 100) * this.price
        this.tagData.earnings = (Math.floor(earnings * 100) / 100).toFixed(2)

        this.showTagLink()
      }
    }
  }
}
</script>
