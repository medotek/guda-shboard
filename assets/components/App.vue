<template>
  <div>
    <div class="loading" v-show="isLoading"></div>
    <router-view/>
  </div>
</template>

<script>
import {mapGetters} from "vuex";
// import style
import '../styles/components/app.scss'

export default {
  name: "App",
  computed: {
    ...mapGetters({
      isLoading: "getLoading"
    })
  }
}
</script>

<style scoped>
.loading {
  position: absolute;
  top: 0;
  left: 0;
  background-color: black;
  width: 100vw;
  height: 100vh;
  z-index: 99
}
</style>
