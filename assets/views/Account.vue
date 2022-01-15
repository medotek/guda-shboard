<template>
  <div>
    <h1>Mon compte</h1>
    <div class="guda-errors" v-if="discordAuthError">Une erreur est survenue lors de la liaison avec discord</div>
    <a class="discord" v-if="!isLinkedSuccessfully && displayAfterPageLoad"
       href="https://discord.com/api/oauth2/authorize?client_id=899946579639799808&redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fdiscord%2Fauth&response_type=code&scope=identify">Discord</a>
    <div class="discord-account">
      <h2 v-if="username()">{{ username() }}</h2>
      <img v-if="avatar() && username()" :src="avatar()" :alt="username()">
    </div>
  </div>
</template>

<script>
import {mapGetters} from "vuex";
import axios from "axios";
import store from "../store/app";

export default {
  name: "Account",
  computed: {
    ...mapGetters({
      user: "auth/getUser",
      discordUser: "auth/getDiscordUser"
    }),
    // async getDiscordInfo() {
    //   return await axios.get(
    //       'https://discord.com/api/v8/users/@me',
    //       {
    //         headers: {
    //           'Authorization': `Bearer ${this.user.discordCredentials.accessToken}`
    //         }
    //       }
    //   );
    // }
  },
  created() {
    store.commit('setLoading', true)
    this.discordAuthError = this.$route.params.discordAuth === 'error';
    // Display discord button link account if the user data is not stored
    if (store.getters['auth/getDiscordUser']) {
      this.isLinkedSuccessfully = true
    }
    // Wait for the rules above
    this.displayAfterPageLoad = true
    store.commit('setLoading', false)
  },
  data() {
    return {
      discordAuthError: false,
      isLinkedSuccessfully: false,
      displayAfterPageLoad: false
    }
  },
  methods: {
    username() {
      if (this.isLinkedSuccessfully) {
        return `${this.discordUser.username}#${this.discordUser.discriminator}`
      }
      return false
    },
    avatar() {
      if (this.isLinkedSuccessfully) {
        return `https://cdn.discordapp.com/avatars/${this.discordUser.id}/${this.discordUser.avatar}.jpg`
      }
      return false
    }
  }
}
</script>

<style scoped>

</style>
