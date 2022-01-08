<template>
  <div id="side-menu">
    <div class="guda"><span>Gudabot</span></div>
    <theme-button />
    <div id="side-menu-routes">
      <Button title="Accueil" routeName="home"/>
      <Button title="Inscription" v-if="!isAuthenticated" routeName="register"/>
      <Button title="Connexion" v-if="!isAuthenticated" routeName="login"/>
      <Button title="Dashboard" v-if="isAuthenticated" routeName="dashboard"/>
<!--      Exception pour ce bouton natif-->
      <button class="button button-primary button-route" v-if="isAuthenticated" @click="submit">Logout</button>
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
      console.log('logout')
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
  width: 210px;

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
    width: 200px;
  }
}
</style>
