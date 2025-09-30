<template></template>

<script>

export default {
  created() {
    this.$error({
      title: document.getElementById('errorMessage').value,
      okText: '确定',
      centered: true,
      onOk: () => {
        parent.location.href = document.getElementById('returnUrl').value;
      },
    });
  }
}
</script>
