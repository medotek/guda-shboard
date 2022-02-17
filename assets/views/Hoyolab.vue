<template>
  <div>
    <h1>Fonctionnalités hoyolab</h1>

    <div class="guda-success" v-if="postAddedSuccessfully">
      Ajout {{isList ? 'des articles' : 'de l\'article'}} avec succès
    </div>
    <div class="hoyolab-manage">
      <ul class="guda-errors" v-if="errors.length">
        <li class="guda-error" v-for="error in errors">{{ error }}</li>
      </ul>
      <button class="button button-primary" @click="addPostsButton">{{ modal ? clickedAddPostMessage : addPostMessage }}</button>
      <form id="hoyolab-post-new" @submit="submitAddHoyoPost" v-if="modal">
        <span class="button button-secondary" v-if="isPost && isList || !isPost && !isList"
                @click="isPost = true">{{ isPostMessage }}</span>
        <span class="button button-secondary" v-if="isPost && isList || !isPost && !isList"
                @click="isList = true">{{ isListMessage }}</span>
        <input type="url" name="url" class="" v-if="isPost || isList">
        <button class="button button-secondary" type="submit" v-if="isPost || isList" >Ajouter</button>
      </form>
    </div>
  </div>
</template>

<script>
import {mapActions} from "vuex";

export default {
  name: "Hoyolab",
  methods: {
    ...mapActions({
      setHoyolabPost: 'discord/setHoyolabPost'
    }),
    submitAddHoyoPost(e) {
      this.postAddedSuccessfully = false
      this.errors = []
      e.preventDefault()
      let formData = new FormData(e.target)
      return this.setHoyolabPost(formData.get('url'), this.isList).then(res => {
        if (res.status === 200) {
          this.postAddedSuccessfully = true
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

<style scoped>

</style>
