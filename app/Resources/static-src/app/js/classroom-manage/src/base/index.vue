<template>
    <div class="course-manage-info pbl">
        <base-info ref="baseInfo"
                   v-bind:classroom="classroom"
                   v-bind:tags="tags"
                   v-bind:enable-org="enableOrg"
                   v-bind:cover="cover"
                   v-bind:cover-crop-url="coverCropUrl"
        ></base-info>
        <marketing-setting ref="marketing"
                           v-bind:classroom="classroom"
                           v-bind:classroom-label="classroomLabel"
                           v-bind:classroom-expiry-rule-url="classroomExpiryRuleUrl"
                           v-bind:service-tags="serviceTags"
                           v-bind:vip-installed="vipInstalled"
                           v-bind:vip-enabled="vipEnabled"
                           v-bind:vip-levels="vipLevels"
                           v-bind:coin-setting="coinSetting"
                           v-bind:course-num="courseNum"
                           v-bind:course-price="coursePrice"
        ></marketing-setting>
        <el-row class="pbl">
            <el-col span="18" offset="6" class="pbl">
                <button class="cd-btn cd-btn-primary" @click="submitForm">{{ 'form.btn.save'|trans }}</button>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import baseInfo from './base-info';
    import marketingSetting from './marketing-setting';

    export default {
        name: "manage-info",
        components: {
            baseInfo,
            marketingSetting
        },
        props: {
            classroom: {},
            tags: [],
            enableOrg: 0,
            cover: '',
            coverCropUrl: '',
            imageUploadUrl: '',
            flashUploadUrl: '',
            classroomLabel: '',
            classroomExpiryRuleUrl: '',
            serviceTags: [],
            vipInstalled: false,
            vipEnabled: false,
            vipLevels: {},
            courseNum: 0,
            coinSetting: {},
            coursePrice: 0,
            infoSaveUrl: ''
        },
        methods: {
            validForm() {
                let invalidField = '';
                let valids = {
                    baseInfo: this.$refs.baseInfo.validateForm(),
                    marketing: this.$refs.marketing.validateForm()
                };

                for (let key in valids) {
                    if (!valids[key].result) {
                            return false;
                    }
                }

                return true;
            },
            submitForm() {
                if (!this.validForm()) {
                    return;
                }

                let formData = {'_csrf_token': $('meta[name=csrf-token]').attr('content')};
                Object.assign(
                    formData,
                    this.$refs.baseInfo.getFormData(),
                    this.$refs.marketing.getFormData()
                );

                this.$axios.post(this.infoSaveUrl, this.$qs.stringify(formData), {emulateJSON: true}).then((res) => {
                    cd.message({type: 'success', message: Translator.trans('site.save_success_hint')});
                    window.location.reload();
                });
            }
        },
        data() {
            return {};
        }
    }
</script>

<style scoped>

</style>