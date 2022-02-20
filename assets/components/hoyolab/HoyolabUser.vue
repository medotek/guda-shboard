<template>
  <div>
    <div class="wrapper">
      <b-row>
        <b-col sm="12" md="6" lg="3">
          <div class="profile-card">
            <div class="h3 text-center">Profil</div>
            <div class="profile-image">
              <img class="avatar" :src="hoyoUser.avatarUrl" :alt="hoyoUser.nickname + ' image'">
              <img class="pendant" :src="hoyoUser.pendant" :alt="hoyoUser.nickname + ' pendant'">
            </div>
            <span>UID : {{ hoyoUser.uid }}</span>
            <span>{{ hoyoUser.nickname }}</span>
          </div>
        </b-col>
        <b-col sm="12" md="6" lg="9">
          <div class="h3">Overall stats</div>
          <ul class="stat-list">
            <li v-for="(value, index) in hoyoStats">
              {{ index }} <span>{{ value }}</span>
            </li>
          </ul>
          <div class="hoyolab-user-management guda-border-highlight">
            <div class="h3">Gestion du compte hoyolab</div>
            <p>Vous allez pouvoir gérer depuis ce dashboard l'envoi de notification (sur les stats) sur un channel discord, et cela
              grace à un webhook</p>

            <div v-if="success" class="guda-success">Le webhook a été ajouté ou modifié avec succès</div>
            <ul class="guda-errors" v-if="errors.length">
              <li class="guda-error" v-for="error in errors">{{ error }}</li>
            </ul>

            <div v-if="hoyoUser.webhookUrl" class="existing-webhook guda-success">Un webhook est déjà existant pour ce compte hoyolab</div>
            <div class="warning">Ce webhook ne comprend pas la modification/suppression des messages depuis la dashboard</div>
            <button class="button button-primary" v-if="!displayWF" @click="displayWF = !displayWF">{{ form.webhookUrl ? 'Modifier' : 'Ajouter'}} le lien du webhook</button>
            <button class="button button-primary" v-if="displayWF" @click="displayWF = false">Annuler</button>

            <form v-if="displayWF" @submit="webhookUrlForm">
              <input v-model="form.webhookUrl" class="guda-input" type="url" placeholder="https://discordapp.com...." name="webhook-url">
              <button class="button button-secondary">Enregistrer</button>
            </form>
          </div>
        </b-col>
      </b-row>
    </div>

    <div class="wrapper">
      <div class="h3">Account posts</div>
      <b-row>
        <b-col sm="6" md="4" lg="3" v-for="(hoyoPost, index) in hoyoPostsInit" :key="index">
          <b-card
              :title="hoyoPost.subject"
              :img-src="hoyoPost.image"
              img-top
              tag="article"
              class="guda-post-card"
              lazy="load"
          >
            <b-card-text>
              <div class="card-content">
                <span class="title">Last Time Reply</span>
                <span class="guda-highlight">{{ hoyoPost.lastReplyTime | formatDate }}</span>
                <span class="title">Post Creation Date</span>
                <span class="guda-highlight">{{ hoyoPost.postCreationDate | formatDate }}</span>
              </div>
            </b-card-text>
          </b-card>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<script>
import {mapActions} from "vuex";

export default {
  name: "HoyolabUserStats",
  beforeMount() {
    this.hoyoInit()
  },
  methods: {
    ...mapActions({
      getHoyoStats: 'discord/getHoyoStats',
      getHoyoPostsList: 'discord/getHoyoUserPostList',
      getHoyoUser: 'discord/getHoyoUser',
      setHoyolabWebhook: 'discord/setHoyolabWebhook'
    }),
    async hoyoInit() {
      this.hoyoStats = await this.getHoyoStats(this.$route.params.uid)
      this.hoyoUser = await this.getHoyoUser(this.$route.params.uid)
      this.hoyoPostsInit = await this.getHoyoPostsList({uid: this.$route.params.uid, page: 1})
    },
    async webhookUrlForm(e) {
      this.success = false;
      this.errors = [];
      e.preventDefault()
      await this.setHoyolabWebhook(this.form).then(res => {
        this.success = true
        console.log(res)
      }).catch(err => {
        console.log(err)
        this.webhookUrl = ""
        this.errors.push("Le webhook n'a pas pu être ajouté ou modifié")
      })
    }
  },
  data() {
    return {
      errors: [],
      success: false,
      hoyoStats: {},
      hoyoPostsInit: {},
      hoyoUser: {
        nickname: "",
        pendant: "",
        avatarUrl: "",
        uid: ""
      },
      form: {
        uid: this.$route.params.uid,
        webhookUrl: ""
      },
      displayWF: false
    }
  }
}
</script>

<style scoped lang="scss">
.guda-post-card {
  background-color: var(--guda-light-blue);
  margin-bottom: 1rem;

  .card-body {
    .card-title {
      font-size: 1rem;
      color: black;
      font-weight: bold;
    }

    .card-text {
      .card-content {
        color: black;
        display: flex;
        flex-direction: column;
      }
    }
  }
}

.hoyolab-user-management {
  border-radius: 0.675rem;
  padding: 1rem;
}

.stat-list {
  list-style: none;
  padding: 0;

  li {
    padding: 5px;
    width: fit-content;
    background-color: var(--guda-dark-blue);
    color: white;
    border-radius: 0.5rem;
    display: inline;
    margin-right: 5px;

    span {
      font-weight: bold;
    }
  }
}


form {
  padding: 0;
}


.warning {
  color: var(--guda-warning-color);
  font-weight: bold;
}
</style>
