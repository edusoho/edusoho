<template>
  <a-row>
    <a-col :xs="3" :sm="2">
      Col
    </a-col>
    <a-col :xs="21" :sm="22">
      <slot />
    </a-col>
  </a-row>
</template>
