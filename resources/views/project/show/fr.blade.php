<div class="tab-pane fade" id="pills-fr" role="tabpanel" aria-labelledby="pills-fr-tab">
  <div class="row no-gutters">
    <div class="col-lg-4">
      <input type="file" id="frFile" accept=".xlsx">
    </div>

  </div>
  <hr>
  <section class="tables" style="display: none">
    <div class="container-fluid" id="table">
      <div id="showMessage"></div>
      <div class="table-responsive" id="frTable">
        <table class="table table-striped" id="frTable">
          <thead>
            <tr>
              <th>No. <span style="color:red">*</span></th>
              <th>Description</th>
              <th>Inputs <span style="color:red">*</span></th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <div class="form-group" style="display:none" id="saveFr">
      <hr>
      <button id="saveFr" data-style="zoom-out" type="submit" class="btn btn-primary btn-lg ladda-button"><span class="ladda-label"><i class="fa fa-save"></i> SAVE</span></button>
  </div>
</div>

<div id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
  <div role="document" class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 id="modalFrHeader" class="modal-title"></h1>
        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        {{-- <button type="button" data-dismiss="modal" class="btn btn-secondary">Close</button> --}}
      </div>
    </div>
  </div>
</div>