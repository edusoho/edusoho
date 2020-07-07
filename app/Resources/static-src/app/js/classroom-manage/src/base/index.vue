<template>
    <div class="course-manage-info">
        <base-info ref="baseInfo"
                   v-bind:classroom="classroom"
                   v-bind:tags="tags"
                   v-bind:enable-org="enableOrg"
                   v-bind:cover="cover"
                   v-bind:cover-crop-url="coverCropUrl"
        ></base-info>
        <marketing-setting ref="marketingSetting"
                           v-bind:classroom="classroom"
                           v-bind:classroom-label="classroomLabel"
                           v-bind:classroom-expiry-rule-url="classroomExpiryRuleUrl"
                           v-bind:service-tags="serviceTags"
                           v-bind:vip-installed="vipInstalled"
                           v-bind:vip-enabled="vipEnabled"
                           v-bind:vip-levels="vipLevels"
        ></marketing-setting>
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
        },
        data() {
            return {};
        }
    }
</script>

<style scoped>

</style>