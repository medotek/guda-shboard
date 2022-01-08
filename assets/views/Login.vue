<template>
  <div class="form-container">
    <form class="login-form" @submit.prevent="submit">
      <h1>Connexion</h1>
      <input type="text"
             aria-label="Email"
             placeholder="Email"
             v-model="form.email"
             required
      >
      <input type="password"
             id="password-input"
             placeholder="Mot de passe"
             required
             v-model="form.password"
      >
      <button type="submit">Se connecter</button>
    </form>
  </div>
</template>

<script>
import {mapActions} from "vuex";
import '../styles/components/form.scss'

export default {
  name: "Login",
  data() {
    return {
      form: {
        email: "",
        password: ""
      }
    }
  },
  methods: {
    ...mapActions({
      login: 'auth/login'
    }),
    async submit() {
      await this.login(this.form).then(() => {
        this.$router.replace({
          name: 'dashboard',
        })
        console.log("registered")
      })
    }
  }
}
</script>

<style scoped lang="scss">

</style>
