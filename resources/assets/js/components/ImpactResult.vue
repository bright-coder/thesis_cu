<template>
    <div class="groot">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary text-white">Impact Result</div>
                <div class="card-body">
                    <h3 class="card-title">Project Name :
                        <a :href="'/project/'+projectName"> {{projectName }} </a>
                    </h3>
                    <h5 class="card-subtitle text-muted">Change Request Id : {{ changeRequestId}}</h5>
                    <h5 class="card-subtitle text-muted">Status :
                        <span class="text-success">{{ status }}</span>
                    </h5>
                    <br>
                    <div class="card">
                        <div class="card-header">Change Request Input List</div>
                        <div class="card-body">
                            <table class="table hover">
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(crInput, index) in crInputList" :key="index">
                                        <td>{{ index+1 }}</td>
                                        <td>{{ crInput.name }} </td>
                                        <td>{{ crInput.dataType }}</td>
                                        <td>{{ crInput.length }} </td>
                                        <td>{{ crInput.precision }}</td>
                                        <td>{{ crInput.scale }}</td>
                                        <td>{{ crInput.default }}</td>
                                        <td v-bind:class="[crInput.nullable == 'N' ? 'text-danger' : 'text-success']">{{ crInput.nullable }}</td>
                                        <td v-bind:class="[crInput.unique == 'N' ? 'text-danger' : 'text-success']">{{ crInput.unique }}</td>
                                        <td>{{ crInput.min }}</td>
                                        <td>{{ crInput.max }}</td>
                                        <td>{{ crInput.tableName }}</td>
                                        <td>{{ crInput.columnName }}</td>
                                        <td>
                                            <span class="badge" v-bind:class="[crInput.changeType == 'add' ? 'badge-success' : crInput.changeType == 'edit' ? 'badge-warning' : 'badge-danger']">{{ crInput.changeType }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <br>
                    <br>
                    <h5 class="card-subtitle">Impact Information</h5>
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
                        <div class="tab-pane fade" id="pills-instance" role="tabpanel" aria-labelledby="pills-instance-tab">
                            <div class="card">
                                <div class="card-body">
                                    <h5>
                                        <span class="text-success">
                                            <i class="fas fa-square-full"></i>
                                        </span> Add&nbsp;&nbsp;&nbsp;
                                        <span class="text-warning">
                                            <i class="fas fa-square-full"></i>
                                        </span> Edit&nbsp;&nbsp;&nbsp;
                                        <span class="text-danger">
                                            <i class="fas fa-square-full"></i>
                                        </span> Delete
                                    </h5>
                                </div>
                            </div>

                            <br>
                            <div class="card-hr" v-for="(instance,index) in impact.instance" :key="index">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Impacted by Change Request Input : {{ getNoCrInput(instance.crInputId) }}</h5>
                                        <hr>
                                        <div class="card-br" v-for="(table, tableIndex) in instance.tableImpactList" :key="tableIndex">
                                            <div class="card">
                                                <div class="card-header">{{ table.tableName }}</div>
                                                <div class="card-body">
                                                    <table class="table table-hover table-bordered">
                                                        <thead>
                                                            <tr class="bg-info text-white">
                                                                <td v-for="(columnName, cNameIndex) in table.columnOrder" :key="cNameIndex" v-bind:class="[
                                                                        table.columnName == columnName && table.changeType == 'edit' ? 'bg-warning' :
                                                                        table.columnName == columnName && table.changeType == 'delete' ? 'bg-danger' : ''
                                                                    ]">
                                                                    {{ columnName }}
                                                                </td>
                                                                
                                                                <td v-if="table.changeType == 'add'" class="bg-success"> {{ table.columnName }}</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="(oldRecords, oldIndex) in table.records.old" :key="oldIndex">
                                                                <td v-for="(oldValue, oldValueIndex) in oldRecords" :key="oldValueIndex">
                                                                    {{ oldValue }}
                                                                    <span v-if="table.changeType == 'edit' && isColumnImpact(index,tableIndex ,oldValueIndex)">
                                                                        &nbsp;
                                                                        <span class="text-warning">
                                                                            <i class="fas fa-arrow-right"></i>
                                                                        </span>
                                                                        &nbsp;{{table.records.new[oldIndex]}}
                                                                    </span>
                                                                </td>
                                                                <td v-if="table.changeType == 'add'"> {{ table.records.new[oldIndex] }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                </div>
                                            </div>

                                            <br v-if="instance.tableImpactList.length-1 != tableIndex">
                                        </div>

                                    </div>
                                </div>
                                <hr v-if="impact.instance.length > 0 && index != impact.instance.length -1">
                            </div>

                        </div>
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
                                            </h5>
                                            <div class="card-hr" v-if="tc.changeType == 'edit'">
                                                <hr>
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr class="bg-info text-white">
                                                            <td>Input Name</td>
                                                            <td>Data</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr  v-for="(tcInputEdit, tcInputIndex) in tc.inputs" :key="tcInputIndex">
                                                            <td>{{ tcInputEdit.name }}</td>
                                                            <td>
                                                                {{ tcInputEdit.oldData }}
                                                                &nbsp;
                                                                        <span class="text-warning">
                                                                            <i class="fas fa-arrow-right"></i>
                                                                        </span>
                                                                        &nbsp;{{tcInputEdit.newData}}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

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
                                                        <td>
                                                            <i class="fas" v-bind:class="[rtm.changeType == 'add' ? 'fa-link' : 'fa-unlink']"></i>
                                                        </td>
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
      status: "",
      crInputList: [],
      columnImpactEditIndex: 0,
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
          vm.impact.schema = response.data.impactList.schema;
          vm.impact.instance = response.data.impactList.instance;
          vm.impact.fr = response.data.impactList.functionalRequirments;
          vm.impact.tc = response.data.impactList.testCases;
          vm.impact.rtm = response.data.impactList.rtm;
          vm.status = response.data.status;
          vm.crInputList = response.data.crInputList;
          console.log(response.data);
          
        })
        .catch(function(errors) {});
    },
    isColumnImpact(instanceIndex ,tableIndex, columnIndex) {
        let impactIndex = -1;
        for(let i = 0 ; i < this.impact.instance[instanceIndex].tableImpactList[tableIndex].columnOrder.length ; ++i){
            if(this.impact.instance[instanceIndex].tableImpactList[tableIndex].columnName == this.impact.instance[instanceIndex].tableImpactList[tableIndex].columnOrder[i]) {
                impactIndex = i
                break
            }
        }
        return impactIndex == columnIndex
    },
    getNoCrInput(crInputId) {

        for(let i = 0 ; i < this.crInputList.length; ++i) {
            if(crInputId == this.crInputList[i].id) {
                return i+1;
            }
        }
        return -1;
    }
  },
  created() {
    this.getImpact();
  }
};
</script>
