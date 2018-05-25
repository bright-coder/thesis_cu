<template>
    <div class="groot">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">Schema</div>
                <div class="card-body"></div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">Instance</div>
                <div class="card-body"></div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">Functional Requirement</div>
                <div class="card-body"></div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">Test Case</div>
                <div class="card-body"></div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">Requirement Traceability Matrix</div>
                <div class="card-body"></div>
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
        impactList: {}
    }
  },
  methods: {
      getImpact() {
          let vm = this;
          axios({
              url: '/api/v1/projects/' + this.projectName + '/changeRequests/' + this.changeRequestId,
              method: 'GET',
              data: null,
              headers: {
                Authorization: "Bearer " + this.accessToken,
                "Content-Type": "application/json; charset=utf-8"
              },
              dataType: 'json'
          })
          .then(function (response){
              vm.impactList = response.data
              console.log(response.data)
          })
          .catch(function (errors) {

          })
      }
  },
  created() {
      this.getImpact()
  }
};
</script>
