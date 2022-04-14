<template>
  <div class="aside-container">
    <div class="aside-header">
      <div class="aside-header__title">{{ 'scrm.title' | trans }}</div>
    </div>

    <div class="scrm-sec">
      <div class="scrm-sec__title">{{ 'scrm.edusoho_title' | trans }}</div>
      <div class="scrm-sec__img">
        <img src="/static-dist/app/img/vue/scrm-right.png" alt="">
      </div>
      <div class="scrm-sec__content">{{ 'scrm.desc' | trans }}</div>
      <div class="scrm-sec__btn">
        <a-button class="mr16" @click="handleClickGoScrm">{{ 'scrm.go_to_scrm' | trans }}</a-button>
        <a-button type="primary" @click="showModal">{{ 'scrm.open_now' | trans }}</a-button>
      </div>
    </div>

    <div class="scrm-sec">
      <div class="scrm-sec__title">{{ 'scrm.product_features' | trans }}</div>
      <div class="scrm-characteristic">
        <div class="characteristic-box">
          <div
            v-for="(item, index) in characteristic1"
            :key="index"
            class="scrm-characteristic__item"
          >
            <div class="characteristic-img">
              <img :src="item.img" :alt="item.title">
            </div>
            <div class="characteristic-title">{{ item.title }}</div>
            <div class="characteristic-desc">{{ item.desc }}</div>
          </div>
        </div>

        <div class="characteristic-box" style="margin-top: 60px;">
          <div
            v-for="(item, index) in characteristic2"
            :key="index"
            class="scrm-characteristic__item"
          >
            <div class="characteristic-img">
              <img :src="item.img" :alt="item.title">
            </div>
            <div class="characteristic-title">{{ item.title }}</div>
            <div class="characteristic-desc">{{ item.desc }}</div>
          </div>
        </div>
      </div>
    </div>

    <a-modal
      :title="'scrm.open_edusoho_scrm' | trans"
      :visible="visible"
      :footer="null"
      :width="584"
      @cancel="handleCancel"
    >
      <div class="scrm-modal">
        <div class="scrm-modal__title">{{ 'scrm.hava_a_dedicated_business_manager' | trans }}</div>
        <div class="scrm-modal__desc">{{ 'scrm.contact_your_business_manager' | trans }}</div>
        <div class="scrm-modal__title" style="margin-top: 36px;">{{ 'scrm.no_knowledgeable_business_manager' | trans }}</div>
        <div class="scrm-modal__desc">{{ 'scrm.please_scan_the_qr_code' | trans }}</div>
        <div class="scrm-modal__img">
          <img src="/static-dist/app/img/vue/scrm-qr.png" alt="">
        </div>
        <div class="scrm-modal__tips">
          {{ 'scrm.note' | trans }}
          <a class="ml8" href="https://work.weixin.qq.com" target="_blank">{{ 'scrm.to_open_corporate_wechat' | trans }}</a>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
const characteristic1 = [
  {
    img: '/static-dist/app/img/vue/scrm1.png',
    title: Translator.trans('scrm.attract_customers'),
    desc: Translator.trans('scrm.based_on_the_friend')
  },
  {
    img: '/static-dist/app/img/vue/scrm2.png',
    title: Translator.trans('scrm.online_transaction_truly'),
    desc: Translator.trans('scrm.refined_marketing_management_improves_conversion_efficiency')
  },
  {
    img: '/static-dist/app/img/vue/scrm3.png',
    title: Translator.trans('scrm.fast_customer_follow_up_improve_efficiency'),
    desc: Translator.trans('scrm.functions_of_sending_information')
  }
];

const characteristic2 = [
  {
    img: '/static-dist/app/img/vue/scrm4.png',
    title: Translator.trans('scrm.customer_management'),
    desc: Translator.trans('scrm.view_the_overall_information')
  },
  {
    img: '/static-dist/app/img/vue/scrm5.png',
    title: Translator.trans('scrm.sales_analysis_performance_status_at_a_glance'),
    desc: Translator.trans('scrm.analyze_the_overall_situation_of_the_company_is_marketing')
  },
  {
    img: '/static-dist/app/img/vue/scrm6.png',
    title: Translator.trans('scrm.live_open_class'),
    desc: Translator.trans('scrm.set_up_live_open_classes')
  }
];
export default {
  data() {
    return {
      characteristic1,
      characteristic2,
      visible: false
    }
  },

  methods: {
    handleClickGoScrm() {
      window.open('https://scrm.edusoho.com');
    },

    handleCancel() {
      this.visible = false;
    },

    showModal() {
      this.visible = true;
    }
  }
}
</script>

<style lang="less" scoped>
.aside-container {
  margin-top: -10px;
  padding: 16px;

  .aside-header {
    &__title {
      position: relative;
      padding-left: 22px;
      font-size: 16px;
      color: #333;
      letter-spacing: 0;
      line-height: 26px;

      &::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 8px;
        transform: translateY(-50%);
        width: 2px;
        height: 14px;
        background: #1890ff;
        border-radius: 1px;
      }
    }
  }

  .scrm-sec {
    position: relative;
    padding: 24px;
    margin-top: 16px;
    background-color: #fff;
    border-radius: 2px;
    font-size: 14px;
    color: #333;
    letter-spacing: 0;

    &__title {
      font-weight: 500;
      line-height: 26px;
    }

    &__img {
      position: absolute;
      top: 7px;
      height: 200px;
      right: 120px;

      img {
        width: auto;
        height: 100%;
      }
    }

    &__content {
      margin-top: 20px;
      margin-left: 72px;
      margin-right: 480px;
      line-height: 26px;
    }

    &__btn {
      margin: 24px 0 8px 72px;
    }

    @media (max-width: 1500px) {
      &__content {
        margin-left: 0;
        margin-right: 338px;
      }

      &__btn {
        margin: 24px 0 8px 0;
      }

      &__img {
        right: 97px;
      }
    }

    .scrm-characteristic {
      padding: 50px 72px 26px;

      @media (max-width: 1500px) {
        padding: 50px 0 26px;
      }

      .characteristic-box {
        position: relative;
        padding: 0 320px;
        height: 315px;
      }

      &__item {
        position: absolute;
        top: 0;
        width: 320px;
        height: 315px;

        .characteristic-img {
          width: 100%;
          height: 206px;

          img {
            width: 100%;
            height: 100%;
          }
        }

        .characteristic-title {
          margin-top: 16px;
          font-weight: 500;
          line-height: 18px;
          text-align: center;
        }

        .characteristic-desc {
          margin-top: 8px;
          color: #666;
          line-height: 22px;
        }
      }

      &__item:nth-child(1) {
        left: 0;
      }

      &__item:nth-child(2) {
        left: 50%;
        transform: translateX(-50%);
      }

      &__item:nth-child(3) {
        right: 0;
      }
    }
  }
}

.scrm-modal {
  padding-left: 128px;

  &__title {
    position: relative;
    font-weight: 500;
    font-size: 18px;
    color: #333;
    line-height: 24px;

    &::before {
      content: "";
      position: absolute;
      top: 50%;
      left: -16px;
      transform: translateY(-50%);
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #1890ff;
    }
  }

  &__desc {
    margin-top: 8px;
    font-size: 16px;
    color: #666;
    line-height: 22px;
  }

  &__img {
    margin-top: 18px;
    width: 246px;
    height: 246px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  &__tips {
    margin-top: 18px;
    font-size: 12px;
    color: #999;
    line-height: 16px;

    a {
      font-size: 12px;
    }
  }
}
</style>
