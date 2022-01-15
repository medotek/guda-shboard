<template>
  <div class="form-container">
    <form @submit.prevent="submit">
      <h1>Inscription</h1>
      <input type="text"
             aria-label="Username"
             placeholder="Nom d'utilisateur"
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
                v-on:input="showScoreBar"
      />
      <input type="password" name="repeatPassword" id="repeatPassword" v-model="form.repeatPassword"
             placeholder="Confirmer le mot de passe" required v-on:input="matchPassword">
      <div class="password-verification">Le mot de passe ne correspond pas</div>
      <button type="submit">S'inscrire</button>
      <ul class="guda-errors" v-if="errors.length">
        <li class="guda-error" v-for="error in errors">{{ error }}</li>
      </ul>
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
  data() {
    return {
      form: {
        username: "",
        email: "",
        password: "",
        repeatPassword: ""
      },
      errors: [],
      matchingPassword: false,
      passwordStrength: false
    }
  },
  state: {
    isPasswordStrong: false,
    passwordInput: false
  },
  methods: {
    ...mapActions({
      register: 'auth/register'
    }),
    submit() {
      this.errors = [];

      if (this.passwordStrength && this.matchingPassword) {
        this.register(this.form).then((res) => {
          if (res.status === 201) {
            console.log('registered')
            this.$router.push({
              name: 'login',
              params: {
                registerStatus: 'created'
              }
            });
          }

          let errors = res.data.error
          if (errors) {
            this.errors = [];
            if (errors['message']) {
              this.errors.push(errors['message'])
            }
          }
        }).catch((e) => {
          console.log(e)
          this.errors = [];
          this.errors.push('Une erreur est survenue, veuillez ré-essayer.');
        })
      } else {
        this.errors.push('Ton mot de passe n\'est pas assez puissant');
      }
    },
    showScoreBar(value) {
      let passwordMeterBar = document.querySelector('.Password__strength-meter')
      value ? passwordMeterBar.style.display = 'block' : passwordMeterBar.style.display = 'none'
    },
    showScore(score) {
      score > 2 ? this.passwordStrength = true : this.passwordStrength = false
    },
    matchPassword() {
      let passwordsAreMatching = this.form.password === this.form.repeatPassword;
      if (this.form.password) {
        let repeatPasswordValidator = document.querySelector('.password-verification')
        if (passwordsAreMatching) {
          repeatPasswordValidator.style.display = 'none'
          this.matchingPassword = true
        } else {
          repeatPasswordValidator.style.display = 'block'
          this.matchingPassword = false
        }
      } else {
        this.append('')
      }
    },
  }
}
</script>

<style scoped lang="scss">
.Password {
  max-width: inherit;
  width: 100%;
}

.password-verification {
  display: none;
  color: var(--guda-warning-color);
  margin-bottom: 20px;
}
</style>
