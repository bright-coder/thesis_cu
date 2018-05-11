<template>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white"> My project</div>
            <div class="card-body"></div>
        </div>
    </div>
</template>
<script>
export default {
  name: "project-main",
  props: ["accessToken"],
  data() {
    return {
      projects: []
    };
  },
  methods: {
    getAllProject() {
      let url = "/api/v1/projects";

      axios({
        url: url,
        method: "GET",
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: 'json'
      })
      .then(function (response){

          if(response.status == 200) {
              projects = response.data
          }
          console.log(response)
      })
      .catch(function (errors){

      })
    }
  },
  created() {
      this.getAllProject()
  }
};
</script>
