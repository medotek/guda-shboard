<template>
  <div>
    <div class="guda-success" v-if="this.$route.params.webhookStatus === 'created'">Nouveau webhook ajouté</div>
    <div class="webhook-header">
      <button onclick="window.history.back()">
        <font-awesome-icon icon="fa-solid fa-arrow-left"/>
      </button>
      <h1>Gestionnaire de WebHooks Discord</h1>
    </div>
    <div class="webhook-wrapper" v-if="$route.name !== 'webhooks.new'">
      <div class="webhook-nav">
        <b-link to="/webhooks/new">Add a webhook</b-link>
      </div>

      <div class="webhook-list">
        <b-row v-if="webhooks" v-for="webhook in webhooks" :key="webhook.id">
          <webhook-card :name="webhook.name"
                        :avatar-id="webhook.avatarId"
                        :channel-id="webhook.channelId"
                        :guild-id="webhook.guildId"
                        :token="webhook.token"
                        :id="webhook.id"
          ></webhook-card>
        </b-row>
      </div>
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
    await this.getWebhooksList(this.user.id).then(r => {
      if (r.status === 200) {
        this.webhooks = JSON.parse(r.data.webhooks)
        console.log(JSON.parse(r.data.webhooks))
      }
    })
  },
  data() {
    return {
      webhooks: [
        {
          avatarId: "",
          channelId: 0,
          guildId: 0,
          id: 0,
          name: "",
          token: "",
          webhookId: 0
        }
      ]
    }
  },
  methods: {
    ...mapActions({
      getWebhooksList: 'discord/getWebhooksList'
    })
  }
}
</script>

<style scoped lang="scss">
.webhook-header {
  display: flex;
  margin: 3rem 0;
}
</style>
