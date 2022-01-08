<template>
  <div class="form-container">
    <form @submit.prevent="submit">
      <h1>Inscription</h1>
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
      <password v-model="form.password"
                placeholder="Mot de passe"
                @score="showScore"
      />
      <button type="submit">S'inscrire</button>
    </form>
  </div>
</template>

<script>
import {mapActions, mapGetters, mapState} from "vuex";
import '../styles/components/form.scss'
import Password from 'vue-password-strength-meter'
import store from '../store/app'

export default {
  name: "Register",
  components: {Password},
  computed: {
    ...mapGetters({
      passwordStrength: "getPasswordStrength",
    })
  },
  data() {
    return {
      form: {
        username: "",
        email: "",
        password: ""
      }
    }
  },
  state: {
    isPasswordStrong: false
  },
  methods: {
    ...mapActions({
      register: 'auth/register'
    }),
    submit() {
      if (this.passwordStrength) {
        this.register(this.form).then((r) => {
          console.log('registered')
        }).catch((e) => {
          console.log('not registered')
          console.log(e)
        })
      }

    },
    showScore(score) {
      score > 2 ? store.commit('setPasswordStrength',true) : store.commit('setPasswordStrength',false)
    }
  }
}
</script>

<style scoped lang="scss">
.Password {
  max-width: inherit;
  width: 100%;
}
</style>
