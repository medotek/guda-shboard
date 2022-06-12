<template>
  <div>
    <h1>Mon compte</h1>
    <div class="guda-errors" v-if="discordAuthError">Une erreur est survenue lors de la liaison avec discord</div>
    <a class="discord" v-if="!isLinkedSuccessfully && displayAfterPageLoad"
       href="https://discord.com/api/oauth2/authorize?client_id=899946579639799808&permissions=8&redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fdiscord%2Fauth&response_type=code&scope=identify%20guilds%20applications.commands%20messages.read%20bot">Discord</a>
    <button v-if="isLinkedSuccessfully && displayAfterPageLoad" @click="discordRevoke()">Dissocier le compte discord
    </button>
    <div class="discord-account" v-if="this.discordUser">
      <h2 v-if="this.currentDiscordUser.username">{{ this.currentDiscordUser.username }}</h2>
      <img v-if="this.currentDiscordUser.avatarUrl" :src="this.currentDiscordUser.avatarUrl"
           :alt="this.currentDiscordUser.username">
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import axios from "axios";
import store from "../store/app";
import Button from "../components/Button";

export default {
  name: "Account",
  components: {Button},
  computed: {
    ...mapGetters({
      user: "auth/getUser",
      discordUser: "auth/getDiscordUser"
    })
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

    // Register discord user info
    if (this.isLinkedSuccessfully) {
      this.username()
      this.avatar()
    }
  },
  data() {
    return {
      discordAuthError: false,
      isLinkedSuccessfully: false,
      displayAfterPageLoad: false,
      currentDiscordUser: {
        username: null,
        avatarUrl: null
      }
    }
  },
  methods: {
    ...mapActions({
      revoke: "auth/revoke"
    }),
    username() {
      if (this.discordUser) {
        this.currentDiscordUser.username = `${this.discordUser.username}#${this.discordUser.discriminator}`
      }
    },
    avatar() {
      if (this.discordUser) {
        this.currentDiscordUser.avatarUrl = `https://cdn.discordapp.com/avatars/${this.discordUser.id}/${this.discordUser.avatar}.jpg`
      }
    },
    discordRevoke() {
      if (this.user.discordCredentials.accessToken) {
        this.revoke(this.user.discordCredentials.accessToken).then((res) => {
          console.log(res)
          this.isLinkedSuccessfully = false
          // window.location.reload()
        }).catch((e) => {
          console.log(e)
        })
      }
    }
  }
}
</script>

<style scoped>

</style>
