<template>
  <div>
    <a-from>
      <a-form-item style="margin-bottom:22px;">
        <a-col :span="4" style="text-align:right;padding-right:20px">
          <span style="font-size:14px;font-weight:bold;color:rgba(0, 0, 0, 0.56)">
            {{ 'course.market_setting.contract'|trans }}
            <a class="es-icon es-icon-help text-normal course-mangae-info__help" data-container="body" data-toggle="popover" data-trigger="hover" data-placement="top" :data-content="'course.market_setting.contract.tip'|trans" data-original-title="" title=""></a>
          </span>
        </a-col>
        <a-col :span="20">
          <a-switch
            id="contract-enable"
            v-model="contractEnableSwitch"
            active-color="#46C37B"
            inactive-color="#BFBFBF"
            inline-prompt
          ></a-switch>
        </a-col>
        <a-col :span="4"></a-col>
        <a-col :span="20">
          <div v-if="contractEnableSwitch">
            <a-dropdown :trigger="['click']" v-model="contractMenuVisible">
              <a-button class="contract-dropdown-btn">
                <span class="contract-name">{{ contractName }}</span>
                <a-icon type="down"/>
              </a-button>
              <a-menu slot="overlay" class="contract-dropdown-menu">
                <a-menu-item
                  v-for="contract in contracts"
                  :key="contract.id"
                >
                  <div class="contract-dropdown-menu-item">
                    <span class="contract-dropdown-menu-item-label contract-name" @click="selectContract(contract.id, contract.name)" :title="contract.name">{{ contract.name }}</span>
                    <span class="contract-dropdown-menu-item-preview" @click="previewContract(contract.id)">{{ 'course.market_setting.contract.btn.view'|trans }}</span>
                  </div>
                </a-menu-item>
              </a-menu>
            </a-dropdown>
          </div>
        </a-col>
      </a-form-item>

      <a-from-item v-if="contractEnableSwitch">
        <a-col :span="4" style="text-align:right;padding-right:20px;margin-bottom:22px;">
            <span style="font-size:14px;font-weight:bold;color:rgba(0, 0, 0, 0.56)">
              {{ 'course.market_setting.contract.mandatory_signature'|trans }}
              <a class="es-icon es-icon-help text-normal course-mangae-info__help" data-container="body" data-toggle="popover" data-trigger="hover" data-placement="top" :data-content="'course.market_setting.contract.mandatory_signature.tip'|trans" data-original-title="" title=""></a>
            </span>
        </a-col>
        <a-col :span="20" style="margin-bottom:22px;">
          <a-switch
            id="contract-force-sign"
            v-model="contractForceSignSwitch"
            checked-color="#46C37B"
            un-checked-color="#BFBFBF"
          />
        </a-col>
      </a-from-item>
    </a-from>


    <a-modal :width="900"
             v-model:open="contractPreviewModalVisible"
             :title="'course.market_setting.contract.model.contractSigning'|trans({name: contractPreview.goodsName})"
             :bodyStyle="{'height': 'fit-content', 'max-height': '500px', 'overflow': 'auto'}"
    >
      <div class="w-full flex flex-col space-y-32 p-32">
        <div class="flex items-center justify-between">
          <span style="opacity: 0">{{ 'course.market_setting.contract.model.contractNumber' | trans }}{{ `: ${contractPreview.code}` }}</span>
          <span class="text-22 font-medium">{{ contractPreview.name }}</span>
          <span class="text-gray-500">{{ 'course.market_setting.contract.model.contractNumber' | trans }}{{ `: ${contractPreview.code}` }}</span>
        </div>
        <div class="text-gray-500" v-html="contractPreview.content"></div>
        <div class="flex space-x-64">
          <div class="flex-1 flex flex-col items-start justify-between space-y-22">
            <span class="text-18 font-medium">{{ 'course.market_setting.contract.model.partyA' | trans }}：</span>
            <div class="w-full flex flex-col space-y-22">
              <img :src="contractPreview.seal" alt="" class="w-150 h-150" />
              <div class="flex items-center">
                <span class="text-gray-500">{{ 'course.market_setting.contract.model.signingDate' | trans }}：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ contractPreview.signDate }}</div>
              </div>
            </div>
          </div>
          <div class="flex-1 flex flex-col items-start justify-between">
            <span class="text-18 font-medium">{{ 'course.market_setting.contract.model.partyB' | trans }}：</span>
            <div class="w-full flex flex-col space-y-22">
              <div v-if="contractPreview.sign && contractPreview.sign.handSignature" class="flex items-center">
                <span class="text-gray-500">{{ 'course.market_setting.contract.model.handSignature' | trans }}：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium"><span class="opacity-0">x</span></div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">{{ 'course.market_setting.contract.model.partyBName' | trans }}：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium"><span class="opacity-0">x</span></div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.IDNumber" class="flex items-center">
                <span class="text-gray-500">{{ 'course.market_setting.contract.model.iDNumber' | trans }}：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium"><span class="opacity-0">x</span></div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.phoneNumber" class="flex items-center">
                <span class="text-gray-500">{{ 'course.market_setting.contract.model.contactInformation' | trans }}：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium"><span class="opacity-0">x</span></div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">{{ 'course.market_setting.contract.model.signingDate' | trans }}：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ contractPreview.signDate }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-center">
          <a-button @click="contractPreviewModalVisible = false">{{ 'course.market_setting.contract.btn.close' | trans }}</a-button>
        </div>
      </template>
    </a-modal>
  </div>
</template>

<script>

export default {
  name: "market-setting",
  props: {
    exercise: {},
  },
  mounted() {
    window.marketingForm = this.marketingForm;
    $('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'hover',
    });
  },
  watch: {
    contractEnableSwitch(newVal) {
      if (newVal) {
        this.fetchContracts();
      }
    },
    contractPreviewModalVisible(val) {
      this.contractMenuVisible = !val;
    },
  },
  methods: {
    fetchContracts() {
      this.$axios.get('/api/simple_contract').then(res => {
        this.contracts = res.data;
        if (this.contracts.length === 0) {
          return;
        }
        if (this.marketingForm.contractId == 0) {
          this.marketingForm.contractId = this.contracts[0].id;
          this.contractName = this.contracts[0].name;
        }
      });
    },
    selectContract(id, name) {
      this.marketingForm.contractId = id;
      this.contractName = name;
      this.contractMenuVisible = false;
    },
    previewContract(id) {
      this.$axios.get(`/api/contract/${id}/preview/itemBankExercise_${this.exercise.id}`).then(res => {
        this.contractPreview = res.data;
        this.contractPreviewModalVisible = true;
      });
    },
  },
  data() {
    let form = {
      contractEnable: this.exercise.contractId !== 0 ? 1 : 0,
      contractId: this.exercise.contractId,
      contractForceSign: this.exercise.contractForceSign,
    };
    this.fetchContracts();

    return {
      contracts: [],
      contractName: this.exercise.contractName,
      contractMenuVisible: false,
      contractPreviewModalVisible: false,
      contractPreview: {},
      marketingForm: form,
    }
  },
  computed: {
    contractEnableSwitch: {
      get() {
        return this.marketingForm.contractEnable === 1;
      },
      set(value) {
        this.marketingForm.contractEnable = value ? 1 : 0;
      },
    },
    contractForceSignSwitch: {
      get() {
        return this.marketingForm.contractForceSign === 1;
      },
      set(value) {
        this.marketingForm.contractForceSign = value ? 1 : 0;
      },
    },
  },
}
</script>

<style scoped>

</style>
