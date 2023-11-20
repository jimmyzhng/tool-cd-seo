<template>
  <div class="blocked-chart-cont">
    <div class="chart-container">
      <div class="chart-title">
  <div class="main-title" :style="{ fontSize: chartTitleFontSize }">{{ mainTitle }}</div>
  <div class="category-title" :style="{ fontSize: categoryTitleFontSize }">{{ categoryTitle }}</div>
</div>
      <apexchart
        :key="chartRenderKey"
        type="line"
        :options="chartOptions"
        :series="series"
      ></apexchart>
      <img :src="purpleLogo" class="chart-logo" />
    </div>
    <div class="chart-info-cont">
      <ChartDetails />
      <div class="chart-selectors">
        <v-select
          label="Amount"
          class="chart-amt-selector"
          v-model="chartStore.showAmount"
          :items="chartStore.topAmounts"
          density="compact"
          variant="outlined"
          color="#7859ff"
          item-color="#7859ff"
        ></v-select>
        <v-select
          label="Category"
          class="chart-category-selector"
          v-model="chartStore.showCategory"
          :items="websiteStatusStore.categories"
          density="compact"
          variant="outlined"
          color="#7859ff"
          item-color="#7859ff"
          clearable
        ></v-select>
        <Citation />
        <v-btn
          class="save-chart-btn"
          @click="downloadChart"
          variant="outlined"
          color="#7859ff"
          item-color="#7859ff"
          height="40"
          >Save as Image</v-btn
        >
        <p class="terms-and-conditions">
          By continuing, you agree to our
          <a href="https://originality.ai/terms-and-conditions" target="_blank"
            >Terms and Conditions.</a
          >
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch, computed, onUnmounted } from "vue";
import VueApexCharts from "vue3-apexcharts";
import { useChartsStore } from "../stores/chartStore";
import html2canvas from "html2canvas";
import purpleLogo from "/purpleLogo.svg";
import { useWebsiteStatusStore } from "../stores/websiteStatusStore";
import Citation from "./Citation.vue";
import ChartDetails from "./ChartDetails.vue";

let backendUrl = import.meta.env.VITE_APP_BACKEND_URL;
let botType = import.meta.env.VITE_APP_BOT_CATEGORY;

export default {
  components: {
    apexchart: VueApexCharts,
    Citation,
    ChartDetails,
  },
  setup() {
    const websiteStatusStore = useWebsiteStatusStore();
    const chartStore = useChartsStore();
    const chartRenderKey = ref(0);
    const chartTitle = computed(() => {
  let title = `The Percent of the ${chartStore.showAmount} Websites Blocking ${botType} Web Crawlers`;

  if (chartStore.showCategory) {
    title += `<br>${chartStore.showCategory}`;
  }

  return title;
});

const mainTitle = computed(() => `The Percent of the ${chartStore.showAmount} Websites Blocking ${botType} Web Crawlers`);
const categoryTitle = computed(() => chartStore.showCategory);

    const yAxisTitle = computed(() => {
      return `% of ${chartStore.showAmount}`;
    });

    const windowWidth = ref(window.innerWidth);

const updateWidth = () => {
  windowWidth.value = window.innerWidth;
};

const chartTitleFontSize = computed(() => {
  const calculatedSize = windowWidth.value * 0.015;
  const minSize = 11; 
  const maxSize = 35; // Set your maximum size here
  const finalSize = Math.max(Math.min(calculatedSize, maxSize), minSize);
  return `${finalSize}px`;
});

const categoryTitleFontSize = computed(() => {
  const calculatedSize = windowWidth.value * 0.015;
  const minSize = 7; 
  const maxSize = 28; // Set your maximum size here
  const finalSize = Math.max(Math.min(calculatedSize, maxSize), minSize);
  return `${finalSize}px`;
});

const axisTitleFontSize = computed(() => {
  const calculatedSize = windowWidth.value * 0.0175;
  const minSize = 10;
  return `${Math.max(calculatedSize, minSize)}px`;
});
const chartHeight = computed(() => {
      return windowWidth.value < 768 ? '325px' : '100%';
    });

const chartOptions = computed(() => ({
  chart: {
    id: "blocked-chart",
    toolbar: {
      show: true,
      tools: {
        pan: false,
        zoom: false,
        selection: false,
        download: false,
        zoomin: false,
        zoomout: false,
        reset: false,
      },
    },
  },
  responsive: [
    {
      breakpoint: 768,
      options: {
        chart: {
          height: chartHeight.value
        }
      }
    },
    {
      breakpoint: 10000,
      options: {
        chart: {
          height: 'auto'
        }
      }
    }
  ],
  tooltip: {
    enabled: true,
    y: {
      formatter: function (val) {
        const total =
          (val / 100) * parseInt(chartStore.showAmount.replace("Top ", ""));
        return `${Math.round(total)} (${val.toFixed(1) + "%"})`;
      },
    },
  },

  xaxis: {
    min: new Date("2023-08-07").getTime(),
    title: {
      text: "Date",
      style: {
        fontSize: axisTitleFontSize.value,
      },
    },
    type: "datetime",
  },
  yaxis: {
    title: {
      text: yAxisTitle.value,
      style: {
        fontSize: axisTitleFontSize.value,
      },
    },
    labels: {
      formatter: function (val) {
        return val.toFixed(0);
      },
    },
  },
}));

    const fetchChartData = () => {
      let selectedAmount = chartStore.showAmount.replace("Top ", "");
      let url = `${backendUrl}blocked-websites.php?limit=${selectedAmount}`;
      if (chartStore.showCategory !== null) {
        url += `&category=${encodeURIComponent(chartStore.showCategory)}`;
      }

      fetch(url)
        .then((response) => {
          if (!response.ok || !response.body) {
            throw new Error("Network response was not ok or no content.");
          }
          return response.json();
        })
        .then((data) => {
          const totalWebsites = selectedAmount;
          const botsMapping = {};

          data.forEach((entry) => {
            const { bot_name, block_date, blocked_count } = entry;
            // console.log("entry", entry);
            if (!botsMapping[bot_name]) {
              botsMapping[bot_name] = [];
            }
            botsMapping[bot_name].push({
              date: block_date,
              count: blocked_count,
            });
          });

          // object with one key-value pair for each bot name. value is an array of objects of all check dates, with a count for each check day.
          // console.log(botsMapping);

          const startDate = new Date("2023-08-07");
          const today = new Date();
          let botSeries = {};

          for (const bot in botsMapping) {
            let runningTotal = 0;

            // clone the bot's data and sort by date => ensure the right cumulative calculation
            const sortedData = [...botsMapping[bot]].sort(
              (a, b) => new Date(a.date) - new Date(b.date)
            );

            const mappedData = {};

            sortedData.forEach((entry) => {
              runningTotal += entry.count;
              const percentageBlocked = (runningTotal / totalWebsites) * 100;

              mappedData[entry.date] = percentageBlocked;
            });

            botSeries[bot] = { name: bot, data: [] };

            // store last recorded %
            let lastPercentage = 0;

            for (
              let d = new Date(startDate);
              d <= today;
              d.setDate(d.getDate() + 1)
            ) {
              // format date
              const formattedDate = d.toISOString().split("T")[0];

              // if date exists in mappedData, use its percentage.. else, use the last recorded percentage.
              const percentage =
                mappedData[formattedDate] !== undefined
                  ? mappedData[formattedDate]
                  : lastPercentage;

              botSeries[bot].data.push([d.getTime(), percentage]);

              // update last recorded %
              lastPercentage = percentage;
            }
          }

          // sorting to show in descending order for legend
          // first, convert botSeries into an array of [bot, series] pairs
          let botSeriesArray = Object.entries(botSeries);

          // sort based on the LAST value of the data array for each bot
          botSeriesArray.sort((a, b) => {
            let lastValueA = a[1].data[a[1].data.length - 1][1];
            let lastValueB = b[1].data[b[1].data.length - 1][1];
            return lastValueB - lastValueA; // For descending order
          });

          // convert back to object
          botSeries = Object.fromEntries(botSeriesArray);

          // console.log("botSeries", botSeries);

          series.value = Object.values(botSeries);
          chartStore.setChartData(botSeries);
          console.log("chartStore data", chartStore.chartData);
        })
        .catch((error) => {
          console.error("Fetch error:", error);
        });
    };

    const downloadChart = async () => {
      const elementToSave = document.querySelector(".chart-container");
      if (elementToSave) {
        const canvas = await html2canvas(elementToSave);
        let link = document.createElement("a");
        let sanitizedFilename = chartTitle.value.replace(/[^a-z0-9]/gi, " ");

        link.download = `Originality.AI - ${sanitizedFilename}.png`;

        link.href = canvas.toDataURL("image/png");
        link.click();
      }
    };

    const series = ref([]);

    onMounted(() => {
      fetchChartData();
      window.addEventListener('resize', updateWidth);
      
    });

    onUnmounted(() => {
      window.removeEventListener('resize', updateWidth);
    });

    watch(
      [() => chartStore.showAmount, () => chartStore.showCategory],
      (newVal, oldVal) => {
        // console.log("showAmount changed from", oldVal, "to", newVal);
        fetchChartData();
        chartRenderKey.value++;
      }
    );

    return {
      websiteStatusStore,
      chartStore,
      chartOptions,
      chartRenderKey,
      series,
      downloadChart,
      chartTitle,
      purpleLogo,
      mainTitle, 
      categoryTitle,
      chartTitleFontSize,
      categoryTitleFontSize
    };
  },
};
</script>

<style scoped>

.chart-title {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}

.main-title {
    font-weight: 800;
  }

  .category-title {
    font-weight: 400
  }

.chart-container {
  position: relative;
  width: 100%;
  height: 100%;
  margin: 25px 0;
}

.chart-info-cont {
  width: 100%;
  display: flex;
  flex-direction: row;
  justify-content: space-around;
}

.chart-logo {
  position: absolute;
  top: 100px;
  left: 50px;
  min-width: 50px;
  max-width: 25%;
}

.chart-selectors {
  width: 40%;
  margin: 10px 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.save-chart-btn,
.chart-amt-selector,
.chart-category-selector {
  font-size: 18px;
  width: 100%;
}

.save-chart-btn {
  background-color: #7859ff;
  color: white !important;
  font-weight: 600;
  border: 1px solid #7859ff;
}

.terms-and-conditions {
  margin-top: 5px;
  font-size: 10px;
  font-style: italic;
}

@media (max-width: 768px) {


  .chart-logo {
    top: 60px;
  left: 45px;
  }
  .chart-info-cont {
    flex-direction: column;
    align-items: center;
  }

  .chart-selectors {
    width: 100%;
  }
}
</style>
