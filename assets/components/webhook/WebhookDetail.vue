<template>
  <div>
    <div class="webhook-detail-header">
      <div class="webhook-detail-header-title">
        <div class="h3">Info span</div>
        <button class="button button-primary">Modifier</button>
      </div>

      <div class="webhook-detail-info">
        <p>Nom : <span class="guda-highlight">{{ webhook.name }}</span></p>
        <p>webhookId : <span class="guda-highlight">{{ webhook.webhookId }}</span></p>
        <p>ChannelId : <span class="guda-highlight">{{ webhook.channelId }}</span></p>
        <p>GuildId : <span class="guda-highlight">{{ webhook.guildId }}</span></p>
        <p>AvatarId : <span class="guda-highlight">{{ webhook.avatarId }}</span></p>
        <p>Token : <span class="guda-highlight">encoded</span></p>
      </div>
    </div>
    <div class="webhook-owner guda-border-highlight" v-if="">
      <div class="h3">Gestion du webhook</div>
      <p>Une fonctionnalité qui arrivera prochainement,<br>
        Qui vous permettra d'ajouter des personnes au webhook pour qu'elles puissent <span class="guda-highlight">envoyer/supprimer/modifier</span> des messages</p>
    </div>
    <div class="webhook-messages">
      <div class="h3">Messages du Webhook</div>
      <div class="guda-errors">
        No message sent
      </div>
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import store from "../../store/app";

export default {
  name: "WebhookDetail",
  // computed: {
  //   ...mapGetters({
  //     user: "auth/getUser"
  //   })
  // },
  async created() {
    store.commit('setLoading', true)
    /** Webhook detail **/
    await this.getWebhookDetail(this.$route.params.id).then(r => {
      if (r.status === 200) {
        this.webhook = JSON.parse(r.data)
      }
      // Unset loading after a complete fetch of data
      store.commit('setLoading', false)
    }).catch(err => {
      console.log(err)
      /** Redirect on error */
      this.$router.push({
        name: 'not-found'
      });
    })

    /** Webhook owner zone **/

  },
  data() {
    return {
      webhook: {}
    }
  },
  methods: {
    ...mapActions({
      getWebhookDetail: 'discord/getWebhookDetail'
    })
  },
}
</script>

<style scoped lang="scss">
.webhook-detail-header, .webhook-messages, .webhook-owner {
  background-color: var(--background-color-primary);
  padding: 0.875rem;
  border-radius: 0.675rem;
  border: 1px solid var(--background-color-secondary);
  margin: 15px 0;
}

.webhook-messages {
  min-height: 400px;
}

.webhook-owner, .webhook-messages {
  .h3 {
    margin-bottom: 0.675rem;
  }
}

.webhook-detail-header {
  .webhook-detail-header-title {
    display: flex;

    button {
      width: fit-content;
      margin-left: 0.675rem;
      max-height: 35px;
    }
  }

  .webhook-detail-info {
    margin-left: 0.375rem;
    border-left: 1px solid var(--text-primary-color);
    padding-left: 0.675rem;
    padding-bottom: 0.675rem;

    p {
      margin-bottom: 0;
    }
  }
}
</style>
