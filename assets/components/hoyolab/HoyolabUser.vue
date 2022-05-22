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
            <b-link class="profile-link" :href="`https://www.hoyolab.com/accountCenter/postList?id=${hoyoUser.uid}`"
                    target="_blank"></b-link>
          </div>
        </b-col>
        <b-col sm="12" md="6" lg="9">
          <div class="h3">Overall stats</div>
          <ul class="stat-list">
            <li v-for="(value, index) in hoyoStats">
              {{ index }} <span>{{ value }}</span>
            </li>
          </ul>

          <div class="guda-border-highlight guda-highlight missing-posts" v-if="missingPosts">
            {{ missingPosts }}
          </div>
          <div class="hoyolab-user-management guda-border-highlight">
            <div class="h3">Gestion du compte hoyolab</div>
            <p>Vous allez pouvoir gérer depuis ce dashboard l'envoi de notification (sur les stats) sur un channel
              discord, et cela
              grace à un webhook</p>

            <div v-if="success" class="guda-success">Le webhook a été ajouté ou modifié avec succès</div>
            <ul class="guda-errors" v-if="errors.length">
              <li class="guda-error" v-for="error in errors">{{ error }}</li>
            </ul>

            <div v-if="hoyoUser.webhookUrl" class="existing-webhook guda-success">Un webhook est déjà existant pour ce
              compte hoyolab
            </div>
            <div class="warning">Ce webhook ne comprend pas la modification/suppression des messages depuis la
              dashboard
            </div>
            <button class="button button-primary" v-if="!displayWF" @click="displayWF = !displayWF">
              {{ form.webhookUrl ? 'Modifier' : 'Ajouter' }} le lien du webhook
            </button>
            <button class="button button-primary" v-if="displayWF" @click="displayWF = false">Annuler</button>

            <form v-if="displayWF" @submit="webhookUrlForm">
              <input v-model="form.webhookUrl" class="guda-input" type="url" placeholder="https://discordapp.com...."
                     name="webhook-url">
              <button class="button button-secondary">Enregistrer</button>
            </form>
          </div>
        </b-col>
      </b-row>
    </div>

    <!-- User stats -->
    <Stats v-if="fetchNext && statsReady && hoyoUserStats" :data-stat="hoyoUserStats"></Stats>

    <div class="wrapper">
      <div class="h3">Account posts</div>
      <b-row>
        <b-col sm="6" md="4" lg="3" v-for="(hoyoPost, index) in hoyoPostsInit" :key="index">
          <b-card
              :img-src="hoyoPost.image"
              img-top
              tag="article"
              class="guda-post-card"
              lazy="load"
          >
            <b-card-title>
              <b-link :href="`https://hoyolab.com/article/${hoyoPost.postId}`" target="_blank">
                {{ hoyoPost.subject }}
              </b-link>
            </b-card-title>
            <b-card-text>
              <div class="card-content">
                <!--                <span class="title">Last Time Reply</span>-->
                <!--                <span class="guda-highlight">{{ hoyoPost.lastReplyTime | formatDate }}</span>-->
                <span class="title">Création du post hoyolab</span>
                <span class="guda-highlight">{{ getCreationDate(hoyoPost) }}</span>
                <span class="title">Dernière notification</span>
                <span class="guda-highlight">{{ getNotificationDate(hoyoPost) }}</span>
              </div>
            </b-card-text>
          </b-card>
        </b-col>
      </b-row>
      <div class="loading" v-if="gudaLoading">Loading</div>
    </div>
  </div>
</template>

<script>
import {mapActions} from "vuex";
import Stats from "../Stats";

export default {
  name: "HoyolabUserStats",
  components: {
    Stats
  },
  beforeMount() {
    this.hoyoInit()
  },
  mounted() {
    this.getNextPosts();
  },
  methods: {
    ...mapActions({
      getHoyoStats: 'discord/getHoyoStats',
      getHoyoPostsList: 'discord/getHoyoUserPostList',
      getHoyoUser: 'discord/getHoyoUser',
      setHoyolabWebhook: 'discord/setHoyolabWebhook',
      getHoyoUserAnalytics: 'discord/getHoyoUserAnalytics'
    }),
    async hoyoInit() {
      this.hoyoStats = await this.getHoyoStats(this.$route.params.uid)
      this.hoyoUser = await this.getHoyoUser(this.$route.params.uid)
      this.hoyoPostsInit = await this.getHoyoPostsList({uid: this.$route.params.uid, page: 1})

      // Load stats
      let analyticsResponse = await this.getHoyoUserAnalytics({uid: this.$route.params.uid, period: 'day'});
      console.log(analyticsResponse.success)
      if (analyticsResponse.success !== undefined) {
        let labels = [];
        let datasets = [];
        if (Object.keys(analyticsResponse.success).length) {
          this.statsReady = true;
          // prepare data for stats
          for (const [key, stat] of Object.entries(analyticsResponse.success)) {
            // init first dataSet
            if (Object.keys(stat).length) {
              if (!datasets.length) {
                let customColors = [
                  '#797ff8',
                  '#79f8bf',
                  '#ff7676',
                  '#f8d079',
                  '#f079f8',
                ]
                let i = 0;

                for (const [statKey, statValue] of Object.entries(stat)) {
                  let datasetSample = {
                    label: statKey,
                    backgroundColor: customColors[i],
                    borderColor: customColors[i],
                    data: []
                  }

                  datasets.push(datasetSample)
                  i++
                }
              }
            }

            labels.push(key.split(' ')[1])
          }

          for (const [key, stat] of Object.entries(analyticsResponse.success)) {
            if (Object.keys(stat).length) {
              // construct data for datasets
              for (const [statKey, statValue] of Object.entries(stat)) {
                // find datasetSample and push data
                let datasetKey = this.getObjKey(datasets, statKey)
                if (datasetKey) {
                  datasets[datasetKey].data.push(statValue)
                }
              }
            } else {
              for (const [datasetKey, dataset] of Object.entries(datasets)) {
                dataset.data.push(0)
              }
            }
          }
        }

        // TODO : instead of unsetting values - fill the empty ones with the value setup before, if no value exists before, unset
        // unset value and label if empty value
        let labelKeysToRemove = []
        let iteration = 0;
        for (const [datasetKey, dataset] of Object.entries(datasets)) {
          if (dataset.data.length) {
            let previousVal = 0;
            dataset.data.forEach((val, k) => {
              if (!val) {
                if (!previousVal) {
                  if (!iteration) {
                    labelKeysToRemove.push(k)
                  }
                  // remove key for datasets
                  datasets[datasetKey].data.splice(k, 1)
                } else {
                  datasets[datasetKey].data[k] = previousVal
                }

              } else {
                previousVal = val
              }
            })
            iteration++;
          }
        }
        console.log(labelKeysToRemove)
        // unset label key
        if (labelKeysToRemove.length) {
          labelKeysToRemove.forEach(labelkey => {
            // same for labels
            labels.splice(labelkey, 1)
          })
        }

        if (datasets.length) {
          this.hoyoUserStats = {
            labels,
            datasets
          }
        }
      }
      // END - TODO

      this.fetchNext = true
      let hoyoUserData = await fetch('https://api.guda.club:3001/https://bbs-api-os.mihoyo.com/community/user/wapi/getUserFullInfo?uid=' + this.$route.params.uid, {method: 'GET'}).then(r => {
        return r.json()
      })

      let result = hoyoUserData.data.user_info.achieve.post_num - this.hoyoStats.posts
      if (result > 0) {
        this.missingPosts = `Il manque ${result} posts hoyolab sur le site. Regardez les plus vieux en priorité ...`
      }
    },
    async webhookUrlForm(e) {
      this.success = false;
      this.errors = [];
      e.preventDefault()
      await this.setHoyolabWebhook(this.form).then(res => {
        this.success = true
      }).catch(err => {
        console.log(err)
        this.webhookUrl = ""
        this.errors.push("Le webhook n'a pas pu être ajouté ou modifié")
      })
    },
    getNotificationDate(hoyoPost) {
      if (hoyoPost.hoyolabPostDiscordNotification) {
        let date = this.$options.filters.formatDate(hoyoPost.hoyolabPostDiscordNotification.processDate)
        return date.replace(':', 'h')
      } else {
        return 'Aucune notification'
      }
    },
    getCreationDate(hoyoPost) {
      let date = this.$options.filters.formatDate(hoyoPost.postCreationDate)
      return date.replace(':', 'h')
    },
    getObjKey(obj, value) {
      for (const [objKey, objValue] of Object.entries(obj)) {
        if (Object.keys(objValue).length) {
          let target = Object.keys(objValue).find(key => objValue[key] === value)
          if (target) {
            return objKey
          }
        }
      }

      return false;
    },
    getNextPosts() {
      window.onscroll = () => {
        if (this.fetchNext) {
          if (!this.noMoreData) {
            let bottomOfWindow = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight;
            if (bottomOfWindow) {
              if (!this.gudaLoading) {
                this.gudaLoading = true
                this.getHoyoPostsList({uid: this.$route.params.uid, page: this.page + 1}).then(response => {
                  this.gudaLoading = false
                  this.page = this.page + 1
                  response.forEach(item => {
                    this.hoyoPostsInit.push(item);
                  })

                  if (response.length < 20) {
                    this.noMoreData = true
                  }
                }).catch(err => {
                  console.log(err)
                  this.gudaLoading = false
                });
              }
            }
          }
        }
      }
    }
  },
  data() {
    return {
      missingPosts: null,
      fetchNext: false,
      noMoreData: false,
      gudaLoading: false,
      statsReady: false,
      page: 1,
      errors: [],
      success: false,
      asyncFinished: false,
      hoyoStats: {},
      hoyoUserStats: {},
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
.profile-card {
  position: relative;

  .profile-link {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
  }

}

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

.missing-posts {
  margin-bottom: 0.675rem;
}

.hoyolab-user-management, .missing-posts {
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
