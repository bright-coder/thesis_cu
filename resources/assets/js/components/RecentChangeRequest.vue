<template>
    <table class="table table-hover">
        <thead>
            <tr class="bg-info text-white">
                <th></th>
                <th>Id</th>
                <th>Project Name</th>
                <th>Change Functional Requirement No</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(changeRequest,index) in changeRequestList" :key="index">
                <td>{{ index+1 }}</td>
                <td>{{ changeRequest.id }}</td>
                <td>{{ changeRequest.projectName }}</td>
                <td>{{ changeRequest.frNo }}</td>
                <td class="text-success"> SUCCESS </td>
                <td><a :href="'/project/'+changeRequest.projectName+'/changeRequest/'+changeRequest.id" class="btn btn-primary">More</a></td>
            </tr>
        </tbody>
    </table>
</template>
<script>
export default {
  name: "recent-change-request",
  props: ["accessToken"],
  data() {
    return {
        changeRequestList: ''
    };
  },
  methods: {
    getAllChangeRequest() {
    let vm = this;
      axios({
        url: "/api/v1/projects/all/changeRequests",
        methods: "GET",
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
    }
  },
  created() {
    this.getAllChangeRequest();
  }
};
</script>

