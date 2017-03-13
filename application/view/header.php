<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags -->
    <meta content='text/html;charset=UTF-8' http-equiv='Content-Type'/>
    <meta content='v0ff, design' name='keywords'/>
    <meta content='Image arrarange' name='description'/>
    <meta http-equiv="imagetoolbar" content="no"/>
    <meta content='7 days' name='revisit-after'/>
    <meta content='v0ff' name='author'/>
    <meta content='Vladimir' name='copyright'/>
    <meta content='handy hands' name='generator'/>
    
    <title><?php echo 'Tasker:: '.((isset($pageTitle)) ? $pageTitle : '')?></title>
    
    <!-- fonts -->
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700"/>
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/icon?family=Material+Icons"/>
    <!-- colors only for tables, from table sorter plugin -->
    <link rel="stylesheet" type="text/css" href="//tablesorter.com/themes/blue/style.css"/>
    
    <!--script src="//cdnjs.cloudflare.com/ajax/libs/material-design-icons/3.0.1/maps/svg/production/ic_beenhere_24px.svg"></script-->
    
    <!-- Bootstrap cdn -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
          rel="stylesheet" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7"
          crossorigin="anonymous"/>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css"/>

    <!-- Material Design js + css,  umd not iife-->
    <link rel="stylesheet" href="css/bootstrap-material-design.css"/>

    <!-- Custom css -->
    <link rel="stylesheet" href="css/core.css"/>
    <style>
        .panel-resizable {
            resize: vertical;
            overflow: auto;
        }
        img.intooltip {
            height: 140px;
            width: auto;
        }

    </style>
</head>
<body>
<!-- Navigation -->
<div class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target=".navbar-responsive-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">Tasker</a>
        </div>
        <div class="navbar-collapse collapse navbar-responsive-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#create" aria-controls="create" role="tab" data-toggle="tab"><i class="fa fa-plus"></i>&nbsp; Create Task</a></li>
                <li><a href="#admin" aria-controls="admin" role="tab" data-toggle="tab"><i class="fa fa-user"></i>&nbsp; Admin</a></li>
            </ul>
        </div>
    </div>
</div>