<template>
  <div>
    <h1>404 - Page not found</h1>
  </div>
</template>

<script>
import store from "../store/app";

export default {
  name: "NotFound",
  created() {
    store.commit('setLoading', false)
  }
}
</script>

<style scoped>

</style>
