<template>
  <div id="side-menu">
    <div class="guda"><span>Gudabot</span></div>
    <theme-button />
    <div id="side-menu-routes">
      <Button title="Home" routeName="home"/>
      <Button title="Register" v-if="!isAuthenticated" routeName="register"/>
      <Button title="Login" v-if="!isAuthenticated" routeName="login"/>
      <Button title="Dashboard" v-if="isAuthenticated" routeName="dashboard"/>
      <Button title="Logout" v-if="isAuthenticated" @click="submit"/>
    </div>
  </div>
</template>

<script>
import Button from "./Button";
import {mapActions, mapGetters} from "vuex";
import ThemeButton from "../components/ThemeButton.vue";

export default {
  name: "Menu",
  computed: {
    ...mapGetters({
      isAuthenticated: "auth/isAuthenticated",
    }),
  },
  components: {
    Button,
    ThemeButton
  },
  methods: {
    ...mapActions({
      logout: "auth/logout",
    }),
    submit() {
      this.logout();
      this.$router.replace({
        name: "home",
      });
    },
  },
}
</script>

<style scoped lang="scss">
@import '../styles/global.scss';
#side-menu {
  height: 100vh;
  width: 200px;

  .guda {
    color: var(--guda-color);
    font-family: 'Nunito', sans-serif;
    font-weight: bold;
    font-size: 24px;
    width: 100%;
    text-align: center;
    padding-top: 15px;
  }

  #side-menu-routes {
    display: flex;
    flex-direction: column;
    vertical-align: middle;
    height: 100%;
    justify-content: center;
    padding-left: 10px;
  }
}
</style>
