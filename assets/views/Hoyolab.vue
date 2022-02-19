<template>
  <div>
    <div class="guda-header">
      <div class="guda-header-title">
        <button onclick="window.history.back()">
          <font-awesome-icon icon="fa-solid fa-arrow-left"/>
        </button>
        <h1>{{ pageName() }}</h1>
      </div>
    </div>

    <div class="hoyolab-manage">
      <div class="guda-success" v-if="postAddedSuccessfully">
        Ajout {{ isList ? 'de ' + responseCount + ' posts' : 'de l\'article' }} avec succès
      </div>
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

    <div class="wrapper" v-if="$route.name === 'hoyolab'">
      <h3>Liste des profils hoyolab</h3>
      <b-row class="profile-wrapper" v-if="hoyoUsers" v-for="hoyoUser in hoyoUsers">
        <b-col sm="12" md="4" class="profile-col" @click="goToProfile(hoyoUser.uid)">
          <div class="profile-card">
            <div class="profile-image">
              <img class="avatar" :src="hoyoUser.avatarUrl" :alt="hoyoUser.nickname">
              <img class="pendant" :src="hoyoUser.pendant" :alt="hoyoUser.nickname">
            </div>
            <span>UID : {{ hoyoUser.uid }}</span>
            <span>{{ hoyoUser.nickname }}</span>
          </div>
        </b-col>
      </b-row>
    </div>

    <router-view></router-view>
  </div>
</template>

<script>
import {mapActions} from "vuex";

export default {
  name: "Hoyolab",
  created() {
    this.hoyoUsersInit()
  },
  methods: {
    ...mapActions({
      setHoyolabPost: 'discord/setHoyolabPost',
      getHoyoUsers: 'discord/getHoyoUsers'
    }),
    goToProfile(uid) {
      this.$router.push(`/hoyolab/user/${uid}`)
      console.log(uid)
    },
    async hoyoUsersInit() {
      this.hoyoUsers = await this.getHoyoUsers()
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
    },
    pageName() {
      switch (this.$route.name) {
        case ('hoyolab'):
          return 'Fonctionnalités hoyolab'
        case ('hoyolab.user'):
          return 'Hoyolab - Profile'
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
      hoyoUsers: {}
    }
  }
}
</script>

<style scoped lang="scss">

.profile-wrapper {
  .profile-col {
    padding: 1rem;

    .profile-card {
      display: flex;
      flex-direction: column;
      background-color: var(--guda-light-blue);
      border-radius: 0.675rem;

      &:hover {
        background-color: var(--guda-dark-blue);
        cursor: pointer;
      }

      span {
        text-align: center;
        color: black;
        font-weight: bold;
      }

      .profile-image {
        position: relative;
        transform: translateX(-50%);
        left: 50%;
        width: 250px;
        height: 250px;

        img {
          position: absolute;
        }

        .avatar {
          left: 50%;
          top: 50%;
          transform: translate(-50%, -50%);
        }

        .pendant {
          width: 250px;
          height: 250px;
        }
      }
    }
  }
}

form {
  padding: 0;
}

.wrapper {
  //min-height: inherit;
}

.hoyolab-manage {
  margin: 0.675rem 0;
}

.hoyolab-post-choice {
  display: flex;

  div {
    text-align: center;
  }
}
</style>
