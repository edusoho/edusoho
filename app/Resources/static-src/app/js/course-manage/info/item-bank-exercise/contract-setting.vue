<template>
  <div>
    <a-from>
      <a-form-item>
        <a-col :span="4">
          <a-tooltip placement="top" :title="'启用后，学员开始学习前需完成电子合同签署'">
            <span>
              {{ '电子合同' | trans }}
              <a-icon type="question-circle" style="margin-left: 8px; color: #888;" />
            </span>
          </a-tooltip>
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
                    <span class="contract-dropdown-menu-item-preview" @click="previewContract(contract.id)">预览</span>
                  </div>
                </a-menu-item>
              </a-menu>
            </a-dropdown>
          </div>
        </a-col>
      </a-form-item>

      <a-from-item v-if="contractEnableSwitch">
        <a-col :span="4">
          <a-tooltip placement="top" :title="'开启强制签署后，学员完成合同签署后才能学习，未签署的学员每次进入学习页面时弹窗提示签署合同。'">
            <span>
              {{ '强制签署' | trans }}
              <a-icon type="question-circle" style="margin-left: 8px; color: #888;" />
            </span>
          </a-tooltip>

        </a-col>
        <a-col :span="20">
          <a-switch
            id="contract-force-sign"
            v-model="contractForceSignSwitch"
            checked-color="#46C37B"
            un-checked-color="#BFBFBF"
          />
        </a-col>
      </a-from-item>
    </a-from>
    <p></p>


    <a-modal :width="900"
             v-model:open="contractPreviewModalVisible"
             :title="`${contractPreview.goodsName}-电子合同签署`"
             :bodyStyle="{'height': 'fit-content', 'max-height': '500px', 'overflow': 'auto'}"
    >
      <div class="w-full flex flex-col space-y-32 p-32">
        <div class="flex items-center justify-between">
          <span class="opacity-0">{{ `合同编号: ${contractPreview.code}` }}</span>
          <span class="text-22 font-medium">{{ contractPreview.name }}</span>
          <span class="text-gray-500">{{ `合同编号: ${contractPreview.code}` }}</span>
        </div>
        <div class="text-gray-500" v-html="contractPreview.content"></div>
        <div class="flex space-x-64">
          <div class="flex-1 flex flex-col items-start justify-between space-y-22">
            <span class="text-18 font-medium">甲方：</span>
            <div class="w-full flex flex-col space-y-22">
              <img :src="contractPreview.seal" alt="甲方印章" class="w-150 h-150" />
              <div class="flex items-center">
                <span class="text-gray-500">签约日期：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ contractPreview.signDate }}</div>
              </div>
            </div>
          </div>
          <div class="flex-1 flex flex-col items-start justify-between">
            <span class="text-18 font-medium">乙方：</span>
            <div class="w-full flex flex-col space-y-22">
              <div v-if="contractPreview.sign && contractPreview.sign.handSignature" class="flex items-center">
                <span class="text-gray-500">手写签名：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium"><span class="opacity-0">x</span></div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">乙方姓名：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium"><span class="opacity-0">x</span></div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.IDNumber" class="flex items-center">
                <span class="text-gray-500">身份证号：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium"><span class="opacity-0">x</span></div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.phoneNumber" class="flex items-center">
                <span class="text-gray-500">联系方式：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium"><span class="opacity-0">x</span></div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">签约日期：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ contractPreview.signDate }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-center">
          <a-button @click="contractPreviewModalVisible = false">关闭</a-button>
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
      this.$axios.get(`/api/contract/${id}/preview/exercise_${this.exercise.id}`).then(res => {
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
