<template>
  <div>
    <h1>Register</h1>
    <form action="" @submit.prevent="submit">
      <input type="text"
             aria-label="Username"
             placeholder="Username"
             v-model="form.username"
             required
      >
      <input type="email"
             name=""
             id="email-input"
             placeholder="Email"
             required
             v-model="form.email"
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
  name: "Register",
  data() {
    return {
      form: {
        username: "",
        email: "",
        password: ""
      }
    }
  },
  methods: {
    ...mapActions({
      register: 'auth/register'
    }),
    submit() {
      this.register(this.form).then(() => {
        console.log("logged")
      })
    }
  }
}
</script>

<style scoped>

</style>
