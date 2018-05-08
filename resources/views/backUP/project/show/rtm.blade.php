<div class="tab-pane fade" id="pills-rtm" role="tabpanel" aria-labelledby="pills-tc-tab">
    <div class="row no-gutters">
        <div class="col-lg-4">
            <input type="file" id="rtmFile" accept=".xlsx">
        </div>
    </div>
    <hr>
    <section class="tables" style="display: none">
        <div class="container-fluid" id="table">
            <div id="showMessage"></div>
            <div class="table-responsive" id="rtmTable">
                <table class="table table-striped" id="rtmTable">
                    <thead>
                        <tr>
                            <th class="blue">Functional Requirement</th>
                            <th >Test Cases</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <div class="form-group" style="display:none" id="saveRtm">
        <hr>
        <button id="saveRtm" data-style="zoom-out" type="submit" class="btn btn-primary btn-lg ladda-button"><span class="ladda-label"><i class="fa fa-save"></i> SAVE</span></button>
    </div>
    {{-- <div class="strike">
        <span>OR</span>
    </div> --}}
</div>