<template>
  <div class="app-container">
    <BlockedChart />
    <WebsiteTable />
    <SearchResult />
  </div>
</template>

<script setup>
import BlockedChart from "./components/BlockedChart.vue";
import SearchResult from "./components/SearchResult.vue";
import WebsiteTable from "./components/WebsiteTable.vue";
</script>

<style scoped>
.app-container {
  width: 100%;
}
</style>
