export const useWebsitesStore = defineStore({
  id: 'websites',
  state: () => ({
    websites: [],
    filteredWebsites: [],
    searchQuery: "",
    topXFilter: null,
    categoryFilter: null,
  }),
  actions: {
    fetchWebsites() {
    },
    filterWebsites() {
    },

  }
});