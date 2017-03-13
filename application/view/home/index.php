<!-- HOME/index -->
<div class="tab-content">

    <div role="tabpanel" class="container-fluid tab-pane fade in active" id="create">
        <section class="col col-xs-12 col-sm-6 col-md-8 col-lg-6 white">
            <h3 class="text-primary">Create a task </h3><hr>
            <form id="create-task-new" action="/ajax/create" method="post">
                <div class="form-group">
                    <label for="name" class="col-md-2 control-label">Name</label>
                    <div class="col-md-10">
                        <input name="name" class="form-control" id="name" type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-md-2 control-label">E-Mail</label>
                    <div class="col-md-10">
                        <input name="email" class="form-control" id="email" type="email">
                    </div>
                </div>
                <div class="form-group">
                    <label for="image" class="col-md-2 control-label">Image of task</label>
                    <div class="col-md-10 file_input_div">
                        <div class="pull-left">
                            <label class="label-lower image_input_button mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-js-ripple-effect mdl-button--colored">
                                <i class="material-icons">file_upload</i>
                                <input name="image" id="file_input_file" class="form-control" type="file" />
                            </label>
                        </div>
                        <div id="file_input_text_div" class="mdl-textfield mdl-js-textfield textfield-demo">
                            <input class="form-control" type="text" disabled readonly id="file_input_text" />
                            <label class="mdl-textfield__label" for="file_input_text"></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description" class="col-md-2 control-label">Description</label>
                    <div class="col-md-10">
                        <textarea class="form-control" rows="3" name="description" id="description"></textarea>
                    </div>
                </div>
                <button type="submit" name="createBtn" class="btn btn-success pull-right">
                    Add Task <i class="fa fa-plus"></i></button>
                <button type="reset" name="resetBtn" class="btn btn-info pull-right">
                    Reset Form <i class="fa fa-remove"></i></button>
            </form>
        </section><div class="visible-xs-block">&nbsp;</div>
        <section class="col col-xs-12 col-sm-offset-1 col-sm-5 col-md-offset-1 col-md-3 col-lg-offset-1 col-lg-5 white"><p>Feel free to add new task.<br/>
                After you add new task, you can wiew it after moderation.</p></section>
    </div>

    <div role="tabpanel" class="container-fluid admin-wrap tab-pane fade" id="admin">
        <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 white">
            <h3 class="text-primary">Manage tasks </h3><hr>
            <form id="admin-login" action="/ajax/indexadmin" method="post">
                <div class="form-group">
                    <label for="login" class="col-md-2 control-label">Login</label>
                    <div class="col-md-10">
                        <input name="login" class="form-control" id="login" type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-md-2 control-label">Password</label>
                    <div class="col-md-10">
                        <input name="password" class="form-control" id="password" type="password">
                    </div>
                </div>
                <button type="submit" name="loginBtn" class="btn btn-success pull-right">
                    login <i class="fa fa-ok"></i></button>
                <button type="reset" name="resetBtn" class="btn btn-info pull-right">
                    Reset Form <i class="fa fa-remove"></i></button>
            </form>
        </section>
    </div>

</div>
