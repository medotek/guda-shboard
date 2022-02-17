<template>
  <div>
    <div class="message-header">
      <div class="message-header-title">
        <button @click="leavePage">
          <font-awesome-icon icon="fa-solid fa-arrow-left"/>
        </button>
        <h1>Créer un message discord</h1>
      </div>
    </div>
    <div class="message-wrapper">
      <b-row>
        <b-col class="message-wrapper-item">
          <form @submit="">
            <textarea name="content" id="content" placeholder="Message" maxlength="2000"></textarea>
            <!-- Embed  -->
            <form class="hoyolab-embed" v-if="modal">
              <input type="url" id="hoyolab-url" placeholder="Url de l'article hoyolab">
              <button class="hoyolab-button-embed button button-secondary" type="button" @click="hoyolabArticle">
                <font-awesome-icon icon="fa-solid fa-plus-square"/>
              </button>
            </form>
            <discord-embed class="discord-embed-form" v-if="modal" v-model="embedData"></discord-embed>
            <button class="add-embed button button-secondary" type="button" @click="modal = !modal">
              <font-awesome-icon icon="fa-solid fa-plus-square"/>
              Ajouter un embed
            </button>
            <button class="button button-primary" type="button" @click="getEmbedData()">Envoyer le message</button>
          </form>
        </b-col>
        <b-col class="message-wrapper-item">
          <div class="message-preview">
            <div class="discord-margin">
              <div class="discord-fake-image"></div>
            </div>
            <div class="discord-content">
              <div class="discord-message-header">
                  <span class="discord-username">{{ webhook.name }}
                    <span class="discord-date">Aujourd'hui {{ getDate() }}</span>
                  </span>
              </div>
              <div id="discord-message-content" class="discord-message-content">
              </div>
              <!--     PREVIEW EMBED       -->
              <div class="discord-embed" v-if="modal">
                <div id="embed-color" class="discord-embed-left-border"
                     :style="`background-color:${this.embedData.color}`"></div>
                <div class="discord-embed-container">
                  <div class="discord-embed-content">
                    <div>
                      <div class="discord-embed-author"
                           v-if="this.embedData.authorName && this.embedData.authorUrl && this.embedData.authorAvatarUrl">
                        <img id="embed-author-imageUrl" class="discord-embed-author-icon"
                             :src="this.embedData.authorAvatarUrl" alt="" style="">
                        <a id="embed-author-url-and-name" :href="this.embedData.authorUrl" target="_blank"
                           rel="noopener noreferrer">
                          {{ this.embedData.authorName }}
                        </a>
                      </div>
                      <div id="embed-title" class="discord-embed-title">
                        <a id="embed-url" href="#" target="_blank" rel="noopener noreferrer"
                           style="color: white;">{{ this.embedData.title }}</a>
                      </div>
                      <div id="embed-description" class="discord-embed-description" @click="livePreview(true)">
                      </div>
                      <div class="discord-embed-fields">
                        <!--[-->
                        <div class="discord-embed-field">
                          <div class="discord-embed-field-title">Regular field title</div>
                          <!--[--> Some value here
                          <!--]-->
                        </div>
                        <div class="discord-embed-field">
                          <div class="discord-embed-field-title">​</div>
                          <!--[--> ​
                          <!--]-->
                        </div>
                        <div class="discord-embed-field discord-embed-field-inline">
                          <div class="discord-embed-field-title">Inline field title</div>
                          <!--[--> Some value here
                          <!--]-->
                        </div>
                        <div class="discord-embed-field discord-embed-field-inline">
                          <div class="discord-embed-field-title">Inline field title</div>
                          <!--[--> Some value here
                          <!--]-->
                        </div>
                        <div class="discord-embed-field discord-embed-field-inline">
                          <div class="discord-embed-field-title">Inline field title</div>
                          Some value here
                        </div>
                      </div>
                      <img id="embed-image" class="discord-embed-image" :src="this.embedData.contentImageUrl" alt="">
                    </div>
                    <img id="embed-thumbnail" class="discord-embed-thumbnail" :src="this.embedData.contentThumbnail"
                         alt="">
                  </div>
                  <div id="embed-footer" class="discord-embed-footer">
                    <img class="discord-embed-footer-icon" src="https://i.imgur.com/AfFp7pu.png" alt="">
                    <span>
                            <span>Some footer text here</span>
                            <span class="discord-embed-footer-separator"> • </span>
                            <span class="discord-embed-footer-date">15/02/2022</span>
                        </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </b-col>
      </b-row>

    </div>
  </div>
</template>

<script>
import store from "../../../store/app";
import {mapActions} from "vuex";
import {marked} from 'marked';
import DiscordEmbed from "../DiscordEmbed";

export default {
  name: "DiscordMessage",
  async created() {
    store.commit('setLoading', true)
    /** Webhook message **/
    if (this.$route.name.match('discord.webhook.message')) {
      await this.getWebhookDetail(this.$route.params.id).then(r => {
        this.routeId = this.$route.params.id
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
    }
  },
  components: {
    DiscordEmbed
  },
  mounted() {
    /**
     * Live preview
     */
    this.livePreview()
  },
  data() {
    return {
      errors: [],
      webhook: {},
      modal: false,
      specialFeature: false,
      embedData: {
        title: "",
        color: "#0099ff",
        authorName: "",
        authorUrl: "",
        authorAvatarUrl: "",
        contentDescription: "",
        contentUrl: "",
        contentImageUrl: "",
        contentThumbnail: "",
        contentFooter: false
      }
    }
  },
  methods: {
    ...mapActions({
      getWebhookDetail: 'discord/getWebhookDetail'
    }),
    /** get Date **/
    getDate() {
      let date = new Date;
      let minutes = date.getMinutes();
      let hour = date.getHours();

      return `${hour}:${minutes}`
    },
    /** Live preview **/
    livePreview(description) {
      document.querySelector('form textarea').addEventListener('keyup', this.debounce(function () {
        let content = this.value
        document.getElementById("discord-message-content").innerHTML = marked(content)
      }, 200))
      if (this.embedData.contentUrl) {
        document.getElementById('embed-url').style.color = "#0099ff"
      }
      if (description) {
        document.querySelector('.discord-embed-form textarea').addEventListener('keyup', this.debounce(function () {
          console.log('tets')
          console.log(this.embedData.contentDescription)
          document.getElementById("embed-description").innerHTML = marked(this.embedData.contentDescription)
        }, 200))
      }
    },
    /** Delay method **/
    debounce(fn, threshold) {
      var timeout;
      threshold = threshold || 100;
      return function debounced() {
        clearTimeout(timeout);
        var args = arguments;
        var _this = this;

        function delayed() {
          fn.apply(_this, args);
        }

        timeout = setTimeout(delayed, threshold);
      };
    },
    getEmbedData() {
      console.log(this.embedData)
    },
    leavePage() {
      if (confirm('Vous allez quitter le formulaire de création d\'un message')) {
        window.location.pathname = `/webhooks/detail/${this.$route.params.id}`
      }
    },
    async hoyolabArticle() {
      let url = document.getElementById('hoyolab-url').value;
      let newUrl = new URL(url);
      let id = url.substring(url.lastIndexOf('/') + 1);

      // https://www.hoyolab.com/article/3367418
      // https://bbs-api-os.hoyolab.com/community/post/wapi/getPostFull?gids=2&post_id=3261217&read=1
      if (newUrl.origin === 'https://www.hoyolab.com' && id) {
        let myHeaders = new Headers();
        // myHeaders.set('Origin', 'https://www.hoyolab.com')
        myHeaders.set('Content-Type', 'application/json')
        myHeaders.set('Accept', 'application/json, text/plain, */*')
        let myInit = {
          method: 'GET',
          headers: myHeaders,
          mode: 'cors',
          cache: 'default'
        }

        let urlApi = `https://api.guda.club:3001/https://bbs-api-os.hoyolab.com/community/post/wapi/getPostFull?gids=2&post_id=${id}&read=1`
        let response = await fetch(urlApi, myInit).then(res => {
          return res.json()
        }).catch(err => {
          this.errors.push('Erreur de communication avec l\'api de Hoyolab')
          console.log(err)
        })

        console.log(response)

        if (response.message === "OK") {
          this.modal = true
          let post = response.data.post
          // Image
          if (post.image_list.length === 1) {
            this.embedData.contentImageUrl = post.image_list[0].url
          }
          // title
          this.embedData.title = post.post.subject
          // url
          this.embedData.contentUrl = document.getElementById('hoyolab-url').value
          document.getElementById('embed-url').style.color = "#0099ff"
          // this.embedData.contentDescription = post.content.replace(/<\/?[^>]+(>|$)/g, "")
        }
      }

      return false;
    }
  },
}
</script>

<style scoped lang="scss">

.hoyolab-embed {
  display: flex;
  padding: 10px 0;
  //border:1px solid var(--guda-color);

  input {
    padding: 5px;
    width: 100%;
    outline: none;
    background-color: var(--guda-discord-placeholder);
    color: #fff;
    border-radius: 0.475rem 0 0 0.475rem;
    border: none;
  }

  .hoyolab-button-embed {
    min-width: 40px;
    width: fit-content;
    border-radius: 0 5px 5px 0;
  }
}

.message-header {
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

  .message-header-title {
    display: flex;

    button {
      margin: 3rem 1rem 3rem 0;
      min-width: 40px;
    }
  }
}

.message-wrapper {
  margin-bottom: 3rem;

  .message-wrapper-item {
    width: calc(50% - 30px);
    box-shadow: 0 0 10px 1px rgb(0 0 0 / 20%);
    margin: 0 15px;
    background-color: var(--guda-discord-background);
    padding: 0.875rem;
    border-radius: 0.675rem;
    min-height: 400px;
  }
}


form {
  #content {
    width: 100%;
    min-height: 100px;
    // textarea
    resize: none;
    outline: none;
    background-color: var(--guda-discord-placeholder);
    color: #fff;
    border-radius: 0.475rem;
    border: none;
    padding: 5px;
  }

  .discord-embed-form {
    margin-top: 1rem;
  }

  padding: inherit;

  .add-embed {
    margin-bottom: 0.4rem;
  }
}

.message-preview {
  display: flex;

  .discord-margin {
    margin-right: 1rem;

    .discord-fake-image {
      width: 50px;
      height: 50px;
      background-color: var(--guda-content);
      border-radius: 50%;
    }
  }

  .discord-content {
    .discord-message-header {
      .discord-username {
        font-weight: bold;
        color: var(--guda-color);
        position: relative;
      }

      .discord-date {
        color: #888888;
        margin-left: .675rem;
        font-size: 12px;
        position: absolute;
        width: 100%;
        left: 100%;
        bottom: 0;
        font-weight: normal;
      }
    }
  }
}


.discord-embed-container {
  background-color: #2f3136;
  display: flex;
  flex-direction: column;
  max-width: 520px;
  padding: 8px 16px 16px;
  border: 1px solid rgba(46, 48, 54, .6);
  border-radius: 0 4px 4px 0;

  .discord-embed-content {
    display: flex;
  }
}


.discord-embed {
  font-family: Roboto, sans-serif;
  color: #dcddde;
  display: flex;
  margin-top: 8px;
  margin-bottom: 8px;
  font-size: 13px;
  line-height: 150%;

  .discord-embed-left-border {
    background-color: #202225;
    flex-shrink: 0;
    width: 4px;
    border-radius: 4px 0 0 4px;

  }

  a {
    color: #0096cf;
    font-weight: 400;
    text-decoration: none;
  }

  .discord-embed-content {
    .discord-embed-title {
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      margin-top: 8px;
    }

    .discord-embed-fields {
      //display: flex;
      display: none;
      flex-wrap: wrap;
      margin-top: 8px;

      .discord-embed-field {
        min-width: 100%;
        margin-top: 5px;

        .discord-embed-field-title {
          color: var(--guda-discord-field);
          font-weight: 500;
          margin-bottom: 2px;
        }
      }
    }

    .discord-embed-title a {
      color: #00b0f4;
      font-weight: 600;
    }

    .discord-embed-description {
      margin-top: 8px;
      font-size: 0.875rem;

      strong {
        font-weight: 700;
        color: white;
      }
    }

    .discord-embed-image {
      max-width: 100%;
      margin-top: 16px;
      border-radius: 4px;
    }

    .discord-embed-thumbnail {
      max-width: 80px;
      max-height: 80px;
      margin-left: 16px;
      margin-top: 8px;
      border-radius: 4px;
      -o-object-fit: contain;
      object-fit: contain;
      -o-object-position: top center;
      object-position: top center;
    }

    .discord-embed-author {
      color: #fff;
      align-items: center;
      font-weight: 500;
      margin-top: 8px;

      .discord-embed-author-icon {
        width: 24px;
        height: 24px;
        margin-right: 8px;
        border-radius: 50%;
      }

      a {
        color: #fff;
        font-weight: 500;
      }
    }
  }

  .discord-embed-footer {
    color: #72767d;
    display: none;
    align-items: center;
    font-size: .85em;
    margin-top: 8px;

    .discord-embed-footer-icon {
      flex-shrink: 0;
      width: 20px;
      height: 20px;
      margin-right: 8px;
      border-radius: 50%;
    }
  }
}


</style>
