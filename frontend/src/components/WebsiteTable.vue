<template>
  <div class="chart-container">
    <div class="table-options">
      <div class="filters">
              <v-btn
        @click="toggleFavoritesOnly"
        variant="outlined"
        color="#a2a2a2"
        item-color="#7859ff"
        height="44"
        class="favorite-filter hover-icon-color"
      >
        <v-icon color="#7164ac" left>{{
          showOnlyFavorites ? "mdi-heart" : "mdi-heart-outline"
        }}</v-icon>
      </v-btn>

      <div class="input-selectors">
        <v-text-field
          v-model="websiteStatusStore.name"
          hide-details
          placeholder="Search"
          density="compact"
          variant="outlined"
          color="#7859ff"
          item-color="#7859ff"
          class="search-bar table-selector"
        ></v-text-field>
        <v-select
          v-model="websiteStatusStore.selectedCategory"
          label="Category"
          :items="websiteStatusStore.categories"
          density="compact"
          variant="outlined"
          color="#7859ff"
          item-color="#7859ff"
          clearable
          hide-details
          class="category-selector table-selector"
        ></v-select>
      </div>
      </div>
      <div class="last-update-cont">
        Last Updated: {{ formatDate(websiteStatusStore.lastUpdated) }}
      </div>
    </div>

    <div class="table-container">
      <div class="table-wrapper">
        <v-data-table-server
          v-model:items-per-page="websiteStatusStore.sitesPerPage"
          :search="websiteStatusStore.search"
          :headers="websiteStatusStore.headers"
          :items-length="websiteStatusStore.totalSites"
          :items="websiteStatusStore.sites"
          :loading="websiteStatusStore.loading"
          class="elevation-1 centered-headers"
          item-value="websiteStatusStore.name"
          @update:options="loadItems"
          items-per-page-text="Websites per page:"
          :items-per-page-options="websiteStatusStore.itemsPerPage"
        >
          <template v-slot:item.isFavorite="{ item }">
            <v-icon
              class="favorite-table-icon"
              color="#7164ac"
              @click="toggleFavorite(item)"
            >
              {{ item.isFavorite ? "mdi-heart" : "mdi-heart-outline" }}
            </v-icon>
          </template>
          <template v-slot:item.botsStatuses.MJ12bot.status="{ item }">
  <div v-if="item.botsStatuses.MJ12bot && item.botsStatuses.MJ12bot.status === 'Blocked'">
    <span
      :class="{
        'blocked-text': item.botsStatuses.MJ12bot.status === 'Blocked',
      }"
    >
      {{ item.botsStatuses.MJ12bot.status}}
      <v-tooltip v-if="item.botsStatuses.MJ12bot.block_date && moment(item.botsStatuses.MJ12bot.block_date.split(' ')[0], 'YYYY-MM-DD').isValid()" activator="parent" location="bottom">
        Blocked On:
        {{ formatBlockDate(item.botsStatuses.MJ12bot.block_date) }}
      </v-tooltip>
    </span>
  </div>
  <span v-else>
    {{ item.botsStatuses.MJ12bot ? item.botsStatuses.MJ12bot.status : 'Allowed' }}
  </span>
</template>
<template v-slot:item.botsStatuses.AhrefsBot.status="{ item }">
  <div v-if="item.botsStatuses.AhrefsBot && item.botsStatuses.AhrefsBot.status === 'Blocked'">
    <span
      :class="{
        'blocked-text': item.botsStatuses.AhrefsBot.status === 'Blocked',
      }"
    >
      {{ item.botsStatuses.AhrefsBot.status}}
      <v-tooltip v-if="item.botsStatuses.AhrefsBot.block_date && moment(item.botsStatuses.AhrefsBot.block_date.split(' ')[0], 'YYYY-MM-DD').isValid()" activator="parent" location="bottom">
        Blocked On:
        {{ formatBlockDate(item.botsStatuses.AhrefsBot.block_date) }}
      </v-tooltip>
    </span>
  </div>
  <span v-else>
    {{ item.botsStatuses.AhrefsBot ? item.botsStatuses.AhrefsBot.status : 'Allowed' }}
  </span>
</template>
<template v-slot:item.botsStatuses.SemrushBot.status="{ item }">
  <div v-if="item.botsStatuses.SemrushBot && item.botsStatuses.SemrushBot.status === 'Blocked'">
    <span
      :class="{
        'blocked-text': item.botsStatuses.SemrushBot.status === 'Blocked',
      }"
    >
      {{ item.botsStatuses.SemrushBot.status}}
      <v-tooltip v-if="item.botsStatuses.SemrushBot.block_date && moment(item.botsStatuses.SemrushBot.block_date.split(' ')[0], 'YYYY-MM-DD').isValid()" activator="parent" location="bottom">
        Blocked On:
        {{ formatBlockDate(item.botsStatuses.SemrushBot.block_date) }}
      </v-tooltip>
    </span>
  </div>
  <span v-else>
    {{ item.botsStatuses.SemrushBot ? item.botsStatuses.SemrushBot.status : 'Allowed' }}
  </span>
</template>
<template v-slot:item.botsStatuses.dotbot.status="{ item }">
  <div v-if="item.botsStatuses.dotbot && item.botsStatuses.dotbot.status === 'Blocked'">
    <span
      :class="{
        'blocked-text': item.botsStatuses.dotbot.status === 'Blocked',
      }"
    >
      {{ item.botsStatuses.dotbot.status}}
      <v-tooltip v-if="item.botsStatuses.dotbot.block_date && moment(item.botsStatuses.dotbot.block_date.split(' ')[0], 'YYYY-MM-DD').isValid()" activator="parent" location="bottom">
        Blocked On:
        {{ formatBlockDate(item.botsStatuses.dotbot.block_date) }}
      </v-tooltip>
    </span>
  </div>
  <span v-else>
    {{ item.botsStatuses.dotbot ? item.botsStatuses.dotbot.status : 'Allowed' }}
  </span>
</template>
<template v-slot:item.botsStatuses.rogerbot.status="{ item }">
  <div v-if="item.botsStatuses.rogerbot && item.botsStatuses.rogerbot.status === 'Blocked'">
    <span
      :class="{
        'blocked-text': item.botsStatuses.rogerbot.status === 'Blocked',
      }"
    >
      {{ item.botsStatuses.rogerbot.status}}
      <v-tooltip v-if="item.botsStatuses.rogerbot.block_date && moment(item.botsStatuses.rogerbot.block_date.split(' ')[0], 'YYYY-MM-DD').isValid()" activator="parent" location="bottom">
        Blocked On:
        {{ formatBlockDate(item.botsStatuses.rogerbot.block_date) }}
      </v-tooltip>
    </span>
  </div>
  <span v-else>
    {{ item.botsStatuses.rogerbot ? item.botsStatuses.rogerbot.status : 'Allowed' }}
  </span>
</template>
<template v-slot:item.botsStatuses.ScreamingFrogSEOSpider.status="{ item }">
  <div v-if="item.botsStatuses.ScreamingFrogSEOSpider && item.botsStatuses.ScreamingFrogSEOSpider.status === 'Blocked'">
    <span
      :class="{
        'blocked-text': item.botsStatuses.ScreamingFrogSEOSpider.status === 'Blocked',
      }"
    >
      {{ item.botsStatuses.ScreamingFrogSEOSpider.status}}
      <v-tooltip v-if="item.botsStatuses.ScreamingFrogSEOSpider.block_date && moment(item.botsStatuses.ScreamingFrogSEOSpider.block_date.split(' ')[0], 'YYYY-MM-DD').isValid()" activator="parent" location="bottom">
        Blocked On:
        {{ formatBlockDate(item.botsStatuses.ScreamingFrogSEOSpider.block_date) }}
      </v-tooltip>
    </span>
  </div>
  <span v-else>
    {{ item.botsStatuses.ScreamingFrogSEOSpider ? item.botsStatuses.ScreamingFrogSEOSpider.status : 'Allowed' }}
  </span>
</template>
<template v-slot:item.botsStatuses.cognitiveSEO.status="{ item }">
  <div v-if="item.botsStatuses.cognitiveSEO && item.botsStatuses.cognitiveSEO.status === 'Blocked'">
    <span
      :class="{
        'blocked-text': item.botsStatuses.cognitiveSEO.status === 'Blocked',
      }"
    >
      {{ item.botsStatuses.cognitiveSEO.status}}
      <v-tooltip v-if="item.botsStatuses.cognitiveSEO.block_date && moment(item.botsStatuses.cognitiveSEO.block_date.split(' ')[0], 'YYYY-MM-DD').isValid()" activator="parent" location="bottom">
        Blocked On:
        {{ formatBlockDate(item.botsStatuses.cognitiveSEO.block_date) }}
      </v-tooltip>
    </span>
  </div>
  <span v-else>
    {{ item.botsStatuses.cognitiveSEO ? item.botsStatuses.cognitiveSEO.status : 'Allowed' }}
  </span>
</template>
<template v-slot:item.botsStatuses.OnCrawl.status="{ item }">
  <div v-if="item.botsStatuses.OnCrawl && item.botsStatuses.OnCrawl.status === 'Blocked'">
    <span
      :class="{
        'blocked-text': item.botsStatuses.OnCrawl.status === 'Blocked',
      }"
    >
      {{ item.botsStatuses.OnCrawl.status}}
      <v-tooltip v-if="item.botsStatuses.OnCrawl.block_date && moment(item.botsStatuses.OnCrawl.block_date.split(' ')[0], 'YYYY-MM-DD').isValid()" activator="parent" location="bottom">
        Blocked On:
        {{ formatBlockDate(item.botsStatuses.OnCrawl.block_date) }}
      </v-tooltip>
    </span>
  </div>
  <span v-else>
    {{ item.botsStatuses.OnCrawl ? item.botsStatuses.OnCrawl.status : 'Allowed' }}
  </span>
</template>
         
         
         
        </v-data-table-server>

      </div>
      <div class="custom-search-cont">        <v-btn
          class="custom-search-btn csv-btn"
          variant="outlined"
          color="#7859ff"
          height="44"
          @click="websiteStatusStore.fetchDataAndDownloadCSV"
          ><v-icon class="custom-search-icon">mdi-download</v-icon> Download CSV
        </v-btn>
        <v-btn
          class="custom-search-btn"
          :onClick="searchStore.toggleSearch"
          variant="outlined"
          color="#7859ff"
          height="44"
          ><v-icon class="custom-search-icon">mdi-search-web</v-icon> Custom
          Search</v-btn
        >
        <div class="custom-search-bar">
          <v-text-field
            v-model="searchStore.searchQuery"
            v-if="searchStore.showSearch"
            :loading="searchStore.searchLoading"
            color="#7859ff"
            density="compact"
            label="Enter a website..."
            append-inner-icon="mdi-search-web"
            single-line
            hide-details
            @click:append-inner="searchStore.fetchSearch"
            @keydown.enter="searchStore.fetchSearch"
          ></v-text-field>
        </div>
        <v-alert
          class="error-message"
          variant="outlined"
          density="compact"
          v-if="searchStore.searchError"
          :text="searchStore.searchError"
          icon="$error"
          color="error"
          max-height="44"
          max-width="45%"
          closable
        ></v-alert>
      </div>
    </div>
  </div>
</template>

<script>
import {
  VDataTable,
  VDataTableServer,
  VDataTableVirtual,
} from "vuetify/labs/VDataTable";
import { useWebsiteStatusStore } from "../stores/websiteStatusStore";
import { useSearchStore } from "../stores/searchStore";
import { watch, ref } from "vue";
import { formatBlockDate } from "../utils/tableHelper";
import moment from 'moment';

export default {
  setup() {
    const websiteStatusStore = useWebsiteStatusStore();
    const searchStore = useSearchStore();
    const showOnlyFavorites = ref(false);

    const loadItems = ({ page, itemsPerPage, sortBy }) => {
      const sortColumn = sortBy && sortBy.length > 0 ? sortBy[0].key : null;
      const sortOrder = sortColumn ? sortBy[0].order : null;

      websiteStatusStore.currentParameters = {
        page: page,
        sitesPerPage: itemsPerPage,
        sortBy: sortColumn,
        sortOrder: sortOrder,
        search: websiteStatusStore.name,
        category: websiteStatusStore.selectedCategory,
      };

      websiteStatusStore.fetchStatuses(websiteStatusStore.currentParameters);
    };

    websiteStatusStore.fetchCategories();

    // toggle favorites on click
    const toggleFavorite = (site) => {
      site.isFavorite = !site.isFavorite;

      const favorites = websiteStatusStore.sites
        .filter((s) => s.isFavorite)
        .map((s) => s.url);
      localStorage.setItem("favorites", JSON.stringify(favorites));
    };

    // favorites button toggle
    const toggleFavoritesOnly = () => {
      // console.log("Toggle Favorites Only");
      showOnlyFavorites.value = !showOnlyFavorites.value;
      if (showOnlyFavorites.value) {
        websiteStatusStore.fetchStatuses({
          ...websiteStatusStore.currentParameters,
          onlyFavorites: true,
        });
      } else {
        websiteStatusStore.fetchStatuses(websiteStatusStore.currentParameters);
      }
    };

    // format Date
    const formatDate = (dateStr) => {
      if (!dateStr) return "";
      const [, month, day] = dateStr.split("-");

      const monthNames = [
        "Jan.",
        "Feb.",
        "Mar.",
        "Apr.",
        "May",
        "Jun.",
        "Jul.",
        "Aug.",
        "Sep.",
        "Oct.",
        "Nov.",
        "Dec.",
      ];
      const monthStr = monthNames[parseInt(month, 10) - 1];

      return `${monthStr} ${parseInt(day, 10)}`;
    };

    watch(
      () => websiteStatusStore.name,
      () => {
        websiteStatusStore.search = String(Date.now());
      }
    );

    watch(
      () => websiteStatusStore.selectedCategory,
      () => {
        websiteStatusStore.search = String(Date.now());
      }
    );

    watch(
      () => searchStore.searchError,
      () => {
        // console.log("searchError updated:", newValue);
      }
    );

    return {
      websiteStatusStore,
      searchStore,
      loadItems,
      toggleFavorite,
      toggleFavoritesOnly,
      formatDate,
      formatBlockDate,
      showOnlyFavorites,
      moment
    };
  },
  components: {
    VDataTable,
    VDataTableServer,
    VDataTableVirtual,
  },

};
</script>

<style scoped>
.chart-container {
  margin-top: 5vh;
  position: relative;
}

.table-container {
  display: flex;
  flex-direction: column;
}

.table-wrapper {
  position: relative;
}

.csv-dl-btn {
  position: absolute;
  bottom: 5px;
  left: 5px;
  z-index: 1;
  background-color: #7859ff;
  color: white !important;
  font-weight: 600;
  border: 1px solid #7859ff;
}

.custom-search-cont {
  display: flex;
  flex-direction: row;
  align-items: center;
  margin-top: 20px;
  width: 100%;
}
.hover-icon-color:hover > v-icon {
  color: #7164ac !important;
}
.table-options {
  display: flex;
  flex-direction: row;
  align-items: center;
  margin: 20px 0;
  width: 100%;
}

.filters{
  display: flex;
  flex-direction: row;
  align-items: center;
}

.input-selectors {
  display: flex;
  flex-direction: row;
  width: 77%;
  margin: 0 10px;
}

.search-bar,
.category-selector {
  max-width: 30%;
  margin: 0 10px;
}

.table-selector {
  min-width: 150px;
}

.last-update-cont {
  position: absolute;
  right: 0;
  margin-right: 5px;
}

.favorite-table-icon:hover {
  opacity: 0.75;
}

.custom-search-icon {
  margin-right: 5px;
}

.custom-search-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  align-self: flex-start;
  z-index: 10;
  margin: 0 5px;
  margin-left: 0;
}

.custom-search-btn:hover {
  opacity: 0.75;
}

.custom-search-bar {
  align-self: flex-start;
  width: 30%;
  margin: 0 5px;
}

.error-message {
  width: 20%;
  align-self: center;
}

.blocked-text {
  font-weight: 600;
  color: #7859ff;
}

.blocked-text:hover {
  opacity: 0.85;
  cursor: pointer;
}

@media (max-width: 768px) {

  .chart-container{
    margin-top: 2vh;
  }
  .table-container {
    font-size: 14px;
  }

  .table-wrapper {
    overflow-x: auto;
  }
  .table-options {
    flex-direction: column;
    margin-bottom: 0;
  }

  .last-update-cont {
    position: static;
    width: 100%;
    text-align: right;
    margin-top: 10px;

  }

  .filters {
    width: 100%;
  }

  .input-selectors {
    width: 100%;
    justify-content: space-between;
    margin-right: 0;
  }

  .table-selector {
    min-width: 50px;
  }
  .search-bar,
  .category-selector {
    max-width: 100%;
    margin: 0 5px;
  }

  .custom-search-cont {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  .custom-search-bar, .custom-search-btn, .error-message {
    width: 100%;
    margin: 5px 0;
  }

  .custom-search-bar {
    margin-top: 15px;
  }

  .error-message {
    max-width: 100% !important;
    margin-top: 15px;
    font-size: 12px;
  }
}
</style>
