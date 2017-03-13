<!-- exactly without #admin wrapper ajax/adminform -->
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
