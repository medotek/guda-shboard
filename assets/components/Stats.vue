<template>
  <div class="wrapper hoyolab-stats">
    <div class="hoyolab-stats-header">
      <div class="h3">
        Stats<span class="guda-new">NEW</span><span class="guda-beta">BETA</span>
      </div>
    </div>
    <div class="hoyolab-stats-content">
      <div class="period">
        <button @click="day">Last 24 Hours</button>
        <!--            <button @click="sample2">Change data 2</button>-->
      </div>
      <LineChartGenerator
          ref="stat"
          :chart-options="chartOptions"
          :chart-data="chartData"
          :chart-id="chartId"
          :dataset-id-key="datasetIdKey"
          :plugins="plugins"
          :css-classes="cssClasses"
          :styles="styles"
          :width="width"
          :height="height"
      />
    </div>
  </div>
</template>

<script>
import {Line as LineChartGenerator} from 'vue-chartjs/legacy'

import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  CategoryScale,
  PointElement
} from 'chart.js'

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    CategoryScale,
    PointElement
)

export default {
  name: "Stats",
  components: {LineChartGenerator},
  props: {
    chartId: {
      type: String,
      default: 'line-chart'
    },
    datasetIdKey: {
      type: String,
      default: 'label'
    },
    width: {
      type: Number,
      default: 400
    },
    height: {
      type: Number,
      default: 400
    },
    cssClasses: {
      default: '',
      type: String
    },
    styles: {
      type: Object,
      default: () => {
      }
    },
    plugins: {
      type: Array,
      default: () => []
    },
    dataStat: {
      type: Object,
      default: () => {
      }
    }
  },
  mounted() {
    // TODO : Init chart with user data
    let datasets = this.dataStat.datasets
    let labels = this.dataStat.labels
    if (this.dataStat.datasets) {
      if (Array.isArray(datasets)) {
        ChartJS.getChart(this.chartId).data.datasets = []
        datasets.forEach(dataset => {
          ChartJS.getChart(this.chartId).data.datasets.push(dataset);
        })
      }

      if (labels) {
        if (Array.isArray(labels))
          ChartJS.getChart(this.chartId).data.labels = labels;
      }
    }
    ChartJS.getChart(this.chartId).update()
    // Alimenter le tableau pour les données
    // this.chartData.datasets.push();
    // this.chartData.labels = [];
  },
  data() {
    return {
      chartData: {
        labels: [
          ''
        ],
        datasets: [
          {}
        ]
      },
      chartOptions: {
        responsive: true,
        maintainAspectRatio: false
      },
      title: {
        display: true,
        text: 'Hoyo Stats'
      },
      key: 'postStat'
    }
  },
  methods: {
    day() {
      // Sample : update data of charts
      // ChartJS.getChart(this.chartId).data.datasets[0].data = [10, 2, 40, 1, 47, 44, 1]
      // this.update()
    },
    sample2() {
      // ChartJS.getChart(this.chartId).data.datasets[0].data = [40, 39, 10, 40, 39, 80, 40]
      this.update()
    },
    update() {
      ChartJS.getChart(this.chartId).update()
    }
  },
}
</script>

<style scoped lang="scss">
.hoyolab-stats {

  .hoyolab-stats-header {
    .h3 {
      width: min-content;
      position: relative;
      z-index: 1;

      .guda-new {
        position: absolute;
        top: 0;
        left: 90%;
        z-index: 0;
      }
      .guda-beta {
        position: absolute;
        top: 0;
        left: 140%;
        z-index: 0;
      }
    }
  }

  .hoyolab-stats-content {
    .period {
      text-align: center;

      button {
        border-radius: 5px;
        background-color: #FF7A59FF;
        color: white;
      }
    }
  }
}
</style>
