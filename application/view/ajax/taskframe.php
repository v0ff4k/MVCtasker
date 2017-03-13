<div class="container-fluid taskframe">
    <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 main">
        <h3 class="text-primary"> Public Task List
            <button type="button" name="loadListBtn" id="loadFreshList" class="btn btn-default btn-xs"
                    onclick="loadTaskList();"><i class="fa fa-refresh"></i></button>
        </h3><hr />

        <table class="table table-striped table-bordered table-responsive tablesorter" id="myTable">
            <thead>
            <tr><th>Image Name</th><th>Username</th><th>Task content</th><th>Created</th></tr>
            </thead>
            
            <tbody id="task-list">
            </tbody>
        </table>
    </section>
</div>