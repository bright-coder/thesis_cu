<template>
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h4>
          <i class="fas" v-bind:class="[this.contentType == 'fr' ? 'fa-list-ul' : this.contentType == 'tc' ? 'fa-clipboard-check': 'fa-link']"></i>&nbsp;&nbsp;{{ this.projectName }}</h4>
      </div>
      <div class="card-body">
        <div class="row" v-if="isSave == 0 || isSave == 2">
          <div class="col-md-4">
            <div class="custom-file">
              <input type="file" class="custom-file-input" ref="file" @change="this.readFile">
              <label class="custom-file-label">{{ filename }}</label>
            </div>
          </div>
          <div class="col-md-4">
            <button class="btn btn-primary" @click="this.save">Save</button>
          </div>
        </div>

        <div class="row" v-if="isSave > 0">
          <div class="col-md-12" v-if="isSave > 1"><br></div>
          <div class="col-md-6">
            <div class="alert alert-dismissible fade show" v-bind:class="[this.isSave == 1 ? 'alert-success' : 'alert-danger']" role="alert">
              <strong>{{ this.msg }}</strong>
            </div>
          </div>
        </div>
        <div class="row" v-if="this.content.length > 0">
          <div class="col-md-12" v-if="isSave == 0 || isSave == 2"><hr></div>
          <div class="col-md-12">
            <functional-requirement-table v-if="this.contentType == 'fr'" v-bind:frs="this.content"></functional-requirement-table>
            <test-case-table v-if="this.contentType == 'tc'" v-bind:tcs="this.content"></test-case-table>
            <rtm-table v-if="this.contentType == 'rtm'" v-bind:relations="this.content"></rtm-table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import XLSX from "xlsx";
import FunctionalRequirementTable from "./FunctionalRequirementTable.vue";
import TestCaseTable from "./TestCaseTable.vue";
import RtmTable from "./RtmTable.vue";
export default {
  name: "project-file",
  props: ["accessToken", "projectName", "contentType"],
  data() {
    return {
      content: [],
      filename: "Choose file .xlsx",
      isSave: 0,
      msg: "",
      tables: []
    };
  },
  components: {
    FunctionalRequirementTable,
    TestCaseTable,
    RtmTable
  },
  methods: {
    getContent() {
      let url = "/api/v1/projects/" + this.projectName;
      if (this.contentType == "fr") {
        url += "/functionalRequirements";
      } else if (this.contentType == "tc") {
        url += "/testCases";
      } else {
        url += "/RTM";
      }
      var vm = this
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
      .then(function(response){
        if(response.status == 200) {
          vm.isSave = -1
          vm.content = response.data
          if(vm.contentType == "fr") {
            for(let i = 0 ; i < vm.content.length; ++i) {
              for(let j = 0 ; j < vm.content[i].inputs.length ; ++j) {
                let info = vm.findColumnInfo(vm.content[i].inputs[j].tableName, vm.content[i].inputs[j].columnName)
          
                vm.content[i].inputs[j] = Object.assign(vm.content[i].inputs[j], info)
                
              }
            }
          }
        }
        //console.log(vm.content);
      
      })
      .catch(function(errors){
        vm.isSave = 2
        console.log(errors)
        if(errors.response.status == 500)
          vm.msg = 'Server Error, please try again later.'
      })
    },
    findColumnInfo(tableName, columnName){
      let info = {}
      let vm = this
                for(let i = 0; i < vm.tables.length; ++i) {
            if(vm.tables[i].name == tableName) {
              for(let j = 0; j < vm.tables[i].columns.length; ++j) {
                if(vm.tables[i].columns[j].name == columnName) {
                  info.dataType = vm.tables[i].columns[j].dataType
                  info.length = vm.tables[i].columns[j].length
                  info.precision = vm.tables[i].columns[j].precision
                  info.scale = vm.tables[i].columns[j].scale
                  info.default = vm.tables[i].columns[j].default
                  info.nullable = vm.tables[i].columns[j].nullable
                  info.unique = vm.tables[i].columns[j].unique
                  info.min = vm.tables[i].columns[j].min ? vm.tables[i].columns[j].min.value : null
                  info.max = vm.tables[i].columns[j].max ? vm.tables[i].columns[j].max.value : null
                }
              }
            }
          }
          return info
    },
    readFile() {
      if (this.$refs.file.files.length > 0) {
        this.$data.filename = this.$refs.file.files[0].name;
        var reader = new FileReader();
        var vm = this;
        vm.content = [];
        reader.onload = function(e) {
          var binary = "";
          var bytes = new Uint8Array(e.target.result);
          var length = bytes.byteLength;
          for (var i = 0; i < length; i++) {
            binary += String.fromCharCode(bytes[i]);
          }
          var workbook = XLSX.read(binary, { type: "binary" });
          var listOfSheet = [];
          $.each(workbook.Sheets, function(index, sheet) {
            var arraySheet = vm.sheetToArray(sheet);
            if (arraySheet.length > 0) {
              listOfSheet.push(arraySheet);
            }
          });

          if (listOfSheet.length > 0) {
            if (vm.contentType == "fr") {
              vm.readFrFromExcel(listOfSheet);
            } else if (vm.contentType == "tc") {
              vm.readTcFromExcel(listOfSheet);
            } else if (vm.contentType == "rtm") {
              vm.readRtmFromExcel(listOfSheet[0]);
            }
          }
        };
        reader.readAsArrayBuffer(this.$refs.file.files[0]);
        //vm.content = vm.cleanContent(vm.content)
        //vm.cleanContent();
      }
    },
    readFrFromExcel(frList) {
      var vm = this;
      $.each(frList, function(index, fr) {
        var no = vm.isKeyExist(fr, 0, 1) ? fr[0][1] : undefined;
        var description = vm.isKeyExist(fr, 1, 1) ? fr[1][1] : undefined;
        var inputList = [];
        for (var i = 4; i < fr.length; ++i) {
          let input = {
            name: 0 in fr[i] ? fr[i][0] : "",
            columnName: 1 in fr[i] ? fr[i][1] : "",
            tableName: 2 in fr[i] ? fr[i][2] : ""
          }
          let info = vm.findColumnInfo(input.tableName, input.tableName)
          Object.assign(input, info)
          inputList.push(input);
        }
        vm.content.push({
          no: no,
          desc: description,
          inputs: inputList.length > 0 ? inputList : undefined
        });
      });
    },
    getDatabase() {
      let vm = this;
      axios({
        url: "/api/v1/projects/" + this.projectName + "/databases",
        method: "GET",
        data: null,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
 
          vm.tables = response.data;
          //console.log(vm.tables);
        })
        .catch(function(errors) {});
    },
    readTcFromExcel(tcList) {
      var vm = this;
      $.each(tcList, function(index, tc) {
        var no = vm.isKeyExist(tc, 0, 1) ? tc[0][1] : undefined;
        var type = vm.isKeyExist(tc, 1, 1) ? tc[1][1].toLowerCase() : undefined;
        var inputList = [];
        for (var i = 4; i < tc.length; ++i) {
          inputList.push({
            name: 0 in tc[i] ? tc[i][0] : undefined,
            testData: 1 in tc[i] ? tc[i][1] : undefined
          });
        }
        vm.content.push({
          no: no,
          type: type,
          inputs: inputList.length > 0 ? inputList : undefined
        });
      });
    },
    readRtmFromExcel(rtm) {
      var vm = this;
      for (var i = 1; i < rtm.length; ++i) {
        var frNo = this.isKeyExist(rtm, i, 0) ? rtm[i].shift() : undefined;
        var testCaseNos = vm.filter_array(rtm[i]);
        vm.content.push({
          functionalRequirementNo: frNo,
          testCaseNos: testCaseNos
        });
      }
      
    },
    sheetToArray(sheet) {
      var result = [];
      var row;
      var rowNum;
      var colNum;
      if (sheet["!ref"] == undefined) {
        return result;
      }
      var range = XLSX.utils.decode_range(sheet["!ref"]);
      for (rowNum = range.s.r; rowNum <= range.e.r; rowNum++) {
        row = [];
        for (colNum = range.s.c; colNum <= range.e.c; colNum++) {
          var nextCell =
            sheet[XLSX.utils.encode_cell({ r: rowNum, c: colNum })];
          if (typeof nextCell === "undefined") {
            row.push(void 0);
          } else row.push(nextCell.w);
        }
        result.push(row);
      }
      return result;
    },
    isKeyExist(array, dimen1, dimen2 = undefined) {
      if (dimen1 in array) {
        if (dimen2 != undefined) {
          if (!Array.isArray(array[dimen1])) {
            return false;
          }
          if (dimen2 in array[dimen1]) {
            return true;
          } else return false;
        }
      } else return false;
    },
    save() {
      let url = "/api/v1/projects/" + this.projectName;

      if (this.contentType == "fr") {
        url += "/functionalRequirements";
      } else if (this.contentType == "tc") {
        url += "/testCases";
      } else {
        url += "/RTM";
      }

      var vm = this;
      var data = JSON.stringify(this.content);
      axios({
        url: url,
        method: "POST",
        data: data,
        headers: {
          Authorization: "Bearer " + this.accessToken,
          "Content-Type": "application/json; charset=utf-8"
        },
        dataType: "json"
      })
        .then(function(response) {
          vm.isSave = 1;
          vm.msg = "Save success.";
        })
        .catch(function(errors) {
          vm.isSave = 2;
          console.log(errors.response.status)
          if (errors.response.status == 500)
            vm.msg = "Server Error, please try again later.";
          else vm.msg = "Somthing Wrong, please check your xlsx file.";
        });
    },
    cleanContent() {
      var obj = this.content;
      Object.keys(obj).forEach(function(key) {
        //console.log(555)
        if (obj[key] instanceof Array || typeof obj[key] === "object") {
          string += "object " + key;
          obj[key] = vm.cleanContent(obj[key]);
          if (obj[key] instanceof Array) {
            string += "array " + key;
            obj[key] = vm.filter_array(obj[key]);
          }
        } else if (!obj[key]) {
          delete obj[key];
        }
      });

      return obj;
    },
    filter_array(array) {
      var index = -1,
        arr_length = array ? array.length : 0,
        resIndex = -1,
        result = [];

      while (++index < arr_length) {
        var value = array[index];

        if (value) {
          result[++resIndex] = value;
        }
      }

      return result;
    }
  },

  created() {
    if(this.contentType == 'fr') {
      this.getDatabase();
    }
    this.getContent();
    
  }
};
</script>
