<template>
    <div class="groot">
        <table class="table table-hover">
            <thead>
                <tr class="bg-info text-white">
                    <th>Functional Requirement No</th>
                    <th>Test Case No</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(rel, index) in relations" :key="index" v-if="index >= startIndex && index < startIndex+perPage">
                    <td width="30%">{{rel.functionalRequirementNo}}</td>
                    <td>{{rel.testCaseNos.join(" | ") }}</td>
                </tr>
            </tbody>
        </table>
        <br>
        <nav aria-label="Page navigation example" v-if="this.relations.length > this.perPage">
            <ul class="pagination justify-content-center">
                <li class="page-item" v-bind:class="{'disabled' :active == 1}">
                    <button class="page-link" tabindex="-1" @click="previous">Previous</button>
                </li>
                <li class="page-item" v-for="page in pages" :key="page" v-bind:class="{'active': active == page}">
                    <button class="page-link" @click="go(page)">{{ page }}</button>
                </li>
                <li class="page-item" v-bind:class="{'disabled' :active == pages}">
                    <button class="page-link" @click="next">Next</button>
                </li>
            </ul>
        </nav>
    </div>
</template>
<script>
export default {
  name: "rtm-table",
  props: ["relations"],
  data() {
    return {
      startIndex: 0,
      pages: 1,
      active: 1,
      perPage: 20
    };
  },
  created() {
    if (this.relations.length % 2 == 0) {
      this.pages = parseInt(this.relations.length / this.perPage);
    } else {
      this.pages = parseInt(this.relations.length / this.perPage) + 1;
    }
  },
  methods: {
    go(page) {
      this.startIndex = (page - 1) * 2;
      this.active = page;
    },
    previous() {
      if (this.active > 1) {
        --this.active;
        this.startIndex -= this.perPage;
      }
    },
    next() {
      if (this.active < this.pages) {
        ++this.active;
        this.startIndex += this.perPage;
      }
    }
  }
};
</script>

