<template>
  <div class="wrapper hoyolab-stats">
    <div class="hoyolab-stats-header">
      <div class="h3">
        Stats<span class="guda-new">NEW</span>
      </div>
    </div>
    <div class="hoyolab-stats-content">
      <LineChartGenerator
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
import { Line as LineChartGenerator } from 'vue-chartjs/legacy'

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
  components: { LineChartGenerator },
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
      default: () => {}
    },
    plugins: {
      type: Array,
      default: () => []
    }
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
          {
            // Sample : views, likes, ...
            label: 'Data One',
            backgroundColor: '#f87979',
            borderColor: '#f87979',
            data: [40, 39, 10, 40, 39, 80, 40]
          }
        ]
      },
      chartOptions: {
        responsive: true,
        maintainAspectRatio: false
      },
      title: {
        display: true,
        text: 'Hoyo Stats'
      }
    }
  }
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
