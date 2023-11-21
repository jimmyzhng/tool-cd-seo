<template>
  <div class="chart-details-cont">
    <v-data-table-virtual
      :headers="chartStore.chartDetailsHeaders"
      :items="tableData"
      class="elevation-1"
      hide-default-footer
      height="265"
    >
      <template v-slot:item.percentage="{ item }">
        {{ item.percentage }}%
      </template>
    </v-data-table-virtual>
  </div>
</template>
<script>
import { useChartsStore } from "../stores/chartStore";
import { computed, onMounted } from "vue";
import {
  VDataTable,
  VDataTableServer,
  VDataTableVirtual,
} from "vuetify/labs/VDataTable";

export default {
  components: {
    VDataTable,
    VDataTableServer,
    VDataTableVirtual,
  },
  setup() {
    const chartStore = useChartsStore();

    onMounted(async () => {
      await chartStore.fetchBotNames();
    });

    const tableData = computed(() => {
      console.log("tableData", chartStore.chartData);

      if (!chartStore.chartData) return [];

      const entries = Object.entries(chartStore.chartData);

      // map each entry to format for the table
      const processedBots = entries.map(([botName, dataPoints]) => {
        if (!dataPoints.data || dataPoints.data.length === 0) return null;

        const latestPoint = dataPoints.data[dataPoints.data.length - 1];
        if (!latestPoint) return null;

        const percentage = parseFloat(latestPoint[1]);
        const totalWebsites = parseInt(
          chartStore.showAmount.replace("Top ", "")
        );
        const actualBlockedCount = Math.round(
          (percentage * totalWebsites) / 100
        );

        return {
          name: botName,
          percentage: percentage.toFixed(2),
          count: actualBlockedCount,
        };
      });

      if (chartStore.botNames) {
        chartStore.botNames.forEach((botName) => {
          if (!processedBots.some((bot) => bot.name === botName)) {
            processedBots.push({
              name: botName,
              percentage: "0.00",
              count: 0,
            });
          }
        });
      }

      return processedBots.filter((row) => row !== null);
    });

    // console.log(tableData);

    return {
      chartStore,
      tableData,
    };
  },
};
</script>
<style scoped>
.chart-details-cont {
  width: 45%;
}

@media (max-width: 768px) {
  .chart-details-cont {
    width: 100%;
    margin-bottom: 35px;
    font-size: 12px;
  }
}
</style>
