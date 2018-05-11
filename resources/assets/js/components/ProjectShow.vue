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
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'basic' }" @click="showBasic">
                                <i class="fas fa-info"></i>&nbsp;&nbsp;Basic Information</a>
                            <a href="#database" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'database' }" @click="showDB">
                                <i class="fas fa-database"></i>&nbsp;&nbsp;Database</a>
                            <a href="#functionalRequirement" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'fr' }" @click="showFR">
                                <i class="fas fa-list-ul"></i>&nbsp;&nbsp;Functional Requirement</a>
                            <a href="#testCase" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'tc' }" @click="showTC">
                                <i class="fas fa-clipboard-check"></i>&nbsp;&nbsp;Test Case</a>
                            <a href="#requirementTraceabilityMatrix" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'rtm' }" @click="showRTM">
                                <i class="fas fa-link"></i>&nbsp;&nbsp;Requirement Traceability Matrix</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <project-form :access-token="accessToken" :request-type="requestType" :project-name-init="projectNameInit" v-if="menu == 'basic'">
                </project-form>
                <project-file :access-token="accessToken" :project-name="projectNameInit" v-if="menu == 'fr'" :contentType="menu"></project-file>
                <project-file :access-token="accessToken" :project-name="projectNameInit" v-if="menu == 'tc'" :contentType="menu"></project-file>
                <project-file :access-token="accessToken" :project-name="projectNameInit" v-if="menu == 'rtm'" :contentType="menu"></project-file>
            </div>
        </div>
    </div>
</template>
<script>
import ProjectForm from "./ProjectForm.vue";
import ProjectFile from "./ProjectFile.vue";
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
    ProjectFile
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
    }
  }
};
</script>
