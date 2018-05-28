<template>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white"> <h4>My project</h4></div>
            <div class="card-body">
                <div class="row" v-if="projects.length > 0">
                    <div class="col-md-4">
                        <select class="form-control" v-model="selectedProject">
                            <option v-for="(project, index) in projects" v-bind:key="index" v-bind:value="project.name">
                                {{ project.name }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary" @click="go()"> Go</button>
                    </div>
                </div>
                <div class="row" v-if="projects.length == 0">
                    <div class="col-md-12">
                        Let's 
                        <a href="/project/create">create a new project.</a>
                    </div>
                    
                </div>

            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'change-request-main',
    props: ['accessToken','projectName'],
    data() {
        return {
            projects: '',
            selectedProject: ''
        }
    },
    methods: {
            getAllProject() {
      let url = "/api/v1/projects";
      var vm = this;
      axios({
        url: url,
        method: "GET",
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
          if (response.status == 200) {
            vm.projects = response.data;
            vm.selectedProject = vm.projects[0].name
          }
          //console.log(response);
        })
        .catch(function(errors) {});
    },
    go() {
        location.href = '/project/' + this.selectedProject + '/changeRequest'
    }
    },
    created() {
        this.getAllProject()
    }
}
</script>
