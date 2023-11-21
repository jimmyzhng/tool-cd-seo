import { defineStore } from 'pinia';

let backendUrl = import.meta.env.VITE_APP_BACKEND_URL;

export const useChartsStore = defineStore({
  id: 'charts',
  state: () => ({
    chartData: [],
    botNames: [],
    topAmounts: ["Top 10", "Top 25", "Top 100", "Top 250", "Top 500", "Top 1000"],
    showAmount: "Top 1000",
    showCategory: null,
    chartDetailsHeaders: [
      { title: "Bot Name", value: "name", align: "center" },
      { title: "Blocked", value: "count", align: "center" },
      { title: "%", value: "percentage", align: "center" },
    ],
  }),
  _actions: {
    setChartData(data) {
      this.chartData = data;
    },
    fetchChartData() {
    },
    async fetchBotNames() {
      const response = await fetch(
        // "http://localhost:3001/get_bots.php"
        `${backendUrl}get_bots.php`
      );
      if (response.ok) {
        const data = await response.json();
        this.botNames = data.botNames;
        // console.log('bot names', this.botNames)
      }
    }
  },
  get actions() {
    return this._actions;
  },
  set actions(value) {
    this._actions = value;
  },
});