<template>
  <div>
    <div class="form-container">
      <form class="webhook-form" @submit.prevent="submit">
        <div class="h3">Nouveau webhook</div>
        <div class="webhook-form-new" v-if="$route.name === 'webhooks.new'">
          <input type="url"
                 aria-label="Url du webhook"
                 placeholder="Url du webhook"
                 v-model="form.webhookUrl"
                 required>
        </div>
        <div class="webhook-form-edit" v-if="$route.name === 'webhooks.edit'">
          <input type="url"
                 aria-label="Url du webhook"
                 placeholder="Url du webhook"
                 v-model="form.webhookUrl"
                 required
                 disabled>
          <input type="text"
                 aria-label="Nom du webhook"
                 placeholder="Nom du webhook"
                 v-model="form.name"
                 required>
        </div>
        <button type="submit">Ajouter</button>
        <ul class="guda-errors" v-if="errors.length">
          <li class="guda-error" v-for="error in errors">{{ error }}</li>
        </ul>
      </form>
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";

export default {
  name: "WebhookForm",
  computed: {
    ...mapGetters({
      user: "auth/getUser",
    })
  },
  data() {
    return {
      form: {
        webhookUrl: "",
        name: "",
        userId: ""
      },
      errors: [],
    }
  },
  methods: {
    ...mapActions({
      setDiscordWebhook: 'discord/setDiscordWebhook'
    }),
    async submit() {
      this.errors =[]
      // Set user id
      this.form.userId = this.user.id

      await this.setDiscordWebhook(this.form).then(r => {
        switch (r.status) {
          case (200):
            if (r.data.message === 'created') {
              this.$router.push({
                name: 'webhooks',
                params: {
                  webhookStatus: 'created'
                }
              })
            }
            break
          case (201):
            this.errors.push('Le webhook existe déjà dans la base de données');
            break
          case (203):
            this.errors.push('Le webhook n\'existe pas ou l\'url ne correspond pas');
            break
          default:
            this.errors.push('La communication avec l\'api de discord s\'est mal passé');
            break;
        }
      })
    }
  }
}
</script>

<style scoped lang="scss">
  .webhook-form {
    .webhook-form-new, .webhook-form-edit {
      input {
        width: 100%;
      }
    }
  }
</style>
