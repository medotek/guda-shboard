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

.button-route {
  margin: 5px 0;
}
.button {
  width: 100%;
  border: none;
  color: var(--text-primary-color);
  border-radius: 5px;
  padding: 10px 5px;
  cursor: pointer;
  font-size: 14px;
}

.button-primary {
  background-color: var(--guda-color);
  font-weight: 600;
}
</style>
<!-- END STYLE -->
