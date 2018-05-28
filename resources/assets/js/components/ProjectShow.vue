<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4>Menu</h4>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'basic' }" @click.prevent="showBasic">
                                <i class="fas fa-info"></i>&nbsp;&nbsp;Basic Information</a>
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'database' }" @click.prevent="showDB">
                                <i class="fas fa-database"></i>&nbsp;&nbsp;Database</a>
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'fr' }" @click.prevent="showFR">
                                <i class="fas fa-list-ul"></i>&nbsp;&nbsp;Functional Requirement</a>
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'tc' }" @click.prevent="showTC">
                                <i class="fas fa-clipboard-check"></i>&nbsp;&nbsp;Test Case</a>
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'rtm' }" @click.prevent="showRTM">
                                <i class="fas fa-link"></i>&nbsp;&nbsp;Requirement Traceability Matrix</a>
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'cr-history' }" @click.prevent="showCrHistory">
                                <i class="fas fa-history"></i>&nbsp;&nbsp;Change Request History</a>
                        </div>
                        <hr>
                        <button type="button" data-toggle="modal" data-target="#confirmModal" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-trash"></i>&nbsp;&nbsp;Delete This Project</button>
                        <!-- Modal -->
                        <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModal" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">
                                            <span class="badge badge-danger">Delete</span> {{ this.projectNameInit }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure ?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger" @click="remove">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <project-form :access-token="accessToken" :request-type="requestType" :project-name-init="projectNameInit" v-show="menu == 'basic'">
                </project-form>
                <database-table :access-token="accessToken" :project-name="projectNameInit" v-if="menu == 'database'"></database-table>
                <project-file :access-token="accessToken" :project-name="projectNameInit" v-if="menu == 'fr'" :contentType="'fr'" key="fr"></project-file>
                <project-file :access-token="accessToken" :project-name="projectNameInit" v-if="menu == 'tc'" :contentType="'tc'" key="tc"></project-file>
                <project-file :access-token="accessToken" :project-name="projectNameInit" v-if="menu == 'rtm'" :contentType="'rtm'" key="rtm"></project-file>
                <div class="container-fluid" v-if="menu == 'cr-history'">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-history"></i>&nbsp;&nbsp;{{ projectNameInit }}</h4> 
                        </div>
                        <div class="card-body">
                            <change-request-list :access-token="accessToken" :project-name="projectNameInit" v-if="menu == 'cr-history'"></change-request-list>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</template>
<script>
import ProjectForm from "./ProjectForm.vue";
import ProjectFile from "./ProjectFile.vue";
import DatabaseTable from "./DatabaseTable.vue";
import ChangeRequestList from "./ChangeRequestList.vue";
export default {
  name: "project-show",
  props: ["accessToken", "requestType", "projectNameInit"],
  data() {
    return {
      menu: "basic"
    };
  },
  components: {
    ProjectForm,
    ProjectFile,
    DatabaseTable,
    ChangeRequestList
  },
  methods: {
    showBasic() {
      this.menu = "basic";
    },
    showDB() {
      this.menu = "database";
    },
    showFR() {
      this.menu = "fr";
    },
    showTC() {
      this.menu = "tc";
    },
    showRTM() {
      this.menu = "rtm";
    },
    showCrHistory() {
      this.menu = "cr-history";
    },
    remove() {
      var vm = this;
      axios({
        url: "/api/v1/projects/" + this.projectNameInit,
        method: "DELETE",
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
          location.href = "/project/";
        })
        .catch(function(errors) {});
    }
  }
};
</script>
