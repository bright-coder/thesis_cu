<template>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-success text-white">
                New Change Request
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="project">Project</label>
                            <select class="form-control" id="project" v-model="selectedProject" @change="getFunctionalList">
                                <option value="-"> - </option>
                                <option v-for="(project, index) in projectList" :key="index" :value="project.name">
                                    {{ project.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3" v-if="isHaveFr">
                        <div class="form-group" v-if="functionalList.length > 0">
                            <label for="project">Functional Requiremnt</label>
                            <select class="form-control" v-model="selectedFunctional" @change="resetFunctional">
                                <option value="-"> - </option>
                                <option v-for="(functional, index) in functionalList" :key="index" :value="index">
                                    {{ functional.no }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3" v-else>
                        <label for="project">Functional Requirement</label><br> Please
                        <a href="/project">upload</a> functional requirement first.
                    </div>
                </div>
                <div v-if="selectedFunctional != '-'">
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
                                <th>
                                    <button class="btn btn-success" data-toggle="modal" data-target="#modal" @click="newInput">Add new input</button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(input,indexInput) in functionalList[selectedFunctional].inputs" :key="indexInput">
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
                                <td>
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#modal" @click="editInput(indexInput)" v-if="!(input.name in changeRequestIndex)">Edit</button>
                                    <button class="btn btn-danger" data-toggle="modal" data-target="#modal" @click="deleteInput(indexInput)" v-if="!(input.name in changeRequestIndex)">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="card" v-if="changeRequestList.length > 0">
                        <div class="card-header">
                            Change Request List
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
                                    <tr v-for="(changeRequest, index) in changeRequestList" :key="index">
                                        <td>{{ index+1 }}</td>
                                        <td>{{ changeRequest.name }} </td>
                                        <td>{{ changeRequest.dataType }}</td>
                                        <td>{{ changeRequest.length }} </td>
                                        <td>{{ changeRequest.precision }}</td>
                                        <td>{{ changeRequest.scale }}</td>
                                        <td>{{ changeRequest.default }}</td>
                                        <td v-bind:class="[changeRequest.nullable == 'N' ? 'text-danger' : 'text-success']">{{ changeRequest.nullable }}</td>
                                        <td v-bind:class="[changeRequest.unique == 'N' ? 'text-danger' : 'text-success']">{{ changeRequest.unique }}</td>
                                        <td>{{ changeRequest.min }}</td>
                                        <td>{{ changeRequest.max }}</td>
                                        <td>{{ changeRequest.tableName }}</td>
                                        <td>{{ changeRequest.columnName }}</td>
                                        <td>
                                            <span class="badge" v-bind:class="[changeRequest.changeType == 'add' ? 'badge-success' : changeRequest.changeType == 'edit' ? 'badge-warning' : 'badge-danger']">{{ changeRequest.changeType }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger" @click="deleteChangeRequest(index)">-</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr>
                            <div class="float-right">
                                <button class="btn btn-primary" @click="submitChangeRequest">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalHeader" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modalHeader" v-if="changeRequest.changeType == 'add'">
                            <span class="badge badge-success">Add new input</span>
                        </h4>
                        <h4 class="modal-title" id="modalHeader" v-if="changeRequest.changeType == 'edit'">
                            <span class="badge badge-warning">Edit</span>
                            <span class="badge badge-light">{{ changeRequest.name }}</span>
                        </h4>
                        <h4 class="modal-title" id="modalHeader" v-if="changeRequest.changeType == 'delete'">
                            <span class="badge badge-danger">Delete</span>
                            <span class="badge badge-light">{{ changeRequest.name }}</span>
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form v-on:submit.prevent="addChangeRequest">
                        <div class="modal-body">
                            <div v-if="changeRequest.changeType == 'add' || changeRequest.changeType == 'edit'">
                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">Name</label>
                                    <div class="col-sm-10 align-text-bottom" v-if="changeRequest.changeType == 'add'">
                                        <input type="text" class="form-control" v-model="changeRequest.name" required>
                                    </div>
                                    <label v-else class="col-sm-10 col-form-label">{{ changeRequest.name }}</label>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">Data Type</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" v-model="changeRequest.dataType">
                                            <option value="char">char</option>
                                            <option value="varchar">varchar</option>
                                            <option value="nchar">nchar</option>
                                            <option value="nvarchar">nvarchar</option>
                                            <option value="int">int</option>
                                            <option value="float">float</option>
                                            <option value="decimal">decimal</option>
                                            <option value="date">date</option>
                                            <option value="datetime">datetime</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row" v-if="changeRequest.dataType.indexOf('char') != -1">
                                    <label for="" class="col-sm-2 col-form-label">Length</label>
                                    <div class="col-sm-10">
                                        <input type="number" class="form-control" v-model="changeRequest.length" required>
                                    </div>
                                </div>
                                <div class="form-group row" v-if="changeRequest.dataType.indexOf('float') != -1 || changeRequest.dataType.indexOf('decimal') != -1">
                                    <label for="" class="col-sm-2 col-form-label">Precision</label>
                                    <div class="col-sm-10">
                                        <input type="number" class="form-control" v-model="changeRequest.precision" required>
                                    </div>
                                </div>
                                <div class="form-group row" v-if="changeRequest.dataType.indexOf('decimal') != -1">
                                    <label for="" class="col-sm-2 col-form-label">Scale</label>
                                    <div class="col-sm-10">
                                        <input type="number" class="form-control" v-model="changeRequest.scale" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">Default</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" v-model="changeRequest.default">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 form-control-label"></label>
                                    <div class="col-sm-4">
                                        <label class="checkbox-inline">
                                            <input name="nullable" type="checkbox" true-value="Y" false-value="N" v-model="changeRequest.nullable"> Nullable
                                        </label>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="checkbox-inline">
                                            <input name="unique" type="checkbox" true-value="Y" false-value="N" v-model="changeRequest.unique"> Unique
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group row" v-if="changeRequest.dataType.indexOf('int') != -1 ||
                            changeRequest.dataType.indexOf('decimal') != -1 ||
                            changeRequest.dataType.indexOf('float') != -1">
                                    <label for="" class="col-sm-2 col-form-label">Min</label>
                                    <div class="col-sm-10">
                                        <input type="number" class="form-control" v-model="changeRequest.min">
                                    </div>
                                </div>
                                <div class="form-group row" v-if="changeRequest.dataType.indexOf('int') != -1 ||
                            changeRequest.dataType.indexOf('decimal') != -1 ||
                            changeRequest.dataType.indexOf('float') != -1">
                                    <label for="" class="col-sm-2 col-form-label">Max</label>
                                    <div class="col-sm-10">
                                        <input type="number" class="form-control" v-model="changeRequest.max">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">Table</label>
                                    <div class="col-sm-10" v-if="changeRequest.changeType == 'add'">
                                        <input type="text" class="form-control" v-model="changeRequest.tableName" required>
                                    </div>
                                    <label v-else class="col-sm-10 col-form-label">{{ changeRequest.tableName }}</label>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">Column</label>
                                    <div class="col-sm-10" v-if="changeRequest.changeType == 'add'">
                                        <input type="text" class="form-control" v-model="changeRequest.columnName" required>
                                    </div>
                                    <label v-else class="col-sm-10 col-form-label">{{ changeRequest.columnName }}</label>
                                </div>
                            </div>
                            <div class="alert alert-danger" role="alert" v-if="errors.length > 0">
                                {{ errors }}
                            </div>

                        </div>
                        <div class="modal-footer">
                            <div class="float-right">
                                <button class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
  props: [
    "accessToken",
    "selectedProjectInit",
    "selectedFunctionalRequirementInit"
  ],
  name: "change-request-form",
  data() {
    return {
      projectList: [],
      functionalList: [],
      selectedProject: this.selectedProjectInit,
      selectedFunctional: "",
      isHaveFr: true,
      changeRequest: {
        changeType: "",
        name: "",
        dataType: "char",
        length: "",
        precision: "",
        scale: "",
        default: "",
        nullable: "",
        unique: "",
        min: "",
        max: ""
      },
      changeRequestList: [],
      changeRequestIndex: {},
      errors: ""
    };
  },
  methods: {
    getProjectList() {
      this.selectedFunctional = "-";
      let url = "/api/v1/projects/";
      var vm = this;

      axios({
        method: "GET",
        url: url,
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
          if (response.status == 200) {
            vm.projectList = response.data;
          }
        })
        .catch(function(errors) {});
    },
    getFunctionalList() {
      this.selectedFunctional = "-";
      this.functionalList = [];
      this.changeRequestList = [];
      this.changeRequestIndex = {};
      if (this.selectedProject == "-") {
        return;
      }
      let url =
        "/api/v1/projects/" + this.selectedProject + "/functionalRequirements";
      let vm = this;
      axios({
        method: "GET",
        url: url,
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
          if (response.status == 200) {
            vm.functionalList = response.data;
          } else {
            vm.isHaveFr = false;
          }
        })
        .catch(function(errors) {});
    },
    resetFunctional() {
      if (this.selectedFunctional == "-") {
        return;
      }
      (this.changeRequestList = []), (this.changeRequestIndex = {});
    },
    newInput() {
      this.errors = "";
      this.changeRequest = {
        changeType: "add",
        name: "",
        dataType: "char",
        length: "",
        precision: "",
        scale: "",
        default: "",
        nullable: "Y",
        unique: "N",
        min: "",
        max: "",
        tableName: "",
        columnName: ""
      };
    },
    editInput(inputIndex) {
      this.errors = "";
      var input = this.functionalList[this.selectedFunctional].inputs[
        inputIndex
      ];
      this.changeRequest = {
        changeType: "edit",
        name: input.name,
        dataType: input.dataType,
        length: input.length,
        precision: input.precision,
        scale: input.scale,
        default: input.default,
        nullable: input.nullable,
        unique: input.unique,
        min: input.min,
        max: input.max,
        tableName: input.tableName,
        columnName: input.columnName,
        oldInputIndex: inputIndex
      };
    },
    deleteInput(inputIndex) {
      this.errors = "";
      var input = this.functionalList[this.selectedFunctional].inputs[
        inputIndex
      ];
      this.changeRequest = {
        changeType: "delete",
        name: input.name,
        oldInputIndex: inputIndex
      };
    },
    addChangeRequest() {
      let newChangeRequest = Object.assign({}, this.changeRequest);
      if (this.changeRequest.changeType == "edit") {
        let oldInput = this.functionalList[this.selectedFunctional].inputs[
          this.changeRequest.oldInputIndex
        ];
        let isNotChange = 0;
        if (oldInput.dataType == this.changeRequest.dataType) {
          delete newChangeRequest["dataType"];
          ++isNotChange;
        }
        if (oldInput.length == this.changeRequest.length) {
          delete newChangeRequest["length"];
          ++isNotChange;
        }
        if (oldInput.precision == this.changeRequest.precision) {
          delete newChangeRequest["precision"];
          ++isNotChange;
        }
        if (oldInput.scale == this.changeRequest.scale) {
          delete newChangeRequest["scale"];
          ++isNotChange;
        }
        if (oldInput.default == this.changeRequest.default) {
          delete newChangeRequest["default"];
          ++isNotChange;
        }
        if (oldInput.nullable == this.changeRequest.nullable) {
          delete newChangeRequest["nullable"];
          ++isNotChange;
        }
        if (oldInput.unique == this.changeRequest.unique) {
          delete newChangeRequest["unique"];
          ++isNotChange;
        }
        if (oldInput.min == this.changeRequest.min) {
          delete newChangeRequest["min"];
          ++isNotChange;
        }
        if (oldInput.max == this.changeRequest.max) {
          delete newChangeRequest["max"];
          ++isNotChange;
        }
        delete newChangeRequest["tableName"];
        delete newChangeRequest["columnName"];
        if (isNotChange != 9) {
          this.changeRequestList.push(newChangeRequest);
          this.changeRequestIndex[this.changeRequest.name] = true;
          $("#modal").modal("hide");
        } else {
          this.errors = "Nothing is changed.";
        }
      } else {
        if (this.changeRequest.changeType == "add") {
          Object.keys(newChangeRequest).forEach(function(key) {
            if (!newChangeRequest[key]) {
              delete newChangeRequest[key];
            }
          });
        }
        let isError = false;

        if (this.changeRequest.name in this.changeRequestIndex) {
            console.log('name already')
          isError = true;
          this.errors =
            "Input name : " +
            this.changeRequest.name +
            " already add in this request.";
        } else {
          for (let i = 0; i < this.changeRequestList.length; ++i) {
            if (this.changeRequestList[i].changeType == "add") {
              if (
                this.changeRequestList[i].tableName == newChangeRequest.tableName &&
                this.changeRequestList[i].columnName == newChangeRequest.columnName
              ) {
                isError = true;
                this.errors =
                  "Cannot add the same table name and column name in this request.";
              }
            }
          }
        }
        if (!isError) {
          this.changeRequestList.push(newChangeRequest);
          this.changeRequestIndex[this.changeRequest.name] = true;
          $("#modal").modal("hide");
        }
      }
    },
    deleteChangeRequest(index) {
      let name = this.changeRequestList[index].name;
      this.changeRequestList.splice(index, 1);
      delete this.changeRequestIndex[name];
    },
    submitChangeRequest() {
      let vm = this;
      let data = JSON.stringify({
        functionalRequirementNo: this.functionalList[this.selectedFunctional]
          .no,
        inputs: this.changeRequestList
      });
      axios({
        url: "/api/v1/projects/" + this.selectedProject + "/changeRequests",
        method: "POST",
        data: data,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
          location.href =
            "/project/" +
            vm.selectedProject +
            "/changeRequest/" +
            response.data.changeRequestId;
        })
        .catch(function(errors) {});
    }
  },
  created() {
    this.getProjectList();
  }
};
</script>
