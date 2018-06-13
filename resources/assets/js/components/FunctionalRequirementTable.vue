<template>
    <div class="groot">
        <div v-for="(fr,index) in frs" :key="index" v-if="index >= startIndex && index < startIndex+perPage">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    No. {{ fr.no }}
                </div>
                <div class="card-body">
                    <p> {{ fr.description }} </p>
                    <hr>
                    <table class="table table-hover">
                        <thead>
                            <tr class="bg-info text-white">
                                <th></th>
                                <th>Name</th>
                                <th>DataType</th>
                                <th>Length</th>
                                <th>Precision</th>
                                <th>Scale</th>
                                <th>Default</th>
                                <th>Nullable</th>
                                <th>Unique</th>
                                <th>Min</th>
                                <th>Max</th>
                                <th>Table name</th>
                                <th>Column name</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(input,indexInput) in fr.inputs" :key="indexInput">
                                <td>{{ indexInput+1 }}</td>
                                <td>{{ input.name }} </td>
                                <td>{{ input.dataType }}</td>
                                <td>{{ input.length }} </td>
                                <td>{{ input.precision }}</td>
                                <td>{{ input.scale }}</td>
                                <td>{{ input.default }}</td>
                                <td v-bind:class="[input.nullable == 'N' ? 'text-danger' : 'text-success']">{{ input.nullable }}</td>
                                <td v-bind:class="[input.unique == 'N' ? 'text-danger' : 'text-success']">{{ input.unique }}</td>
                                <td>{{ input.min }}</td>
                                <td>{{ input.max }}</td>
                                <td>{{ input.tableName }}</td>
                                <td>{{ input.columnName }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
        </div>
        <nav aria-label="Page navigation example" v-if="this.frs.length > this.perPage">
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
  name: "functional-requirement-table",
  props: ["frs"],
  data() {
    return {
      startIndex: 0,
      pages: 1,
      active: 1,
      perPage: 2
    };
  },
  created() {
    if (this.frs.length % 2 == 0) {
      this.pages = parseInt(this.frs.length / this.perPage);
    } else {
      this.pages = parseInt(this.frs.length / this.perPage) + 1;
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
