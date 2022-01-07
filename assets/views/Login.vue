<template>
  <div>
    <h1>Login</h1>
    <form action="" @submit.prevent="submit">
      <input type="text"
             aria-label="Email"
             placeholder="Email"
             v-model="form.email"
             required
      >
      <input type="password"
             id="password-input"
             placeholder="Password"
             required
             v-model="form.password"
      >
      <button type="submit">Register</button>
    </form>
  </div>
</template>

<script>
import {mapActions} from "vuex";

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
