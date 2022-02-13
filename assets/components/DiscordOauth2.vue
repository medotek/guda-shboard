<template>
  <div>
  </div>
</template>

<script>
import axios from 'axios'
import {mapActions, mapGetters} from "vuex";
import store from "../store/app";

export default {
  name: "DiscordOauth2",
  computed: {
    ...mapGetters({
      user: "auth/getUser",
      discordUser: "auth/getDiscordUser"
    }),
    userId() {
      return this.user.id
    }
  },
  created() {
    store.commit('setLoading', true)
    this.onWindowLoad();
  },
  methods: {
    ...mapActions({
      discordLogin: 'auth/discordLogin'
    }),
    async onWindowLoad() {
      const urlSearchParams = new URLSearchParams(window.location.search);
      const {code} = Object.fromEntries(urlSearchParams.entries())
      if (code) {
        try {
          // Get credentials
          const linkDiscord = await axios.post('http://localhost:3333/api/auth/discord/redirect?code='+code)
          const {accessToken, refreshToken} = linkDiscord.data;
          // Save in db
          if (!store.getters['auth/getDiscordUser']) {
            const discordLogin = await this.discordLogin({
              accessToken: accessToken,
              refreshToken: refreshToken,
              userId: this.userId
            })
          }

          await this.$router.push({
            name: 'account'
          });
        } catch (e) {
          console.log(e)
          await this.$router.push({
            name: 'account',
            params: {
              discordAuth: 'error'
            }
          });
        }
      }
    },
  }
}
</script>

<style scoped>

</style>
