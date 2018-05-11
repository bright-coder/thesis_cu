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
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'basic' }" @click="showBasic">Basic Information</a>
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'database' }" @click="showDB">Database</a>
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'fr' }" @click="showFR">Functional Requirement</a>
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'tc' }" @click="showTC">Test Case</a>
                            <a href="#" class="list-group-item list-group-item-action" v-bind:class="{'active' : menu == 'rtm' }" @click="showRTM">Requirement Traceability Matrix</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <project-form :access-token="accessToken" :request-type="requestType" :project-name-init="projectNameInit" v-if="menu == 'basic'">
                </project-form>
            </div>
        </div>
    </div>
</template>
<script>
import ProjectForm from "./ProjectForm.vue";
export default {
  name: "project-show",
  props: ["accessToken", "requestType", "projectNameInit"],
  data() {
    return {
      menu: "basic"
    };
  },
  components: {
    ProjectForm
  },
  methods: {
    showBasic() {
        this.menu = 'basic'
    },
    showDB() {
        this.menu = 'database'
    },
    showFR() {
        this.menu = 'fr'
    },
    showTC() {
        this.menu = 'tc'
    },
    showRTM() {
        this.menu = 'rtm'
    }
  }
};
</script>
