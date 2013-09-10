
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class=""> <!--<![endif]-->
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> 创建数据 </title>
  <meta name="keywords" content="" />
  <meta name="description" content="" />
      <link href="/assets/libs/gallery2/bootstrap/3.0.0/css/bootstrap.css?2" rel="stylesheet" />
    <link rel="stylesheet" media="screen" href="/assets/libs/bootstrap-rc/3.0.0/css/bootstrap-extends.css?2" />
    <link rel="stylesheet" media="screen" href="/assets/css/common.css?2" />
    <link rel="stylesheet" media="screen" href="/bundles/topxiaweb/css/web.css?2" />

    <!--[if lt IE 9]>
    <script src="/assets/libs/bootstrap/3.0.0/html5shiv.js?2"></script>
    <script src="/assets/libs/bootstrap/3.0.0/respond.min.js?2"></script>
  <![endif]-->

    <style>
    .pact {
box-shadow: 5px 5px 5px #f7f7f7 inset;
border: 1px solid #bdbcbc;
width: 670px;
height: 350px;
padding: 10px;
overflow: hidden;
display: block;
overflow-y: scroll;
margin: 0 auto;
font-size: 12px;
line-height: 1.5;
margin-bottom: 22px;
outline: none;
}

.step li {
float: left;
height: 60px;
line-height: 60px;
width: 33%;
text-align: center;
font-size: 14px;
color: #6f7885;
font-weight: 700;
}
    </style>

</head>

<body>

<div class="container">
    
  <div class="es-row-wrap">

      <div class="row">

        <div class="col-lg-9">
          
            <div class="es-box">

          <div class="es-box-heading">
            <h1>环境检测</h1>
          </div>

          <div class="step">
            <ul>
              <li > <em>1</em> 检测环境
              </li>

              <li > <em>2</em> 创建数据
              </li>

              <li > <em>3</em> 完成安装
              </li>
            </ul>
          </div>

          <div class="form">

          <form class="form-horizontal" id="create-data-form" action="./createData.php" role="form" action="" method="post">
                  
            <label> 数据库信息</label>

            <div class="form-group">
              <label for="dbhost" class="col-sm-2 control-label">数据库服务器：</label>
              <div class="col-sm-10">
                <input type="text" class="form-control input" id="dbhost" name="dbhost" value="127.0.0.1" placeholder="数据库服务器地址，一般为127.0.0.1">
                <p class="help-block"><span class="text-danger"></span></p>
              </div>
            </div>

            <div class="form-group">
              <label for="dbuser" class="col-sm-2 control-label">数据库用户名：</label>
              <div class="col-sm-10">
                <input type="text" id="dbuser" name="dbuser" class="form-control input" value="root" placeholder="数据库用户名">
                <p class="help-block"><span class="text-danger"></span></p>
              </div>
            </div>

            <div class="form-group">
              <label for="dbpw" class="col-sm-2 control-label">数据库密码：</label>
              <div class="col-sm-10">
                <input type="password" class="form-control input" id="dbpw" name="dbpw">
                <p class="help-block"><span class="text-danger"></span></p>
              </div>
            </div>

            <div class="form-group">
              <label for="dbname" class="col-sm-2 control-label">数据库名：</label>
              <div class="col-sm-10">
                <input type="text" id="dbname" name="dbname" class="form-control input">
                <p class="help-block"><span class="text-danger"></span></p>
              </div>
            </div> 

            <div class="tac">
              <button type="submit" class="btn btn-primary btn-lg">创建数据</button>
            </div>

            </form>

          </div>

          <hr>

          </div>

        </div>

      </div>

  </div>

</div>

</body>

</html>