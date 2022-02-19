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
    this.hoyoStatsInit()
  },
  methods: {
    ...mapActions({
      getHoyoStats: 'discord/getHoyoStats'
    }),
    async hoyoStatsInit() {
      this.hoyoStats = await this.getHoyoStats(this.$route.params.uid)
    }
  },
  data() {
    return {
      hoyoStats: {}
    }
  }
}
</script>

<style scoped>

</style>
