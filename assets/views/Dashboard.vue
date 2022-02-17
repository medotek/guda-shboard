<template>
  <div class="dashboard-page">
    <h1 class="dashboard-title">Mon tableau de bord</h1>

    <div class="dashboard-features">
      <b-link :to="{ path:'webhooks' }">
        <div class="dashboard-feature-card">
          <div class="label"><font-awesome-icon icon="fa-brands fa-discord"/>Webhooks</div>
        </div>
      </b-link>
      <b-link :to="{ path:'hoyolab' }">
        <div class="dashboard-feature-card">
          <div class="label">Hoyolab</div>
        </div>
      </b-link>
    </div>

    <router-view></router-view>
  </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  name: "Dashboard"
}
</script>

<style scoped lang="scss">
.dashboard-page {

  .dashboard-features {
    display: flex;
    background-color: var(--background-menu);
    border-radius: 0.5rem;
    padding: 0.625rem;

    a {
      text-decoration: none;
      margin-right: 1rem;
    }

    .dashboard-feature-card {
      background-color: var(--guda-color);
      border-radius: 0.5rem;
      padding: 0.625rem;
      width: fit-content;

      .h3 {
        color: var(--text-primary-color);
        text-align: center;
      }

      img {
        width: auto;
        height: auto;
        border-radius: 0.5rem;
      }
    }
  }
}
</style>
