<template>
  <div class="wrapper hoyolab-stats">
    <div class="hoyolab-stats-header">
      <div class="h3">
        Stats<span class="guda-new">NEW</span>
      </div>
    </div>
    <div class="hoyolab-stats-content">
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
    <div class="test">
      <button @click="sample1">Change data 1</button>
      <button @click="sample2">Change data 2</button>
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
      default: () => {}
    }
  },
  mounted() {
    // TODO : Init chart with user data
    console.log(this.dataStat)
    // Alimenter le tableau pour les données
    // this.chartData.datasets.push();
    // this.chartData.labels = [];
  },
  data() {
    return {
      chartData: {
        labels: [
          'January',
          'February',
          'March',
          'April',
          'May',
          'June',
          'July'
        ],
        datasets: [
          // {
          //   // Sample : views, likes, ...
          //   label: 'Views',
          //   backgroundColor: '#f87979',
          //   borderColor: '#f87979',
          //   data: [40, 39, 10, 40, 39, 80, 40]
          // },
          // {
          //   // Sample : views, likes, ...
          //   label: 'Likes',
          //   backgroundColor: '#79f8d2',
          //   borderColor: '#79f8d2',
          //   data: [1, 2, 3, 34, 4, 7, 8]
          // },
          // {
          //   // Sample : views, likes, ...
          //   label: 'Replies',
          //   backgroundColor: '#797ff8',
          //   borderColor: '#797ff8',
          //   data: [10, 4, 40, 1, 70, 41, 40]
          // }
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
    sample1() {
      // Sample : update data of charts
      // ChartJS.getChart(this.chartId).data.datasets[0].data = [10, 2, 40, 1, 47, 44, 1]
      // this.update()
    },
    sample2() {
      // ChartJS.getChart(this.chartId).data.datasets[0].data = [40, 39, 10, 40, 39, 80, 40]
      // this.update()
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
    }
  }
}
</style>
