<template>
  <div>
    <van-loading v-if="!contractDetail.content" class="loading-center" />

    <template v-else>
      <div class="sign-contract-container">
        <div class="flex-1 flex flex-col mb-24 rounded-8 border border-solid rounded-lg" style="overflow-y: auto;border-color: #DFE2E6;">
          <div class="py-20 px-32 flex-1 overflow-y-auto" v-html="contractDetail.content"></div>
          <div class="check-contract-detail" >
            <div class="inline-flex items-center" @click="viewContractDetail">
              <svg width="17" height="16" viewBox="0 0 17 16" fill="none" class="color-primary">
                <path d="M3.84205 13.6576V6.32422H1.84204V13.6576H3.84205Z" stroke-linejoin="round"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.84204 6.32606C6.21634 4.15306 7.59231 2.90619 7.96997 2.58544C8.53644 2.10431 9.17671 2.30634 9.17671 3.5104C9.17671 4.71446 7.41487 5.40886 7.41487 6.32606C7.41374 6.33153 9.66681 6.3319 14.174 6.32716C14.7265 6.32656 15.1748 6.77393 15.1754 7.3264V7.32743C15.1754 7.8805 14.727 8.32883 14.174 8.32883H11.5042C11.1012 10.9864 10.8789 12.4313 10.8372 12.6634C10.7747 13.0116 10.4424 13.6594 9.48601 13.6594C8.84844 13.6594 7.18934 13.6594 3.84204 13.6594V6.32606Z" stroke-linejoin="round"/>
              </svg>
              <span class="color-primary" style="margin-left: 10px;">{{ $t('contract.viewContractDetail') }}</span>
            </div>
          </div>
        </div>

        <van-form ref="formRef">
          <template v-for="(a, key) in rules">
            <van-field v-if="key == 'IDNumber'" v-model="formItems[key]"
              autocomplete="off" :maxlength="18"
              :placeholder="$t('contract.placeholder.IDNumber')"
              :name="key" :rules="rules[key]"></van-field>

            <van-field v-else-if="key == 'truename'" v-model="formItems[key]"
              autocomplete="off" :placeholder="$t('contract.placeholder.truename')"
              :name="key" :rules="rules[key]"></van-field>

            <van-field v-else-if="key == 'phoneNumber'" v-model="formItems[key]"
              autocomplete="off" :maxlength="11"
              :placeholder="$t('contract.placeholder.phoneNumber')"
              :name="key" :rules="rules[key]"></van-field>

            <template v-else>
              <div v-if="!formItems.handSignature" @click="writeSignatureVisible = true"
                class="flex items-center justify-center py-8 mt-16 leading-24 border border-dashed border-B6BABF rounded-md">
                <svg width="17" height="16" viewBox="0 0 17 16" fill="none">
                  <path d="M2.83398 14H14.834" stroke="#5E6166" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M4.16602 8.90663V11.3333H6.60508L13.4993 4.43603L11.0644 2L4.16602 8.90663Z" stroke="#5E6166" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
                <span class="ml-8 text-16 font-medium text-37393D">{{ $t('contract.handwritten') }}</span>
              </div>

              <div v-if="formItems.handSignature" class="flex mt-16">
                <div class="flex-1 text-center border border-dashed border-B6BABF rounded-md" style="height: 42px;">
                  <img class="h-full" :src="formItems.handSignature" />
                </div>
                <div class="flex items-center ml-16 py-8 px-16" @click="writeSignatureVisible = true" style="border-color: #fff;">
                  <svg class="color-primary" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M2.33398 14H14.334" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3.66602 8.90663V11.3333H6.10508L12.9993 4.43603L10.5644 2L3.66602 8.90663Z" stroke-linejoin="round"/>
                  </svg>
                  <span class="color-primary ml-8">{{ $t('contract.reSign') }}</span>
                </div>
              </div>
            </template>
          </template>
        </van-form>
      </div>

      <van-popup v-model="writeSignatureVisible" position="bottom" closeable :style="{ height: '100%' }">
        <WriteSignature @submit="getWriteSignature" />
      </van-popup>

      <div class="fixed bottom-0 w-full py-8 px-16 box-border flex">
        <van-button :disabled="!canSubmit" class="flex-1 rounded-md"
          type="primary" @click="submitForm"
          :loading="submitLoading" :loading-text="$t('contract.signing')">
          {{ $t('contract.confirmSign') }}
        </van-button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { Toast } from 'vant'
import Api from '@/api'
import { truenameValidator, phoneNumberValidator, IDNumberValidator, handSignatureValidator } from './validator.js'
import WriteSignature from './WriteSignature.vue';

const baseRules = {
  truename: truenameValidator(),
  IDNumber: IDNumberValidator(),
  phoneNumber: phoneNumberValidator(),
  handSignature: handSignatureValidator(),
}

export default {
  name: 'SignContract',
  components: {
    WriteSignature
  },
  data() {
    return {
      rules: {},
      id: this.$route.params.id,
      goodsKey: this.$route.params.goodsKey,
      contractDetail: {},
      writeSignatureVisible: false,
      submitLoading: false,
      formItems: {}
    }
  },
  computed: {
    canSubmit() {
      for (const key in this.formItems) {
        if (!this.formItems[key] || !this.formItems[key].trim()) return false
      }

      return true
    }
  },
  created() {
    this.initFormItems()
  },
  methods: {
    async initFormItems() {
      const res = await Api.getSignContractTemplate({
        query: { id: this.id, goodsKey: this.goodsKey },
        params: { viewMode: 'html' }
      })

      this.contractDetail = res

      res.signFields.forEach(item => {
        this.$set(this.formItems, item.field, item.default)
        this.$set(this.rules, item.field, baseRules[item.field])
      })
    },
    viewContractDetail() {
      this.$router.push({ name: 'contractDetail', prams: { id: this.id, goodsKey: this.goodsKey } })
    },
    getWriteSignature(url) {
      this.$set(this.formItems, 'handSignature', url)
      this.$refs.formRef.validate('handSignature')
      this.writeSignatureVisible = false
    },
    async submitForm() {
      await this.$refs.formRef.validate()

      try {
        this.submitLoading = true

        await Api.signContract({
          query: { id: this.id },
          data: {
            contractCode: this.contractDetail.code,
            goodsKey: this.goodsKey,
            ...this.formItems,
          }
        })

        Toast.success(this.$t('contract.signSuccess'))
        this.routerBack()
      } finally {
        this.submitLoading = false
      }
    },
    routerBack() {
      this.$router.go(-1)
    }
  }
}
</script>

<style lang="scss" scoped>
.sign-contract-container {
  position: fixed;
  top: 62px;
  bottom: 80px;
  left: 0;
  right: 0;
  display: flex;
  flex-direction: column;
  padding: 0 16px;
}

.loading-center {
  position: fixed;
  top: 50%;
  left: 50%;
  margin: -15px 0 0 -15px;
}

::v-deep .van-cell.van-field {
  padding: 0;
  margin-bottom: 16px;

  input {
    padding: 5px 12px;
    border: solid 1px #DCDEE0;
    border-radius: 6px;
  }

  &::after {
    display: none;
  }
}

.text-37393D {
  color: #37393D;
}

.border-B6BABF {
  border-color: #B6BABF;
}

.check-contract-detail {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 62px;
  box-shadow: 0 -1px 0 0 #F0F0F0;
  background-color: #fff;
}

svg.color-primary path {
  stroke: $primary-color;
}
</style>
