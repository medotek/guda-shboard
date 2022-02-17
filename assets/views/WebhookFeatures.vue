<template>
  <div>
    <div class="guda-success" v-if="this.$route.params.webhookStatus === 'created'">Nouveau webhook ajouté</div>
    <div class="webhook-header">
      <div class="webhook-header-title">
        <button onclick="window.history.back()">
          <font-awesome-icon icon="fa-solid fa-arrow-left"/>
        </button>
        <h1>{{ pageName() }}</h1>
      </div>
      <b-link to="/webhooks/new" class="align-self-center" v-if="$route.name === 'webhooks'">Add a webhook</b-link>
      <b-link :to="`/discord/message/webhook/${this.$route.params.id}/new`" class="align-self-center" v-if="$route.name === 'webhooks.detail'">New message</b-link>
    </div>
    <div class="webhook-wrapper" v-if="$route.name === 'webhooks'">
      <h2>Vos webhooks</h2>
      <div class="webhook-nav">
        <p>TODO : filter</p>
      </div>
      <div class="webhook-list">
        <b-row class="container" v-if="displayWebhooks" v-for="webhook in webhooks" :key="webhook.id">
          <webhook-card :name="webhook.name"
                        :avatar-id="webhook.avatarId"
                        :channel-id="webhook.channelId"
                        :guild-id="webhook.guildId"
                        :id="webhook.id"
          ></webhook-card>
        </b-row>
      </div>
      <div class="guda-errors" v-if="errors">No webhook registered, please add one</div>
    </div>
    <router-view></router-view>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import WebhookCard from "../components/webhook/WebhookCard";

export default {
  name: "WebhookFeatures",
  computed: {
    ...mapGetters({
      user: "auth/getUser"
    })
  },
  components: {
    WebhookCard
  },
  async beforeMount() {
    await this.getWebhooksList(this.page).then(r => {
      if (r.status === 200) {
        this.webhooks = JSON.parse(r.data.webhooks)
        this.displayWebhooks = true
      }
    }).catch(err => {
      console.log(err)
      this.errors = false
    })
  },
  data() {
    return {
      page: 1,
      webhooks: [
        {
          avatarId: "",
          channelId: 0,
          guildId: 0,
          id: 0,
          name: "",
          webhookId: 0
        }
      ],
      displayWebhooks: false,
      errors: false
    }
  },
  methods: {
    ...mapActions({
      getWebhooksList: 'discord/getWebhooksList'
    }),
    pageName() {
      switch (this.$route.name) {
        case ('webhooks'):
          return 'Gestionnaire de WebHooks Discord'
        case ('webhooks.new'):
          return 'Ajouter un nouveau webhook'
        case ('webhooks.edit'):
          return 'Editer un webhook'
        case ('webhooks.detail'):
          return 'Detail du webhook'
      }
    }
  }
}
</script>

<style scoped lang="scss">
.webhook-header {
  justify-content: space-between;
  display: flex;

  a, button {
    margin-left: 0.675rem;
    background-color: var(--guda-color);
    color: var(--text-primary-color);
    text-decoration: none;
    padding: 5px;
    border: none;
    border-radius: 0.475rem;
    font-weight: bold;
  }

  .webhook-header-title {
    display: flex;

    button {
      margin: 3rem 1rem 3rem 0;
      min-width: 40px;
    }
  }
}

.webhook-wrapper {
  background-color: var(--background-color-primary);
  padding: 0.875rem;
  border-radius: 0.675rem;
  min-height: 400px;
  border: 1px solid var(--background-color-secondary);
}
</style>
