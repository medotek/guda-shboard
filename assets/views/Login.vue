<template>
  <div class="form-container">
    <form class="login-form" @submit.prevent="submit">
      <div class="guda-success" v-if="isUserCreated">Ton compte a été créé sans soucis bro</div>
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
      <ul class="guda-errors" v-if="errors.length">
        <li class="guda-error" v-for="error in errors">{{ error }}</li>
      </ul>
    </form>
  </div>
</template>

<script>
import {mapActions} from "vuex";
import '../styles/components/form.scss'
import Button from "../components/Button";


export default {
  name: "Login",
  components: {
    Button
  },
  created() {
    this.isUserCreated = this.$route.params.registerStatus === 'created';
  },
  data() {
    return {
      form: {
        email: "",
        password: ""
      },
      errors: [],
      isUserCreated: false
    }
  },
  methods: {
    ...mapActions({
      login: 'auth/login'
    }),
    async submit() {
      await this.login(this.form).then((r) => {
        this.$router.replace({
          name: 'dashboard',
        })
      }).catch(err => {
        this.errors.push('Identifiant ou mot de passe inconnu')
      })
    }
  }
}
</script>

<style scoped lang="scss">
.discord {
  color: white;
  font-weight: 600;
  text-align: center;
}
</style>
