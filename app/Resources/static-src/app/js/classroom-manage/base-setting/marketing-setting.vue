<template>
  <div>
    <el-form :model="form" :rules="formRule"
             ref="marketingForm"
             label-position="right"
             label-width="150px">
      <div class="course-manage-subltitle cd-mb40 ml0">{{ 'classroom.marketing_setting'|trans }}</div>
      <el-form-item :label="'classroom.price_label'|trans" prop="price">
        <el-col :span="8">
          <el-input ref="price" v-model="form.price" auto-complete="off"></el-input>
          <div class="help-block">
            {{ 'classroom.price_tips'|trans({'courseNum': courseNum, 'price': coursePrice}) }}
          </div>
          <div class="help-block" id="coinPrice"
               v-if="coinSetting.coin_enabled && coinSetting.price_type == 'Coin'"
               :data-rate="coinSetting.cash_rate"
               :data-name="coinSetting.coin_name">
            {{ 'classroom.amount'|trans }}
            {{ form.price * coinSetting.cash_rate }}
            {{ coinSetting.coin_name }}
          </div>
        </el-col>
        <el-col :span="4" class="mlm">{{ 'site.currency.CNY'|trans }}</el-col>
      </el-form-item>

      <!-- <el-form-item :label="'classroom.show_page_label'|trans()">
          <el-radio v-model="form.showable"
                    v-for="item in isShowStatusRadios"
                    :key="item.value"
                    :label="item.value"
                    class="cd-radio">{{ item.label }}
          </el-radio>
          <div class="help-block">
              {{ 'classroom.show_page_tips'|trans }}
          </div>
      </el-form-item> -->

      <el-form-item :label="'classroom.buy_label'|trans({'name':classroomLabel})">
        <el-radio v-model="form.buyable"
                  v-for="item in statusRadios"
                  :key="item.value"
                  :label="item.value"
                  class="cd-radio">{{ item.label }}
        </el-radio>
        <div class="help-block">
          关闭后班级将无法在线购买加入。
        </div>
      </el-form-item>
      <el-form-item>
        <label slot="label">
          {{ 'course.market_setting.contract'|trans }}
          <el-popover
            placement="top"
            :content="'course.market_setting.contract.tip'|trans"
            trigger="hover">
            <a class="es-icon es-icon-help text-normal course-mangae-info__help" slot="reference"></a>
          </el-popover>
        </label>
        <el-col :span="8">
          <el-switch
            v-model="contractEnableSwitch"
            active-color="#46C37B"
            inactive-color="#BFBFBF"
            inline-prompt
          ></el-switch>

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
                    <span class="contract-dropdown-menu-item-label contract-name" @click="selectContract(contract.id, contract.name)" :title="contract.name">
                      {{ contract.name }}
                    </span>
                    <span class="contract-dropdown-menu-item-preview" @click="previewContract(contract.id)">{{ 'course.market_setting.contract.btn.view'|trans }}</span>
                  </div>
                </a-menu-item>
              </a-menu>
            </a-dropdown>
          </div>
        </el-col>
      </el-form-item>
      <el-form-item v-if="contractEnableSwitch">
        <label slot="label">
          {{ 'course.market_setting.contract.mandatory_signature'|trans }}
          <el-popover
            placement="top"
            :content="'course.market_setting.contract.mandatory_signature.tip'|trans"
            trigger="hover">
            <a class="es-icon es-icon-help text-normal course-mangae-info__help" slot="reference"></a>
          </el-popover>
        </label>
        <el-col :span="8">
          <el-switch
            v-model="contractForceSignSwitch"
            active-color="#46C37B"
            inactive-color="#BFBFBF"
            inline-prompt
          ></el-switch>
        </el-col>
      </el-form-item>
      <el-form-item :label="'classroom.expiry_mode_label'|trans">
        <el-radio v-model="form.expiryMode"
                  v-for="(label, value) in expiryModeRadios"
                  :disabled="classroom.status != 'draft'"
                  class="cd-radio"
                  :label="value"
                  :key="value">
          {{ label }}
        </el-radio>
        <a :href="classroomExpiryRuleUrl" target="_blank">{{ 'classroom.watch_expiry_mode_rule'|trans }}</a>
        <div class="color-warning">{{ 'classroom.expiry_mode.first_publish_tips'|trans }}</div>
      </el-form-item>
      <el-form-item prop="expiryValue" :inline-message="true">
        <el-date-picker
          v-if="form.expiryMode == 'date'"
          v-model="form.expiryValue"
          type="date"
          value-format="timestamp"
          :default-value="today"
          :picker-options="dateOptions"
          @blur="validateForm('expiryValue','blur')">
        </el-date-picker>
        <el-col class="help-block" v-if="form.expiryMode =='date'">
          {{ 'classroom.expiry_mode_end_date_tips'|trans }}
        </el-col>
        <div v-if="form.expiryMode == 'days'">
          <el-col :span="8" class="inline-block">
            <el-input v-model="form.expiryValue" :number-format="{
                                                                   maxLength: 8,
                                                                   negative: false,
                                                                   decimal: false,}"
                      @blur="validateForm('expiryValue','blur')"></el-input>
          </el-col>
          <el-col span="1" class="plm">{{ 'site.date.day'|trans }}</el-col>
          <el-col class="help-block" v-if="form.expiryMode =='days'">
            {{ 'classroom.expiry_mode_days_tips'|trans }}
          </el-col>
        </div>
      </el-form-item>

      <el-form-item v-if="vipInstalled && vipEnabled" :label="'vip.level.free_learning_new'|trans">
        <el-select v-model="form.vipLevelId">
          <el-option value="0" :label="'site.default.none'|trans"></el-option>
          <el-option
            v-if="vipLevels"
            v-for="(level) in vipLevels"
            :key="level.id"
            :label="level.name"
            :value="level.id">
          </el-option>
        </el-select>
      </el-form-item>

      <el-form-item :label="'course.marketing_setup.services.provide_services'|trans">
        <el-col>
          <el-popover v-for="(tag, key) in serviceTags"
                      placement="top"
                      :key="key"
                      :content="tag.summary|trans"
                      trigger="hover">
                        <span class="service-item js-service-item"
                              slot="reference"
                              :key="key"
                              :class="tag.active || form.service.indexOf(tag.code) >= 0 ? 'service-primary-item' : ''"
                              :data-code="tag.code"
                              @click="serviceItemClick"
                        >{{ tag.fullName }}</span>
          </el-popover>
        </el-col>
        <el-input class="hidden" type="hidden" v-model="form.service"></el-input>
      </el-form-item>


    </el-form>

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
import * as validation from 'common/element-validation';
import {positive_price} from '../../../../common/element-validation';

export default {
  name: "marketing-info",
  props: {
    classroom: {},
    classroomLabel: '',
    classroomExpiryRuleUrl: '',
    serviceTags: [],
    vipInstalled: false,
    vipEnabled: false,
    vipLevels: [],
    courseNum: 0,
    coursePrice: 0,
    coinSetting: {}
  },
  methods: {
    serviceItemClick(event) {
      let $item = $(event.currentTarget);
      if (!this.form.service) {
        this.form.service = [];
      }

      let code = $item.data('code')
      if ($item.hasClass('service-primary-item')) {
        $item.removeClass('service-primary-item');
        this.form.service.splice(this.form.service.indexOf(code), 1);
      } else {
        $item.addClass('service-primary-item');

        if (this.form.service.indexOf(code) < 0) {
          this.form.service.push(code);
        }
      }
    },
    validateForm() {
      let result = false;
      let invalids = {};
      this.$refs.marketingForm.clearValidate();

      this.$refs.marketingForm.validate((valid, invalidFields) => {
        if (valid) {
          result = true;
        } else {
          invalids = invalidFields;
        }
      });

      return {result: result, invalidFields: invalids};
    },
    getFormData() {
      return this.form;
    },
    fetchContracts() {
      this.$axios.get('/api/simple_contract').then(res => {
        this.contracts = res.data;
        if (this.contracts.length === 0) {
          return;
        }
        if (this.form.contractId == 0) {
          this.form.contractId = this.contracts[0].id;
          this.contractName = this.contracts[0].name;
        }
      });
    },
    selectContract(id, name) {
      this.form.contractId = id;
      this.contractName = name;
      this.contractMenuVisible = false;
    },
    previewContract(id) {
      this.$axios.get(`/api/contract/${id}/preview/classroom_${this.classroom.id}`).then(res => {
        this.contractPreview = res.data;
        this.contractPreviewModalVisible = true;
      });
    },
  },
  computed: {
    getExpiryMode: function () {
      return this.form.expiryMode
    },
    contractEnableSwitch: {
      get() {
        return this.form.contractEnable === 1;
      },
      set(value) {
        this.form.contractEnable = value ? 1 : 0;
      },
    },
    contractForceSignSwitch: {
      get() {
        return this.form.contractForceSign === 1;
      },
      set(value) {
        this.form.contractForceSign = value ? 1 : 0;
      },
    },
  },
  watch: {
    getExpiryMode(newVal, oldVal) {
      this.$refs.marketingForm.clearValidate();
      this.form.expiryValue = null;
      if (newVal == 'date') {
        this.formRule.expiryValue = [
          {
            required: true,
            message: Translator.trans('classroom.manage.expiry_mode_date_error_hint'),
            trigger: 'blur',
          }
        ];
      } else if (newVal == 'days') {
        this.formRule.expiryValue = [
          {
            required: true,
            message: Translator.trans('classroom.manage.expiry_mode_days_error_hint'),
            trigger: 'blur',
          },
          {
            pattern: /(^[1-9][0-9]{0,7}$)/,
            message: Translator.trans('validate.max_effective_time.message'),
            trigger: 'blur',
          }
        ];
      } else {
        this.formRule.expiryValue = [];
      }
    },
    contractPreviewModalVisible(val) {
      this.contractMenuVisible = !val;
    },
  },
  created() {
    if (this.form.expiryMode == 'date') {
      this.formRule.expiryValue = [
        {
          required: true,
          message: Translator.trans('classroom.manage.expiry_mode_date_error_hint'),
          trigger: 'blur',
        }
      ];
    } else if (this.form.expiryMode == 'days') {
      this.formRule.expiryValue = [
        {
          required: true,
          message: Translator.trans('classroom.manage.expiry_mode_days_error_hint'),
          trigger: 'blur',
        },
        {
          pattern: /(^[1-9][0-9]{0,7}$)/,
          message: Translator.trans('validate.max_effective_time.message'),
          trigger: 'blur',
        }
      ];
    } else {
      this.formRule.expiryValue = [];
    }
    this.fetchContracts();
  },
  data() {

    let form = {
      price: this.classroom.price,
      showable: this.classroom.showable,
      buyable: this.classroom.buyable,
      expiryMode: this.classroom.expiryMode,
      expiryValue: this.classroom.expiryMode == 'date' ? this.classroom.expiryValue * 1000 : this.classroom.expiryValue,
      service: this.classroom.service,
      contractEnable: this.classroom.contractId !== 0 ? 1 : 0,
      contractId: this.classroom.contractId,
      contractForceSign: this.classroom.contractForceSign,
    };

    if (this.vipInstalled && this.vipEnabled) {
      form.vipLevelId = this.classroom.vipLevelId;
    }
    return {
      statusRadios: [
        {
          'value': '1',
          'label': Translator.trans('site.open')
        },
        {
          'value': '0',
          'label': Translator.trans('site.close')
        },
      ],
      isShowStatusRadios: [
        {
          'value': '1',
          'label': Translator.trans('site.show')
        },
        {
          'value': '0',
          'label': Translator.trans('site.hide')
        },
      ],
      expiryModeRadios: {
        'date': Translator.trans('classroom.expiry_mode_end_date'),
        'days': Translator.trans('classroom.expiry_mode_days'),
        'forever': Translator.trans('classroom.expiry_mode_forever'),
      },
      form: form,
      formRule: {
        price: [
          {
            required: true,
            message: Translator.trans('validate.required.message', {'display': Translator.trans('classroom.price_label')}),
            trigger: 'blur'
          },
          {
            validator: validation.currency,
            trigger: 'blur'
          },
        ],
        expiryValue: []
      },
      today: Date.now(),
      dateOptions: {
        disabledDate(time) {
          return time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
        }
      },
      contracts: [],
      contractName: this.classroom.contractName,
      contractMenuVisible: false,
      contractPreviewModalVisible: false,
      contractPreview: {},
    };
  }
}
</script>

<style scoped>

</style>
