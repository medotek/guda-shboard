<template>
  <div id="main">
    <Menu/>
    <div id="content">
      <div class="loading" v-show="isLoading"></div>
      <router-view/>
    </div>
  </div>
</template>

<script>
import Menu from "./Menu";
import {mapGetters} from "vuex";
// import style
import '../styles/components/app.scss'

export default {
  name: "App",
  components: {
    Menu
  },
  computed: {
    ...mapGetters({
      isLoading: "getLoading"
    })
  }
}
</script>

<style scoped lang="scss">
#main {
  display: flex;
  Menu {
    width: 30%;
  }
  #content {
    width: 70%;
  }
}

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
