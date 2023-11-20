<template>
  <v-card
    class="search-result-cont"
    v-if="
      searchStore.searchResult &&
      searchStore.showSearch &&
      searchStore.searchResultVisible
    "
    variant="outlined"
  >
    <v-card-title>
      <v-row no-gutters>
        <v-col cols="11" class="text-left text-h5 card-title"
          >
          <span>Search result for: </span>
          
          <span>{{ searchStore.searchResult.data.website }}</span></v-col
        >
        <v-col cols="1" class="d-flex justify-end align-start">
          <v-icon
            class="search-close-btn"
            @click="searchStore.closeSearchResult"
            >mdi-close-circle</v-icon
          >
        </v-col>
      </v-row>
    </v-card-title>
    <v-card-text>
      <div class="text-left card-text" v-if="noBlockageFound">
        This website is currently not blocking any AI web crawlers.
      </div>
      <div v-else>
        <v-data-table
          :headers="searchStore.searchTableHeaders"
          :items="searchStore.searchResult.data.agents"
          item-key="name"
          class="card-text"
        >
          <template v-slot:item.userAgent="{ item }">
            {{
              item.userAgent === "*" ? "* (All User-Agents)" : item.userAgent
            }}
          </template>
        </v-data-table>
      </div>
    </v-card-text>
    <v-card-actions class="search-result-actions">
      <v-row>
        <v-spacer></v-spacer>
        <v-btn
          class="robots-btn"
          variant="outlined"
          :href="`${searchStore.searchResult.data.website}/robots.txt`"
          target="_blank"
        >
          Robots.txt File
        </v-btn>
      </v-row>
    </v-card-actions>
  </v-card>
</template>

<script>
import { useSearchStore } from "../stores/searchStore";
import { VDataTable } from "vuetify/labs/VDataTable";
import { computed } from "vue";

export default {
  components: {
    VDataTable,
  },
  setup() {
    const searchStore = useSearchStore();

    const noBlockageFound = computed(() => {
      if (!searchStore.searchResult.data.agents) return true;

      return searchStore.searchResult.data.agents.every(
        (agent) => agent.blockageStatus === "No Blockage"
      );
    });

    return {
      searchStore,
      noBlockageFound,
    };
  },
};
</script>

<style scoped>
button {
  background-color: #7164ac !important;
}
.search-close-btn:hover {
  opacity: 0.75;
}

.search-close-btn {
  color: #a2a2a2 !important;
}
.search-result-actions {
  padding-right: 20px;
}
.search-result-cont {
  padding: 10px;
  margin-top: 20px;
}

.robots-btn {
  background-color: #7859ff;
  color: white !important;
  font-weight: 600;
  border: 1px solid #7859ff;
}

.card-title {
  font-weight: 750;
}

.card-text {
  padding-top: 15px;
  font-size: 18px;
}

@media (max-width: 768px) {
  .search-result-cont {
    padding: 2.5px;
    margin-top: 15px;
  }

  .card-title {
    font-size: 16px !important;
    font-weight: 600;
    display: flex;
    flex-direction: column;
  }

  .card-text {
    font-size: 12px !important;
  }

  .search-close-btn {
    font-size: 18px !important;
  }

  .robots-btn {
    font-size: 12px;
  }
}
</style>
