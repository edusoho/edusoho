<template>
  <div>
    <e-panel :title="ItemBankExercise.title">
      <div class="course-detail__plan-price">
        <span :class="{ isFree: isFree }">{{ filterPrice() }} </span>
        <span class="plan-price__student-num"
          >{{ ItemBankExercise.studentNum }}人在学</span
        >
      </div>
    </e-panel>

    <div class="course-detail__validity">
      <div>
        <span class="mr20">学习有效期</span>
        <span class="dark">{{ learnExpiryHtml() }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { createNamespacedHelpers } from 'vuex';
import { learnExpiry } from '@/utils/itemBank-status';
const { mapState } = createNamespacedHelpers('ItemBank');
export default {
  components: {},
  data() {
    return {
      isFree: false,
    };
  },
  computed: {
    ...mapState({
      ItemBankExercise: state => state.ItemBankExercise,
    }),
  },
  watch: {},
  created() {},
  methods: {
    filterPrice() {
      const ItemBankExercise = this.ItemBankExercise;
      if (
        Number(ItemBankExercise.isFree) ||
        ItemBankExercise.price === '0.00'
      ) {
        this.isFree = true;
        return '免费';
      }
      this.isFree = false;
      return `¥${ItemBankExercise.price}`;
    },
    learnExpiryHtml() {
      const obj = {
        expiryMode: this.ItemBankExercise.expiryMode,
        expiryDays: this.ItemBankExercise.expiryDays,
        expiryStartDate: this.ItemBankExercise.expiryStartDate,
        expiryEndDate: this.ItemBankExercise.expiryEndDate,
      };
      return learnExpiry(obj);
    },
  },
};
</script>
