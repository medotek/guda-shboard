<template>
  <div id="side-menu">
    <div class="guda"><span>Gudabot</span></div>
    <theme-button/>
    <div id="side-menu-routes">
      <Button title="Accueil" routeName="home"/>
      <Button title="Inscription" v-if="!isAuthenticated" routeName="register"/>
      <Button title="Connexion" v-if="!isAuthenticated" routeName="login"/>
      <Button title="Dashboard" v-if="isAuthenticated" routeName="dashboard"/>
      <!--      Exception pour ce bouton natif-->
      <button class="button button-primary button-route" v-if="isAuthenticated" @click="submit">Logout</button>
      <p v-if="isAuthenticated">Salut <span class="username">{{ username }}</span></p>
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
      user: "auth/getUser"
    }),
    username() {
      return this.user.name !== '' ? this.user.name : 'mec sans nom (tu n\'as pas de pseudo)'
    }
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
    }
  },
}
</script>

<style scoped lang="scss">
@import '../styles/global.scss';

#side-menu {
  height: 100vh;
  width: 220px;
  background: var(--background-menu);

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
    margin-top: 2rem;
    flex-direction: column;
    vertical-align: middle;
    justify-content: center;
    padding: 0 10px;
    width: 200px;
    color:white;

    .username {
      font-weight: 600;
      color: var(--guda-color);
    }
  }
}
</style>
