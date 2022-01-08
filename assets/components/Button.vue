<template>
  <router-link :to="{name: routeName}" class="button-route">
    <button class="button button-primary">
      {{ title }}
    </button>
  </router-link>
</template>
<!-- SCRIPT -->
<script>
export default {
  name: "Button",
  props: ['title', 'routeName']
}
</script>
<!-- END SCRIPT -->

<!-- STYLE -->
<style scoped lang="scss">
@import '../styles/global.scss';

</style>
<!-- END STYLE -->
