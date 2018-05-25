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
        ImpactList: {}
    }
  },
  methods: {},
  created() {
      
  }
};
</script>
