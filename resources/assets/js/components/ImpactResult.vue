<template>
    <div class="groot">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary text-white">Impact Result</div>
                <div class="card-body">
                    <h3 class="card-title">Project Name : <a :href="'/project/'+projectName"> {{projectName }} </a></h3>
                    <h5 class="card-subtitle text-muted">Change Request Id : {{ changeRequestId}}</h5>
                    <h5 class="card-subtitle text-muted">Status : <span class="text-success">Success</span></h5>
                    <hr>
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-fr-tab" data-toggle="pill" href="#pills-fr" role="tab" aria-controls="pills-home" aria-selected="true">
                                Functional Requirements
                                <span class="badge badge-light">{{ impact.fr.length }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-schema-tab" data-toggle="pill" href="#pills-schema" role="tab" aria-controls="pills-schema" aria-selected="false">
                                Database Schema
                                <span class="badge badge-light">{{ impact.schema.length }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-instance-tab" data-toggle="pill" href="#pills-instance" role="tab" aria-controls="pills-instance" aria-selected="false">
                                Database Instance
                                <span class="badge badge-light">{{ impact.instance.length }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-tc-tab" data-toggle="pill" href="#pills-tc" role="tab" aria-controls="pills-tc" aria-selected="false">
                                Test Cases
                                <span class="badge badge-light">{{ impact.tc.length }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-rtm-tab" data-toggle="pill" href="#pills-rtm" role="tab" aria-controls="pills-rtm" aria-selected="false">
                                Requirement Traceability Matrix
                                <span class="badge badge-light">{{ impact.rtm.length }}</span>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-fr" role="tabpanel" aria-labelledby="pills-fr-tab">
                            <div class="card-hr" v-for="(fr,index) in impact.fr" :key="index">
                                <div class="card">
                                    <div class="card-header">
                                        {{ fr.functionalRequirementNo }}
                                        <span class="badge badge-info">{{ fr.inputs.length }}</span>
                                    </div>
                                    <div class="card-body">
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
                                                    <th>ChangeType</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(input, inputIndex) in fr.inputs" :key="inputIndex">
                                                    <td>{{ inputIndex+1 }}</td>
                                                    <td>{{ input.name }} </td>
                                                    <td>{{ input.changeType == 'delete' ? input.old.dataType : input.new.dataType }}</td>
                                                    <td>{{ input.changeType == 'delete' ? input.old.length : input.new.length }} </td>
                                                    <td>{{ input.changeType == 'delete' ? input.old.precision : input.new.precision }}</td>
                                                    <td>{{ input.changeType == 'delete' ? input.old.scale : input.new.scale }}</td>
                                                    <td>{{ input.changeType == 'delete' ? input.old.default : input.new.default }}</td>
                                                    <td v-if="input.changeType != 'delete'" v-bind:class="[input.new.nullable == 'N' ? 'text-danger' : 'text-success']">{{ input.new.nullable }}</td>
                                                    <td v-else v-bind:class="[input.old.nullable == 'N' ? 'text-danger' : 'text-success']">{{ input.old.nullable }}</td>
                                                    <td v-if="input.changeType != 'delete'" v-bind:class="[input.new.unique == 'N' ? 'text-danger' : 'text-success']">{{ input.new.unique }}</td>
                                                    <td v-else v-bind:class="[input.old.unique == 'N' ? 'text-danger' : 'text-success']">{{ input.old.unique }}</td>
                                                    <td>{{ input.changeType == 'delete' ? input.old.min : input.new.min }}</td>
                                                    <td>{{ input.changeType == 'delete' ? input.old.max : input.new.max }}</td>
                                                    <td>{{ input.changeType == 'delete' ? input.old.tableName : input.new.tableName }}</td>
                                                    <td>{{ input.changeType == 'delete' ? input.old.columnName : input.new.columnName }}</td>
                                                    <td>
                                                        <span class="badge" v-bind:class="[input.changeType == 'add' ? 'badge-success' : input.changeType == 'edit' ? 'badge-warning' : 'badge-danger']">{{ input.changeType }}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <hr v-if="impact.fr.length > 1">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-schema" role="tabpanel" aria-labelledby="pills-schema-tab">
                            <div class="card-hr" v-for="(table,index) in impact.schema" :key="index">
                                <div class="card">
                                    <div class="card-header">
                                        {{ table.name }}
                                        <span class="badge badge-info">{{ table.columnList.length }}</span>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr class="bg-info text-white">
                                                    <th></th>
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
                                                    <th>ChangeType</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(column, columnIndex) in table.columnList" :key="columnIndex">
                                                    <td>{{ columnIndex+1 }}</td>
                                                    <td>{{ column.name }} </td>
                                                    <td>{{ column.changeType == 'delete' ? column.old.dataType : column.new.dataType }}</td>
                                                    <td>{{ column.changeType == 'delete' ? column.old.length : column.new.length }} </td>
                                                    <td>{{ column.changeType == 'delete' ? column.old.precision : column.new.precision }}</td>
                                                    <td>{{ column.changeType == 'delete' ? column.old.scale : column.new.scale }}</td>
                                                    <td>{{ column.changeType == 'delete' ? column.old.default : column.new.default }}</td>
                                                    <td v-if="column.changeType != 'delete'" v-bind:class="[column.new.nullable == 'N' ? 'text-danger' : 'text-success']">{{ column.new.nullable }}</td>
                                                    <td v-else v-bind:class="[column.old.nullable == 'N' ? 'text-danger' : 'text-success']">{{ column.old.nullable }}</td>
                                                    <td v-if="column.changeType != 'delete'" v-bind:class="[column.new.unique == 'N' ? 'text-danger' : 'text-success']">{{ column.new.unique }}</td>
                                                    <td v-else v-bind:class="[column.old.unique == 'N' ? 'text-danger' : 'text-success']">{{ column.old.unique }}</td>
                                                    <td>{{ column.changeType == 'delete' ? column.old.min : column.new.min }}</td>
                                                    <td>{{ column.changeType == 'delete' ? column.old.max : column.new.max }}</td>
                                                    <td>
                                                        <span class="badge" v-bind:class="[column.changeType == 'add' ? 'badge-success' : column.changeType == 'edit' ? 'badge-warning' : 'badge-danger']">{{ column.changeType }}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <hr v-if="impact.schema.length > 1">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-instance" role="tabpanel" aria-labelledby="pills-instance-tab">...</div>
                        <div class="tab-pane fade" id="pills-tc" role="tabpanel" aria-labelledby="pills-tc-tab">
                            <div class="row">
                                <div class="col-md-4" v-for="(tc,index) in impact.tc" :key="index">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ tc.no }}
                                                <span class="badge" v-bind:class="[tc.changeType == 'add' ? 'badge-success' : 
                                                    tc.changeType == 'edit' ? 'badge-warning' : 'badge-danger']">
                                                    {{ tc.changeType }}
                                                </span>
                                                <div class="float-right">
                                                    T
                                                </div>
                                            </h5>

                                        </div>
                                        
                                    </div>
                                    <br v-if="impact.tc.length > 3">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-rtm" role="tabpanel" aria-labelledby="pills-rtm-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr class="bg-info text-white">
                                                        <th></th>
                                                        <th>Functional Requirement No</th>
                                                        <th>
                                                            
                                                        </th>
                                                        <th>Test Case No</th>
                                                        <th>Change Type</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(rtm,index) in impact.rtm" :key="index">
                                                        <td>{{index+1}}</td>
                                                        <td>{{ rtm.functionalRequirementNo }}</td>
                                                        <td><i class="fas" v-bind:class="[rtm.changeType == 'add' ? 'fa-link' : 'fa-unlink']"></i></td>
                                                        <td>{{ rtm.testCaseNo }}</td>
                                                        <td>
                                                            <span class="badge" v-bind:class="[rtm.changeType == 'add' ? 'badge-success' : 'badge-danger']">
                                                                {{ rtm.changeType }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
  name: "impact-result",
  props: ["accessToken", "projectName", "changeRequestId"],
  data() {
    return {
      impact: {
        schema: "",
        instance: "",
        fr: "",
        tc: "",
        rtm: ""
      }
    };
  },
  methods: {
    getImpact() {
      let vm = this;
      axios({
        url:
          "/api/v1/projects/" +
          this.projectName +
          "/changeRequests/" +
          this.changeRequestId,
        method: "GET",
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
          vm.impact.schema = response.data.schema;
          vm.impact.instance = response.data.instance;
          vm.impact.fr = response.data.functionalRequirments;
          vm.impact.tc = response.data.testCases;
          vm.impact.rtm = response.data.rtm;
          console.log(vm.impact);
        })
        .catch(function(errors) {});
    }
  },
  created() {
    this.getImpact();
  }
};
</script>
