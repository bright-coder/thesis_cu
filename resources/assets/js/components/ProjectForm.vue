<template>
<div class="container">
  <div class="card">
    <div class="card-header" v-bind:class="[requestType == 'create' ? 'bg-success text-white' : '']">
      
      <h4>{{ requestType == 'update' ? this.projectName : 'Create a new project' }}</h4>
    </div>
    <div class="card-body">
        <form v-on:submit.prevent="sendRequest">
    <div class="form-row">
      <div class="form-group col-md-6" v-if="requestType == 'create'">
        <label for="projectName">Project Name</label>
        <input type="text" class="form-control" placeholder="" v-model="projectName" v-bind:class="[errors.projectName ? 'is-invalid' :  isSend ? 'is-valid' : '']">
        <div class="invalid-feedback" v-if="errors.projectName">
          {{ errors.projectName[0] }}
        </div>
      </div>
      <div class="form-group col-md-2">
        <label for="prefix">Prefix</label>
        <input type="text" class="form-control" placeholder="e.g. HS, OR" v-model="prefix" v-bind:class="[errors.prefix ? 'is-invalid' :  isSend ? 'is-valid' : '']">
        <div class="invalid-feedback" v-if="errors.prefix">
          {{ errors.prefix[0] }}
        </div>
      </div>
    </div>
    <hr>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="projectName">Database Name</label>
        <input type="text" class="form-control" placeholder="" v-model="dbName" v-bind:class="[errors.dbName ? 'is-invalid' :  isSend ? 'is-valid' : '']">
        <div class="invalid-feedback" v-if="errors.dbName">
          {{ errors.dbName[0] }}
        </div>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="projectName">Database Server</label>
        <input type="text" class="form-control" placeholder="e.g. localhost" v-model="dbServer" v-bind:class="[errors.dbServer ? 'is-invalid' :  isSend ? 'is-valid' : '']">
        <div class="invalid-feedback" v-if="errors.dbServer">
          {{ errors.dbServer[0] }}
        </div>
      </div>
      <div class="form-group col-md-2">
        <label for="projectName">Database Port</label>
        <input type="number" class="form-control" placeholder="Port" v-model="dbPort" v-bind:class="[errors.dbPort ? 'is-invalid' :  isSend ? 'is-valid' : '']">
        <div class="invalid-feedback" v-if="errors.dbPort">
          {{ errors.dbPort[0] }}
        </div>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="projectName">Database Username</label>
        <input type="text" class="form-control" placeholder="" v-model="dbUsername" v-bind:class="[errors.dbUsername ? 'is-invalid' :  isSend ? 'is-valid' : '']">
        <div class="invalid-feedback" v-if="errors.dbUsername">
          {{ errors.dbUsername[0] }}
        </div>
      </div>
      <div class="form-group col-md-6">
        <label for="projectName">Database Password</label>
        <input type="password" class="form-control" placeholder="" v-model="dbPassword" v-bind:class="[errors.dbPassword ? 'is-invalid' :  isSend ? 'is-valid' : '']">
        <div class="invalid-feedback" v-if="errors.dbPassword">
          {{ errors.dbPassword[0] }}
        </div>
      </div>
    </div>
    <label for="inlineRadio1">Database Type</label><br>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="sqlsrv" v-model="dbType" checked="">
      <label class="form-check-label" for="inlineRadio1">Sql Server</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio3" value="mysql" disabled>
      <label class="form-check-label" for="inlineRadio3">MySql (disabled)</label>
    </div>
    <div class="invalid-feedback" v-if="errors.dbType">
      {{ errors.dbType[0] }}
    </div>
    <hr>
    <button type="submit" class="btn btn-primary">Save</button>
  </form>
    </div>
  </div>
</div>
</template>
<script>
import XLSX from "xlsx";
export default {
  name: "project-form",
  props: ["accessToken", "requestType", "projectNameInit"],
  data() {
    return {
      projectName: "",
      prefix: "",
      dbName: "",
      dbServer: "",
      dbPort: 1433,
      dbUsername: "",
      dbPassword: "",
      dbType: "sqlsrv",
      errors: [],
      isSend: false
    };
  },
  methods: {
    sendRequest() {
      let url = "/api/v1/projects";
      let method = "post";
      if (this.requestType == "update") {
        url += '/'+this.projectName
        method = "patch"
      }
      var data = JSON.stringify({
        projectName: this.projectName,
        prefix: this.prefix,
        dbName: this.dbName,
        dbServer: this.dbServer,
        dbPort: this.dbPort,
        dbUsername: this.dbUsername,
        dbPassword: this.dbPassword,
        dbType: this.dbType
      });

      var vm = this; // to use data of Vue e.g. this.error inside axios
      vm.errors = []; // reset error
      vm.isSend = true; // sendRequest
      axios({
        method: method,
        url: url,
        data: data,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
          if(response.status == 200) {
            location.href = '/project/'+vm.projectName
          }
        })
        .catch(function(error) {
          let errorFields = error.response.data.msg.fields;

          vm.$set(vm, "errors", errorFields);
        });
    },
    getProject() {
      let url = "/api/v1/projects/" + this.projectNameInit
      var vm = this
      axios({
        method: 'get',
        url: url,
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: 'json'
      })
        .then(function(response) {
          let data = response.data
          vm.projectName = data.name
          vm.prefix = data.prefix
          vm.dbName = data.dbName
          vm.dbServer = data.dbServer
          vm.dbPort = data.dbPort
          vm.dbUsername = data.dbUsername
          vm.dbPassword = data.dbPassword
          vm.dbType = data.dbType
        })
        .catch(function (errors) {
          
        })
        
    }
  },
  created() {
    if(this.requestType == 'update') {
      this.getProject()
    }
  }
};
</script>
