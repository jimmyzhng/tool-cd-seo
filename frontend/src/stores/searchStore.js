import { defineStore } from 'pinia';
import { validateAndFixUrl } from '../utils/searchHelper';
import { nextTick } from 'vue';

let backendUrl = import.meta.env.VITE_APP_BACKEND_URL;

export const useSearchStore = defineStore({
  id: 'search',
  state: () => ({
    showSearch: false,
    searchQuery: "",
    searchLoading: false,
    searchLoaded: false,
    searchResult: null,
    searchResultVisible: false,
    searchTableHeaders: [{ title: "User-Agent", align: "center", sortable: false, value: "userAgent" },
    { title: "Status", align: "center", sortable: false, value: "blockageStatus" }],
    searchError: null
  }),
  actions: {
    toggleSearch() {
      this.showSearch = !this.showSearch;
    },
    async fetchSearch() {
      this.searchLoading = true;
      this.setSearchError(null);

      const validUrl = validateAndFixUrl(this.searchQuery);
      if (!validUrl) {
        setSearchError("Please enter a valid URL.");
        return;
      }

      try {
        const response = await fetch(
          // 'http://localhost:3001/search_website.php'
          `${backendUrl}search_website.php`
          , {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ website: validUrl })
          });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }

        if (data.success) {
          // console.log('data', data);
          this.searchResult = data;
        } else {
          console.error("Error retrieving blockage information.");
          this.setSearchError("Error: invalid URL.");
        }
      } catch (error) {
        console.error("Error fetching data:", error);
        this.setSearchError("Error: invalid URL.");
      } finally {
        this.searchResultVisible = true;

        await nextTick();

        if (document.querySelector('.search-result-cont')) {
          document.querySelector('.search-result-cont').scrollIntoView({ behavior: "smooth" });
        }

        this.searchLoading = false;
        this.searchLoaded = true;
      }
    },
    closeSearchResult() {
      this.searchResultVisible = false;

    },
    setSearchError(err) {
      // console.log("Setting search error:", err);
      this.searchError = err;
    }

  }
});