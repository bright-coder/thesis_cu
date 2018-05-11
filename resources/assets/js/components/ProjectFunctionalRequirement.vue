<template>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4>
                    <i class="fas fa-list-ul"></i>&nbsp;&nbsp;{{ this.projectName }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="customFile" ref="file" @change="this.readFileName">
                            <label class="custom-file-label" for="customFile">{{ this.filename }}</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary" @click="this.readFile">Read file</button>
                    </div>
                </div>
                
                <div class="row" v-if="this.frs.length > 0">
                    <div class="col-md-12"><hr></div>
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import XLSX from "xlsx";
export default {
  name: "project-functional-requirement",
  props: ["accessToken", "projectName"],
  data() {
    return {
      content: [],
      filename: "Choose file .xlsx",

    };
  },
  methods: {
    getFrs() {},
    readFileName() {
      if (this.$refs.file.files.length > 0) {
        this.$data.filename = this.$refs.file.files[0].name;
      }
    },
    readFile() {
      if (this.$refs.file.files.length > 0) {
        var reader = new FileReader();
        var vm = this;
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
            vm.readFrFromExcel(listOfSheet);
          }
        };
        reader.readAsArrayBuffer(this.$refs.file.files[0]);
      }
    },
    readFrFromExcel(frList) {
      var vm = this;
      $.each(frList, function(index, fr) {
        var no = vm.isKeyExist(fr, 0, 1) ? fr[0][1] : undefined;
        var description = vm.isKeyExist(fr, 1, 1) ? fr[1][1] : undefined;
        var inputList = [];
        for (var i = 4; i < fr.length; ++i) {
          inputList.push({
            name: 0 in fr[i] ? fr[i][0] : "",
            dataType: 1 in fr[i] ? fr[i][1] : "",
            length: 2 in fr[i] ? fr[i][2] : "",
            precision: 3 in fr[i] ? fr[i][3] : "",
            scale: 4 in fr[i] ? fr[i][4] : "",
            default: 5 in fr[i] ? fr[i][5] : "",
            nullable: 6 in fr[i] ? fr[i][6] : "",
            unique: 7 in fr[i] ? fr[i][7] : "",
            min: 8 in fr[i] ? fr[i][8] : "",
            max: 9 in fr[i] ? fr[i][9] : "",
            columnName: 10 in fr[i] ? fr[i][10] : "",
            tableName: 11 in fr[i] ? fr[i][11] : ""
          });
        }
        vm.frs.push({
          no: no,
          desc: description,
          inputs: inputList.length > 0 ? inputList : undefined
        });
      });
      console.log(this.frs);
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
    }
  },

  created() {
    this.getFrs();
  }
};
</script>
