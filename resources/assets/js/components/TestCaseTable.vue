<template>
    <div class="groot">
        <div class="row">
            <div class="col-md-6" v-for="(tc,index) in tcs" :key="index" v-if="index >= startIndex && index < startIndex+perPage">
                <div class="card">
                    <div class="card-header">{{ tc.no }} <span class="badge" v-bind:class="[tc.type.toLowerCase() == 'valid' ? 'badge-success' : 'badge-danger' ]">{{ tc.type }}</span></div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr class="bg-info text-white">
                                    <th>Input name</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(input,inputIndex) in tc.inputs" :key="inputIndex">
                                    <td>{{ input.name }}</td>
                                    <td>{{ input.testData }}</td>
                                </tr>
                            </tbody>
                        </table>
    
                    </div>
                </div>
                <br>
            </div>
        </div>
        <nav aria-label="Page navigation example" v-if="this.tcs.length > this.perPage">
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
  name: "test-case-table",
  props: ["tcs"],
  data() {
    return {
      startIndex: 0,
      pages: 1,
      active: 1,
      perPage: 4
    };
  },
  created() {
    if (this.tcs.length % 2 == 0) {
      this.pages = parseInt(this.tcs.length / this.perPage);
    } else {
      this.pages = parseInt(this.tcs.length / this.perPage) + 1;
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
