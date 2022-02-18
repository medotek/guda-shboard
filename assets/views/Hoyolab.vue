<template>
  <div>
    <h1>Fonctionnalités hoyolab</h1>

    <div class="guda-success" v-if="postAddedSuccessfully">
      Ajout {{ isList ? 'de ' + responseCount + ' posts' : 'de l\'article' }} avec succès
    </div>
    <div class="hoyolab-manage">
      <ul class="guda-errors" v-if="errors.length">
        <li class="guda-error" v-for="error in errors">{{ error }}</li>
      </ul>
      <button class="button button-primary" @click="addPostsButton">{{
          modal ? clickedAddPostMessage : addPostMessage
        }}
      </button>
      <form id="hoyolab-post-new" @submit="submitAddHoyoPost" v-if="modal">
        <div class="hoyolab-post-choice">
          <div class="button button-secondary" v-if="isPost && isList || !isPost && !isList"
               @click="isPost = true">{{ isPostMessage }}
          </div>
          <div class="button button-secondary" v-if="isPost && isList || !isPost && !isList"
               @click="isList = true">{{ isListMessage }}
          </div>
        </div>
        <input type="url" name="url" class="" v-if="isPost || isList">
        <button class="button button-secondary" type="submit" v-if="isPost || isList">Ajouter</button>
      </form>
    </div>
  </div>
</template>

<script>
import {mapActions} from "vuex";

export default {
  name: "Hoyolab",
  mounted() {
    this.getHoyoStatsData.then
  },
  methods: {
    ...mapActions({
      setHoyolabPost: 'discord/setHoyolabPost',
      getHoyoStats: 'discord/getHoyoStats'
    }),
    async getHoyoStatsData() {
      await this.getHoyoStats().then((r) => {
        console.log(r)
      });
    },
    submitAddHoyoPost(e) {
      this.postAddedSuccessfully = false
      this.errors = []
      e.preventDefault()
      let formData = new FormData(e.target)
      let data = {
        url: formData.get('url'),
        isList: this.isList
      }
      return this.setHoyolabPost(data).then(res => {
        if (res.status === 200) {
          this.postAddedSuccessfully = true
          this.responseCount = res.data.count
        } else {
          this.errors.push(res.error)
        }
      }).catch(e => {
        console.log(e)
        this.errors.push('Une erreur est survenue')
      })
    },
    addPostsButton() {
      this.modal = !this.modal
      if (!this.modal) {
        this.isPost = false
        this.isList = false
      }
    }
  },
  data() {
    return {
      postAddedSuccessfully: false,
      responseCount: 0,
      errors: [],
      modal: false,
      isPostMessage: 'Lien de votre article',
      isPost: false,
      isListMessage: 'Lien de votre compte hoyolab',
      isList: false,
      addPostMessage: "Ajouter des articles hoyolab",
      clickedAddPostMessage: "Annuler",
    }
  }
}
</script>

<style scoped lang="scss">
form {
  padding: 0;
}
.hoyolab-post-choice {
  display: flex;

  div {
    text-align: center;
  }
}
</style>
