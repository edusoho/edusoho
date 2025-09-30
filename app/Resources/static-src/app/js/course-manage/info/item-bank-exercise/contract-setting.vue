<template>
  <div>
    <a-from>
      <a-form-item style="margin-bottom:22px;">
        <a-col :span="4" style="text-align:right;padding-right:20px">
          <span style="font-size:14px;font-weight:bold;color:rgba(0, 0, 0, 0.56)">
            {{ 'course.market_setting.contract'|trans }}
            <a-popover>
              <template slot="content">
                <div class="text-14">{{ 'course.market_setting.contract.tip'|trans }}</div>
              </template>
              <a-icon type="question-circle"/>
            </a-popover>
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
                    <span class="contract-dropdown-menu-item-label contract-name"
                          @click="selectContract(contract.id, contract.name)" :title="contract.name">{{
                        contract.name
                      }}</span>
                    <span class="contract-dropdown-menu-item-preview" @click="previewContract(contract.id)">{{
                        'course.market_setting.contract.btn.view'|trans
                      }}</span>
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
              <a-popover>
                <template slot="content">
                  <div class="text-14" style="width: 400px">{{ 'course.market_setting.contract.mandatory_signature.tip'|trans }}</div>
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
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
             :centered="true"
             v-model:open="contractPreviewModalVisible"
             :title="'course.market_setting.contract.model.contractSigning'|trans({name: contractPreview.goodsName})"
             :bodyStyle="{'height': 'fit-content', 'max-height': '500px', 'overflow': 'auto'}">

      <div style="width: 100%; display: flex; flex-direction: column; gap: 32px; padding: 32px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
      <span style="opacity: 0;">{{
          'course.market_setting.contract.model.contractNumber' | trans
        }}{{ `: ${contractPreview.code}` }}</span>
          <span style="font-size: 22px; font-weight: 500;">{{ contractPreview.name }}</span>
          <span style="color: #6b7280;">{{
              'course.market_setting.contract.model.contractNumber' | trans
            }}{{ `: ${contractPreview.code}` }}</span>
        </div>

        <div style="color: #6b7280; max-width: 100%" v-html="contractPreview.content"></div>

        <div style="display: flex; gap: 64px;">
          <div style="flex: 1; display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between; gap: 22px;">
            <span style="font-size: 18px; font-weight: 500;">{{ 'course.market_setting.contract.model.partyA' | trans }}：</span>
            <div style="width: 100%; display: flex; flex-direction: column; gap: 22px;">
              <img :src="contractPreview.seal" alt="" style="width: 150px; height: 150px;" />
              <div style="display: flex; align-items: center;">
                <span style="color: #6b7280;">{{ 'course.market_setting.contract.model.signingDate' | trans }}：</span>
                <div style="flex-grow: 1; border-bottom: 1px solid #d1d5db; font-weight: 500;">{{
                    contractPreview.signDate
                  }}
                </div>
              </div>
            </div>
          </div>

          <div style="flex: 1; display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between;">
            <span style="font-size: 18px; font-weight: 500;">{{ 'course.market_setting.contract.model.partyB' | trans }}：</span>
            <div style="width: 100%; display: flex; flex-direction: column; gap: 22px;">
              <div v-if="contractPreview.sign && contractPreview.sign.handSignature" style="display: flex; align-items: center;">
                <span style="color: #6b7280;">{{ 'course.market_setting.contract.model.handSignature' | trans }}：</span>
                <div style="flex-grow: 1; border-bottom: 1px solid #d1d5db; font-weight: 500;"><span style="opacity: 0;">x</span></div>
              </div>

              <div style="display: flex; align-items: center;">
                <span style="color: #6b7280;">{{ 'course.market_setting.contract.model.partyBName' | trans }}：</span>
                <div style="flex-grow: 1; border-bottom: 1px solid #d1d5db; font-weight: 500;"><span style="opacity: 0;">x</span></div>
              </div>

              <div v-if="contractPreview.sign && contractPreview.sign.IDNumber" style="display: flex; align-items: center;">
                <span style="color: #6b7280;">{{ 'course.market_setting.contract.model.iDNumber' | trans }}：</span>
                <div style="flex-grow: 1; border-bottom: 1px solid #d1d5db; font-weight: 500;"><span style="opacity: 0;">x</span></div>
              </div>

              <div v-if="contractPreview.sign && contractPreview.sign.phoneNumber" style="display: flex; align-items: center;">
            <span style="color: #6b7280;">{{
                'course.market_setting.contract.model.contactInformation' | trans
              }}：</span>
                <div style="flex-grow: 1; border-bottom: 1px solid #d1d5db; font-weight: 500;"><span style="opacity: 0;">x</span></div>
              </div>

              <div style="display: flex; align-items: center;">
                <span style="color: #6b7280;">{{ 'course.market_setting.contract.model.signingDate' | trans }}：</span>
                <div style="flex-grow: 1; border-bottom: 1px solid #d1d5db; font-weight: 500;">{{
                    contractPreview.signDate
                  }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <div style="display: flex; justify-content: center;">
          <a-button @click="contractPreviewModalVisible = false">{{
              'course.market_setting.contract.btn.close' | trans
            }}
          </a-button>
        </div>
      </template>
    </a-modal>
  </div>
</template>

<script>

export default {
  name: 'market-setting',
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
    };
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
};
</script>
