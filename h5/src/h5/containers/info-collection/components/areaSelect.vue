<template>
  <div>
    <!-- 地区 -->
    <van-field
      readonly
      v-model="info.area"
      :value="value"
      label="省市"
      placeholder="请选择省市区县"
      right-icon="
    iconfangxiang my_setting-more-special"
      icon-prefix="iconfont"
      @click="areaCancel"
    />
    <van-action-sheet v-model="show.area">
      <van-area
        title="选择地区"
        :area-list="areaList"
        value
        @confirm="areaConfirm"
        @cancel="areaCancel"
      />
    </van-action-sheet>
  </div>
</template>

<script>
import { arealist } from '@/utils/arealist';
export default {
  components: {},
  data() {
    return {
      info: {
        area: '',
        province: '',
        city: '',
        local: '',
      },
      show: {
        area: false,
      },
      value: '',
      areaList: arealist,
    };
  },
  methods: {
    areaConfirm(val) {
      this.info.province = val[0].name;
      this.info.city = val[1].name;
      this.info.local = val[2].name;
      if (this.info.province === this.info.city) {
        this.info.area = this.info.city + ' ' + this.info.local;
      } else
        this.info.area =
          this.info.province + ' ' + this.info.city + ' ' + this.info.local;
      this.show.area = false;
    },
    areaCancel() {
      this.show.area = !this.show.area;
    },
  },
};
</script>

<style></style>
