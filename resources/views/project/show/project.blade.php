<div class="tab-pane fade show active" id="pills-project" role="tabpanel" aria-labelledby="pills-project-tab">
    <form id="saveProject">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="projectName">Project Name <i class="fa fa-asterisk" style="color:red"></i></label>
                    <input type="text" v-model="projectName" class="form-control" name="projectName" aria-describedby="pNameHelp" placeholder="Enter Your Project Name">
                </div>
            </div>
        </div>
        <div class="line"></div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="dbName">Database Name <i class="fa fa-asterisk" style="color:red"></i></label>
                    <input type="text" class="form-control" name="dbName" aria-describedby="dbNameHelp" placeholder="Enter Your Database Name">
                </div>
                <div class="form-group">
                    <label for="dbServer">Database Server <i class="fa fa-asterisk" style="color:red"></i></label>
                    <input type="text" class="form-control" name="dbServer" aria-describedby="dbHostHelp" placeholder="Enter Your Database Server">
                </div>
                <div class="form-group">
                    <label for="dbPort">Database Port <i class="fa fa-asterisk" style="color:red"></i></label>
                    <input type="number" class="form-control" name="dbPort" aria-describedby="dbPortHelp" placeholder="Enter Your Database Port"
                        value="1433">
                </div>
                <div class="form-group">
                    <label for="dbTypeSqlSrv">Database Type <i class="fa fa-asterisk" style="color:red"></i></label>
                    <div class="i-checks">
                        <input id="dbTypeSqlSrv" type="radio" value="sqlsrv" name="dbType" class="radio-template">
                        <label for="dbTypeSqlSrv"><small>SQL SERVER</small></label>
                    </div>
                    <div class="i-checks">
                        <input id="dbTypeMySql" type="radio" name="dbType" disabled="" value="mysql" class="radio-template">
                        <label for="dbTypeMySql"><small>MySQL</small></label>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="dbUsername">Username <i class="fa fa-asterisk" style="color:red"></i></label>
                    <input type="text" class="form-control" name="dbUsername" aria-describedby="dbUsereHelp" placeholder="Enter Your Username to Connect this Database.">
                </div>
                <div class="form-group">
                    <label for="dbPassword">Password <i class="fa fa-asterisk" style="color:red"></i></label>
                    <input type="password" class="form-control" name="dbPassword" aria-describedby="dbPasswordHelp" placeholder="Enter Your Password to Connect this password.">                                            {{-- <small id="dbPasswordHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>                                            --}}
                </div>
            </div>
        </div>
        <div id="showMessage"></div>
        <div class="line"></div>
        <div class="form-group">
            <button id="saveProjectBtn" data-style="zoom-out" type="submit" class="btn btn-primary btn-lg ladda-button"><span class="ladda-label"><i class="fa fa-save"></i> SAVE</span></button>
        </div>
    </form>
</div>