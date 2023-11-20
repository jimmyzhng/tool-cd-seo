<template>
  <div class="expansion-panel-cont">
    <v-expansion-panels>
      <v-expansion-panel>
        <v-expansion-panel-title>
          Referencing our study in your work?
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <p>Use the following citation format:</p>
          <v-code>
            @misc{crawlerstudy2023originality, <br />
            title = {The Percent of the Top 1000 Websites Blocking AI Web
            Crawlers},
            <br />
            author = {Originality.AI}, <br />
            year = {2023}, <br />
            url = {"https://originality.ai/blog/study-websites-blocking-gptbot"}
            <br />
            }
          </v-code>
          <p>
            For more information, please visit our contact page
            <a :href="contactPage" target="_blank">here</a>.
          </p>
        </v-expansion-panel-text>
      </v-expansion-panel></v-expansion-panels
    >
  </div>
</template>

<script>
export default {
  data() {
    return {
      contactPage: "https://originality.ai/contact-us",
    };
  },
};
</script>

<style scoped>
.expansion-panel-cont {
  width: 100%;
  margin-bottom: 20px;
  color: purple;
}
p {
  padding: 7.5px;
}
</style>
