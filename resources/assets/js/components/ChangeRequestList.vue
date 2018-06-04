<template>
    <table class="table table-hover">
        <thead>
            <tr class="bg-info text-white">
                <th></th>
                <th>Id</th>
                <th v-if="projectName == 'all'">Project Name</th>
                <th>Change Functional Requirement No</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(changeRequest,index) in changeRequestList" :key="index">
                <td>{{ index+1 }}</td>
                <td>{{ changeRequest.id }}</td>
                <td v-if="projectName == 'all'">{{ changeRequest.projectName }}</td>
                <td>{{ changeRequest.frNo }}</td>
                <td v-bind:class="[changeRequest.status == 'success' ? 'text-success' : 'text-danger']"><strong> {{ changeRequest.status }} </strong></td>
                <td>
                    <a :href="'/project/'+changeRequest.projectName+'/changeRequest/'+changeRequest.id" class="btn btn-primary">More</a>
                    <button v-if="changeRequest.status == 'success'" class="btn btn-danger" @click="cancelChangeRequest(changeRequest.projectName, changeRequest.id)">Cancel</button>
                </td>
            </tr>
        </tbody>
    </table>
</template>
<script>
export default {
  name: "change-request-list",
  props: ["accessToken", 'projectName'],
  data() {
    return {
        changeRequestList: ''
    };
  },
  methods: {
    getAllChangeRequest() {
    let vm = this;
      axios({
        url: "/api/v1/projects/"+this.projectName+"/changeRequests",
        method: "GET",
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
      .then(function (response){
          vm.changeRequestList = response.data;
      })
      .catch(function (errors){

      })
    },
    cancelChangeRequest(projectName, changeRequestId) {

        axios({
            url: "/api/v1/projects/"+projectName+"/changeRequests/"+changeRequestId,
            method: "DELETE",
            data: null,
            headers: {
                Authorization: "Bearer " + this.accessToken,
                "Content-Type": "application/json; charset=utf-8"
            },
            dataType: "json"
        })
        .then(function (response) {

        })
        .catach(function (errors) {

        })
    }
  },
  created() {
    this.getAllChangeRequest();
  }
};
</script>

