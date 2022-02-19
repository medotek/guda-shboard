<template>
  <div>
    <div class="wrapper">
      <div class="h3">Stats</div>
      <ul v-for="(value, index) in hoyoStats">
        <li>{{ index + ' : ' + value }}</li>
      </ul>
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
      getHoyoPostsList: 'discord/getHoyoUserPostList'
    }),
    async hoyoInit() {
      this.hoyoStats = await this.getHoyoStats(this.$route.params.uid)
      this.hoyoPosts = await this.getHoyoPostsList(this.$route.params.uid)
      console.log(this.hoyoPosts)
    }
  },
  data() {
    return {
      hoyoStats: {},
      hoyoPosts: {},
    }
  }
}
</script>

<style scoped>

</style>
