<template>
    <div class="groot">
        <div class="card" ref="messages">
            <div class="card-header">
                <h4>
                    <i class="fas fa-database"></i>&nbsp;&nbsp;{{ this.projectName }}</h4>
            </div>
            <div class="card-body">
                <div class="row sticky-top">
                    <div class="col-md-2 offset-md-10">
                        <select class="form-control" @change="goto" v-model="gotoId">
                            <option value='-1' selected> Go to Table </option>
                            <option :value="index" v-for="(table, index) in this.content" :key="index">{{ table.name }}</option>
                        </select>
                    </div>
                </div>
                <hr>
                <div class="card-br" v-for="(table, index) in this.content" :key="index" :id="index">
                    <div class="card">
                        <div class="card-header">
                            <span class="align-middle">
                                <div class="float-left">
                                    <span class="btn" @click="scrollToTop(index)">{{ table.name }}</span>
                                </div>
                                <div class="float-right">
                                    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                        <button type="button" class="btn btn-primary" @click="statusShow[index].isShow = !statusShow[index].isShow">
                                            <i class="fas" v-bind:class="[statusShow[index].isShow ? 'fa-eye-slash' : 'fa-eye']"></i>
                                        </button>

                                        <div class="btn-group" role="group">
                                            <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="true">
                                                {{ statusShow[index].thingShow == 0 ? 'Column' : statusShow[index].thingShow == 1 ? 'Constraint' : 'Instance' }}
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                <a class="dropdown-item" href="#" @click.prevent="statusShow[index].thingShow = 0 ; statusShow[index].isShow = true">Column</a>
                                                <a class="dropdown-item" href="#" @click.prevent="statusShow[index].thingShow = 1 ; statusShow[index].isShow = true">Constraint</a>
                                                <a class="dropdown-item" href="#" @click.prevent="statusShow[index].thingShow = 2 ; statusShow[index].isShow = true">Instance</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </span>

                        </div>
                        <div class="card-body" v-if="statusShow[index].isShow">
                            <table class="table table-hover" v-if="statusShow[index].thingShow == 0">
                                <thead>
                                    <tr class="bg-info text-white">
                                        <th>Column Name</th>
                                        <th>DataType</th>
                                        <th>Length</th>
                                        <th>Precision</th>
                                        <th>Scale</th>
                                        <th>Default</th>
                                        <th>Nullable</th>
                                        <th>Unique</th>
                                        <th>Min</th>
                                        <th>Max</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(column, indexColumn) in table.columns" :key="indexColumn">
                                        <td>{{ column.name }}
                                            <span v-if="column.isPK" class="badge badge-secondary">PK</span>
                                            <span v-if="column.isFK" class="badge badge-secondary">FK</span>
                                        </td>
                                        <td>{{ column.dataType }}</td>
                                        <td>{{ column.length }}</td>
                                        <td>{{ column.precision }}</td>
                                        <td>{{ column.scale }}</td>
                                        <td>{{ column.default }}</td>
                                        <td v-bind:class="[column.nullable == 'N' ? 'text-danger' : 'text-success']">{{ column.nullable }}</td>
                                        <td v-bind:class="[column.unique == 'N' ? 'text-danger' : 'text-success']">{{ column.unique }}</td>
                                        <td>{{ column.min ? column.min.value : ''}}</td>
                                        <td>{{ column.max ? column.max.value : ''}}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="statusShow[index].thingShow == 2">
                                <table class="table table-hover table-bordered" v-if="table.instance">
                                    <thead>
                                        <tr class="bg-info text-white">
                                            <td v-for="(columnOrder,columnOrderIndex) in table.instance.columnOrder" :key="columnOrderIndex">
                                                {{ columnOrder }}
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(record, recordIndex) in table.instance.records" :key="recordIndex">
                                            <td v-for="(value, valueIndex) in record" :key="valueIndex">
                                                {{ value ? value : 'null' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p v-else> Not found instance.</p>
                            </div>
                            <div v-if="statusShow[index].thingShow == 1">

                                <div class="row" v-if="table.constraints">
                                    <div class="col-md-12" v-if="table.constraints.PK">
                                        <h4 class="text-primary">Primary Key</h4>
                                        <p>Columns:
                                            <span class="badge badge-secondary" v-for="(pkColumn, pkIndex) in table.constraints.PK.columns" :key="pkIndex">
                                                {{ pkColumn }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-12" v-if="table.constraints.FKs">
                                        <hr>
                                        <h4 class="text-primary">Foreign Keys
                                            <span class="badge badge-secondary">{{ table.constraints.FKs.length}} </span>
                                        </h4>
                                        <div class="row">
                                            <div class="col-md-12" v-for="(fk, fkIndex) in table.constraints.FKs" :key="fkIndex">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <strong>{{ fk.name }}</strong>

                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr class="table-dark">
                                                                    <td class="bg-info">Referencing Table</td>
                                                                    <td class="bg-info">Column</td>
                                                                    <!-- <td>to</td> -->
                                                                    <td>Referenced Table</td>
                                                                    <td>Column</td>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr v-for="(fkLink, fkLinkIndex) in fk.links" :key="fkLinkIndex">
                                                                    <td>{{ fkLink.from.tableName }}</td>
                                                                    <td>{{ fkLink.from.columnName }}</td>
                                                                    <!-- <td></td> -->
                                                                    <td>{{ fkLink.to.tableName }}</td>
                                                                    <td>{{ fkLink.to.columnName }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <br>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-12" v-if="table.constraints.checks">
                                        <hr>
                                        <h4 class="text-primary">Check Constraints
                                            <span class="badge badge-secondary">{{ table.constraints.checks.length}} </span>
                                        </h4>
                                        <div class="row">
                                            <div class="col-md-6" v-for="(ck, ckIndex) in table.constraints.checks" :key="ckIndex">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <strong>{{ ck.name }}</strong>

                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr class="table-dark">
                                                                    <td class="bg-info">Column</td>
                                                                    <td class="bg-info">Min</td>
                                                                    <td class="bg-info">Max</td>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr v-for="(ckColumn, ckColumnIndex) in ck.columns" :key="ckColumnIndex">
                                                                    <td>{{ ckColumn }}</td>
                                                                    <td>{{ (ckColumn in ck.mins ? ck.mins[ckColumn].value : '-') }}</td>
                                                                    <td>{{ (ckColumn in ck.maxs ? ck.maxs[ckColumn].value : '-') }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <br>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-12" v-if="table.constraints.uniques">
                                        <hr>
                                        <h4 class="text-primary">Unique Constraints
                                            <span class="badge badge-secondary">{{ table.constraints.uniques.length}} </span>
                                        </h4>
                                        <div class="row">
                                            <div class="col-md-4" v-for="(unique, uniIndex) in table.constraints.uniques" :key="uniIndex">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <strong>{{ unique.name }}</strong>
                                                        <hr>
                                                        <p>Columns:
                                                            <span class="badge badge-secondary" v-for="(uniqueColumn, uniqueColumnIndex) in unique.columns" :key="uniqueColumnIndex">
                                                                {{ uniqueColumn }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <br>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <br>
                </div>

            </div>
        </div>
    </div>
</template>
<script>
export default {
  name: "database-table",
  props: ["accessToken", "projectName"],
  data() {
    return {
      content: [],
      statusShow: [{ isShow: true, thingShow: 0 }],
      show: true,
      gotoId: -1
    };
  },
  methods: {
    goto() {
      if (this.gotoId != -1) {
        this.statusShow[this.gotoId].isShow = true;
        this.scrollToTop(this.gotoId);
        this.gotoId = -1;
      }
    },
    scrollToTop(id) {
      $("html, body").animate(
        {
          scrollTop: $("#" + id).offset().top
        },
        1000
      );
    },
    getDatabase() {
      let vm = this;
      axios({
        url: "/api/v1/projects/" + this.projectName + "/databases",
        method: "GET",
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
          for (let index = 0; index < response.data.length; index++) {
            vm.statusShow.push({ isShow: true, thingShow: 0 });
          }
          vm.content = response.data;
          console.log(vm.content);
        })
        .catch(function(errors) {});
    }
  },
  created() {
    this.getDatabase();
  }
};
</script>
