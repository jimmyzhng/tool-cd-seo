import { defineStore } from 'pinia';
import axios from 'axios';
import { convertToCSV, downloadCSV } from '../utils/tableHelper';

let backendUrl = import.meta.env.VITE_APP_BACKEND_URL;

export const useWebsiteStatusStore = defineStore({
  id: 'websiteStatus',
  state: () => ({
    sites: [],
    categories: [],
    selectedCategory: null,
    displayTopLimit: ["Top 10", "Top 25", "Top 100", "Top 250", "Top 500", "Top 1000"],
    itemsPerPage: [
      { value: 10, title: "Top 10" },
      { value: 25, title: "Top 25" },
      { value: 100, title: "Top 100" },
      { value: 250, title: "Top 250" },
      { value: 500, title: "Top 500" },
      { value: 1000, title: "Top 1000" },
    ],
    selectedLimit: "Top 1000",
    totalSites: 0,
    dateFilter: null,
    headers: [
      {
        title: "",
        align: "end",
        sortable: false,
        key: "isFavorite",
      },
      {
        title: "Position",
        align: "center",
        sortable: true,
        key: "position",
      },
      {
        title: "Website",
        align: "center",
        sortable: true,
        key: "url",
      },
      {
        title: "Category",
        align: "center",
        sortable: true,
        key: "category",
      },
            {
        title: "GPTBot",
        align: "center",
        sortable: false,
        key: "botsStatuses.GPTBot.status",
        slotName: "GPTBot"
      },
      {
        title: "CCBot",
        align: "center",
        sortable: false,
        key: "botsStatuses.CCBot.status",
        slotName: "CCBot"
      },
      {
        title: "Anthropic AI",
        align: "center",
        sortable: false,
        key: "botsStatuses.anthropicAi.status",
        slotName: "anthropicAi"
      },
      {
        title: "Google-Extended",
        align: "center",
        sortable: false,
        key: "botsStatuses.GoogleExtended.status",
        slotName: "GoogleExtended"
      },
      {
        title: "MJ12bot",
        align: "center",
        sortable: false,
        key: "botsStatuses.MJ12bot.status",
        slotName: "MJ12bot"
      },
      {
        title: "AhrefsBot",
        align: "center",
        sortable: false,
        key: "botsStatuses.AhrefsBot.status",
        slotName: "AhrefsBot"
      },
      {
        title: "SemrushBot",
        align: "center",
        sortable: false,
        key: "botsStatuses.SemrushBot.status",
        slotName: "SemrushBot"
      },
      {
        title: "dotbot",
        align: "center",
        sortable: false,
        key: "botsStatuses.dotbot.status",
        slotName: "dotbot"
      },
      {
        title: "rogerbot",
        align: "center",
        sortable: false,
        key: "botsStatuses.rogerbot.status",
        slotName: "rogerbot"
      },
      {
        title: "Screaming Frog",
        align: "center",
        sortable: false,
        key: "botsStatuses.ScreamingFrogSEOSpider.status",
        slotName: "ScreamingFrogSEOSpider"
      },
      {
        title: "cognitiveSEO",
        align: "center",
        sortable: false,
        key: "botsStatuses.cognitiveSEO.status",
        slotName: "cognitiveSEO"
      },
      {
        title: "OnCrawl",
        align: "center",
        sortable: false,
        key: "botsStatuses.OnCrawl.status",
        slotName: "OnCrawl"
      },
    ],
    currentParameters: {
      page: 1,
      sitesPerPage: 10,
      sortBy: null,
      sortOrder: null,
      search: '',
      category: null,
    },
    currentPage: 1,
    sitesPerPage: 10,
    search: '',
    name: '',
    loading: false,
    lastUpdated: '',
  }),
  actions: {
    fetchStatuses({ page, sitesPerPage, sortBy, sortOrder, search, category, onlyFavorites = false }) {
      this.loading = true;
      const savedFavorites = JSON.parse(localStorage.getItem('favorites') || '[]');

      setTimeout(() => {
        axios.get(
          // 'http://localhost:3001/get_websites.php'
          `${backendUrl}get_websites.php`
          , {
            params: {
              page, sitesPerPage, sortBy, sortOrder, search, category
            }
          })
          .then(res => {
            this.lastUpdated = res.data.lastUpdated;

            const updatedSites = Array.isArray(res.data.sites) ? res.data.sites.map(site => {
              if (typeof site.botsStatuses === 'string') {
                try {
                  site.botsStatuses = JSON.parse(site.botsStatuses);
                  site.botsStatuses = this.formatBotStatuses(site.botsStatuses);
                } catch (error) {
                  console.error('Error parsing botsStatuses:', site.botsStatuses);
                }
              } else {
                console.error('Invalid or missing botsStatuses:', site.botsStatuses);
              }
              return {
                ...site,
                isFavorite: savedFavorites.includes(site.url)
              };
            })
              .filter(site => !onlyFavorites || site.isFavorite) : [];
            this.sites = updatedSites;
            console.log(this.sites[0].botsStatuses)
            this.totalSites = res.data.totalSites;
            this.loading = false;
          })
          .catch(error => {
            console.error("There was an error!", error);
            this.loading = false;
          });

      }, 500);

    },
    fetchCategories() {
      axios.get(
        // 'http://localhost:3001/get_categories.php'
        `${backendUrl}get_categories.php`
      )
        .then(res => {
          this.categories = res.data.categories;
        })
        .catch(error => {
          console.error("There was an error fetching categories!", error);
        });
    },
    formatBotStatuses(botsStatuses) {
      return Object.keys(botsStatuses).reduce((formatted, key) => {
        const camelCaseKey = this.formatSlotName(key);
        formatted[camelCaseKey] = botsStatuses[key];
        return formatted;
      }, {});
    },
    fetchDataAndDownloadCSV() {
      // fetch('http://localhost:3001/get_csv.php')
        fetch(`${backendUrl}get_csv.php`)
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            console.error("Error fetching data:", data.error);
          } else {
            const csvData = convertToCSV(data.websites);
            downloadCSV(csvData, 'Originality.AI - Top 1000 Websites Blocking AI Web Crawlers Data.csv');
          }
        })
        .catch(error => {
          console.error("Error:", error);
        });
    },
    getSlotName(key) {
      return this.formatSlotName(key);
    },
    formatSlotName(key) {
      return key.replace(/-./g, match => match.charAt(1).toUpperCase());
    }
  },
  getters: {
    flattenedSites() {
      return this.sites.map(site => {
        let flattenedSite = { ...site };
  
        // new botsStatuses object
        flattenedSite.botsStatuses = {};
  
        // loop over each bot status
        for (let botName in site.botsStatuses) {
          let flattenedBotName = botName.replace(/\s+/g, '');
          flattenedSite.botsStatuses[flattenedBotName] = {
            status: site.botsStatuses[botName].status
          };
        }
  
        return flattenedSite;
      });
    }

  }
});
