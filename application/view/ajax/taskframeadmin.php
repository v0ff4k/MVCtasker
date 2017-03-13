<div class="container-fluid taskframeadmin">
    <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 main">
        <h3 class="text-primary"> Manage Task List
            <button type="button" name="loadAdminListBtn"
                    id="loadFreshListAdmin" class="btn btn-default btn-xs"
                    onclick="loadTaskListAdmin();"><i class="fa fa-refresh"></i></button>
        </h3><button onclick="logout();" type="button"
                     name="logOutAdminBtn" class="btn btn-info btn-sm pull-right">logout<i class="fa fa-share"></i></button><hr />

        <table class="table table-striped table-bordered table-responsive tablesorter" id="myTable2">
            <thead>
            <tr><th>Image Name</th><th>Username</th><th>E-mail</th><th>Task content</th><th>Created</th><th>Status</th><th>Action</th></tr>
            </thead>

            <tbody id="task-list-admin">
            <!--tr>
                <td><div>The image name</div></td>
                <td><div>The user name</div></td>
                <td><div>The e-mail</div></td>
                <td> <div> The task description </div> </td>
                <td>date added</td>
                <td> <div>task status</div> </td>
                <td style="width: 5%;"><button><i class="btn-danger fa fa-times"></i></button>
                </td>
            </tr-->
            </tbody>
        </table>
    </section>
</div>