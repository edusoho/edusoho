<template>
  <div class="certificate">
    <div
      class="certificate-year"
      v-for="(item, index) in certificate"
      :key="index"
      :class="[index + 1 == certificate.length ? 'lastChild' : '']"
    >
      <p class="certificate-year__title">{{ item.year }}</p>
      <certificate-item
        v-for="(certificate, ind) in item.certificate"
        :key="ind"
        :certificate="certificate"
        :class="[
          ind == 0 ? 'firstChild' : '',
          ind + 1 == item.certificate.length ? 'lastChild' : '',
        ]"
      >
      </certificate-item>
    </div>
  </div>
</template>

<script>
import CertificateItem from '../certificate-item/index';
export default {
  data() {
    return {
      certificate: [
        {
          year: '2020',
          certificate: [
            {
              img:
                'https://dss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=2534506313,1688529724&fm=26&gp=0.jpg',
              name: '证书名称',
              getTime: '1280977330000',
              currentTime: '1280977330000',
            },
          ],
        },
        {
          year: '2019',
          certificate: [
            {
              img:
                'https://dss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=2534506313,1688529724&fm=26&gp=0.jpg',
              name: '证书名称',
              getTime: '1280977330000',
              currentTime: '1280977330000',
            },
            {
              img:
                'https://dss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=2534506313,1688529724&fm=26&gp=0.jpg',
              name: '证书名称',
              getTime: '1280977330000',
              currentTime: '1280977330000',
            },
            {
              img:
                'https://dss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=2534506313,1688529724&fm=26&gp=0.jpg',
              name: '证书名称',
              getTime: '1280977330000',
              currentTime: '1280977330000',
            },
          ],
        },
        {
          year: '2018',
          certificate: [
            {
              img:
                'https://dss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=2534506313,1688529724&fm=26&gp=0.jpg',
              name: '证书名称',
              getTime: '1280977330000',
              currentTime: '1280977330000',
            },
            {
              img:
                'https://dss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=2534506313,1688529724&fm=26&gp=0.jpg',
              name: '证书名称',
              getTime: '1280977330000',
              currentTime: '1280977330000',
            },
          ],
        },
      ],
    };
  },
  components: {
    CertificateItem,
  },
};
</script>
