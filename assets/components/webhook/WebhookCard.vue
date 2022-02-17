<template>
  <b-link class="webhook-card-link" :to="`webhooks/detail/${this.id}`">
    <b-col class="webhook-card">
      <p class="align-self-center">{{ name }}</p>
      <!--    <img :src="avatar()" :alt="name">-->
    </b-col>
  </b-link>
</template>

<script>
export default {
  name: "WebhookCard",
  props: {
    name: {required: true, type: String},
    avatarId: {required: true, type: String},
    channelId: {required: true, type: Number},
    guildId: {required: true, type: Number},
    id: {required: true, type: Number},
  },
  methods: {
    avatar() {
      return `https://cdn.discordapp.com/webhook/${this.channelId}/${this.avatarId}.jpg`
    },
  }
}
</script>

<style scoped lang="scss">
.webhook-card-link {
  text-decoration: none;
  padding: 0;
}

.webhook-card {
  padding: 0 10px;
  display: flex;
  min-height: 50px;
  box-shadow: 0 0 20px var(--guda-color);
  background-color: rgb(236, 138, 103);
  border-radius: 0.675rem;

  &:hover {
    background-color: var(--guda-color);
    cursor: pointer;
  }

  p {
    margin: 0;
    font-weight: bold;
  }
}
</style>
