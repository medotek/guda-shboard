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

@media screen and (max-width: 767px) {
  #content {
    padding: 0 20px;
  }
}

@media screen and (min-width: 768px) {
  #content {
    padding: 0 50px;
  }
}

//@media screen and (min-width: 1200px) {
//  #content {
//    padding: 0 150px;
//  }
//}

#main {
  display: flex;

  #content {
    width: calc(100% - 220px);
    min-height: 100vh;
    margin-left: 220px;
    position: relative;
    background-color: var(--guda-grey)
  }
}

.loading {
  position: fixed;
  top: 0;
  right: 0;
  background-color: var(--background-color-primary);
  width: calc(100vw - 220px);
  height: 100vh;
  z-index: 99
}
</style>
