<template>
  <div class="discord-embed-form-wrapper">
    <div class="discord-embed-wrapper">
      <b-row class="header">
        <b-col sm="6" class="embed">
          <fieldset>
            <legend>Embed</legend>
            <input type="text" class="title-text" placeholder="Titre" v-model="embed.title">
            <label> Choisir une couleur
              <input type="color" class="color" v-model="embed.color" value="#0099ff">
            </label>
          </fieldset>
        </b-col>
        <b-col sm="6" class="author">
          <fieldset>
            <legend>Auteur</legend>
            <input v-model="embed.authorName" type="text" class="author-name" placeholder="Nom de l'auteur">
            <input v-model="embed.authorUrl" type="url" class="author-url" placeholder="Url (site) de l'auteur">
            <input v-model="embed.authorAvatarUrl" type="url" class="author-icon-url"
                   placeholder="Image de profil de l'auteur">
          </fieldset>
        </b-col>
      </b-row>
      <fieldset class="additionnal-info">
        <legend>Additionnal info</legend>
        <textarea v-model="embed.contentDescription" class="description" placeholder="Description"></textarea>
        <input v-model="embed.contentUrl" type="text" class="url" placeholder="url">
        <input v-model="embed.contentImageUrl" type="text" class="image" placeholder="Image url">
        <input v-model="embed.contentThumbnail" type="text" class="thumbnail" placeholder="thumbnail">
        <div class="footer">
          <input v-model="embed.contentFooter" type="checkbox" class="ios8-switch ios8-switch-sm" id="footer">
          <label for="footer">Footer</label>
        </div>
      </fieldset>
    </div>
  </div>
</template>

<script>
export default {
  name: "DiscordEmbed",
  props: ['value'],
  computed: {
    embed: {
      get() {
        return this.value;
      },
      set(value) {
        this.$emit('input', value);
      }
    }
  },
}
</script>

<style scoped lang="scss">

//EMBED FORM

fieldset {
  padding: 10px;
  border: none;
  border-radius: 5px;
  box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.2);
  background-color: #36393f;

  legend {
    color: var(--guda-color);
    font-weight: bold;
    text-align: center;
    box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.2);
    background-color: var(--background-menu);
    border-radius: 5px;
  }
}

.discord-embed-form-wrapper {
  .header {
    margin-bottom: 10px;

    .author, .embed {
      fieldset {
        display: flex;
        flex-direction: column;

        input {
          margin-bottom: 10px;
          width: 100%;
          color: white;
          padding: 5px;
          border: none;
          background-color: #40444b;
          outline: none;

          &::placeholder {
            font-weight: 400;
          }
        }
      }
    }
  }

  .additionnal-info {
    display: flex;
    flex-direction: column;

    input, textarea {
      color: white;
      padding: 5px;
      margin-bottom: 10px;
      border: none;
      background-color: #40444b;
      outline: none;

      &::placeholder {
        font-weight: 400;
      }
    }
  }
}

.discord-embed .discord-embed-field.discord-embed-field-inline {
  flex-grow: 1;
  flex-basis: auto;
  min-width: 100px !important;
}

input[type="color"] {
  -webkit-appearance: none;
  border: none;
  width: 32px;
  border-radius: 5px;
  height: 32px;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
  border-radius: 5px;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 5px;
}

input[type="checkbox"].ios8-switch {
  position: absolute;
  margin: 8px 0 0 16px;
}

input[type="checkbox"].ios8-switch + label {
  position: relative;
  padding: 5px 0 0 50px;
  line-height: 2.0em;
}

input[type="checkbox"].ios8-switch + label:before {
  content: "";
  position: absolute;
  display: block;
  left: 0;
  top: 0;
  width: 40px; /* x*5 */
  height: 24px; /* x*3 */
  border-radius: 16px; /* x*2 */
  background: #fff;
  border: 1px solid #d9d9d9;
  -webkit-transition: all 0.3s;
  transition: all 0.3s;
}

input[type="checkbox"].ios8-switch + label:after {
  content: "";
  position: absolute;
  display: block;
  left: 0;
  top: 0;
  width: 24px; /* x*3 */
  height: 24px; /* x*3 */
  border-radius: 16px; /* x*2 */
  background: #fff;
  border: 1px solid #d9d9d9;
  -webkit-transition: all 0.3s;
  transition: all 0.3s;
}

input[type="checkbox"].ios8-switch + label:hover:after {
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
}

input[type="checkbox"].ios8-switch:checked + label:after {
  margin-left: 16px;
}

input[type="checkbox"].ios8-switch:checked + label:before {
  background: var(--guda-color);
}

</style>
