
<?php
require_once('./header.php');
?>

<body>

<div class="container" style="width:940px">
    
  <div class="es-row-wrap">

      <div class="row">

        <div class="col-lg-12">
          
            <div class="es-box">

             <div class="setup-wizard">
              <span class="pull-left text-primary" style="font-weight:900;font-size:250%">Edusoho</span>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <span class="text-success" style="font-weight:900;font-size:250%">安装向导</span>
            </div>
            <hr>

          <ul class="nav nav-pills nav-justified">
            <li class="disabled"><a style="color:green">1 环境检测</a></li>
            <li class="active disabled"><a>2 创建数据库</a></li>
            <li class="disabled"><a>3 初始化系统</a></li>
            <li class="disabled"><a>4 完成安装</a></li>
          </ul>
          <hr>
          <form class="form-horizontal" id="create-data-form" action="./createData.php" role="form" action="" method="post">

            <div class="form-group">
              <label for="dbhost" class="col-sm-4 control-label">数据库服务器</label>
              <div class="col-sm-5">
                <input type="text" class="form-control input" id="dbhost" name="dbhost" value="127.0.0.1" placeholder="数据库服务器地址，一般为127.0.0.1">
                <p class="help-block"><span class="text-danger"></span></p>
              </div>
            </div>

            <div class="form-group">
              <label for="dbuser" class="col-sm-4 control-label">数据库用户名</label>
              <div class="col-sm-5">
                <input type="text" id="dbuser" name="dbuser" class="form-control input" value="root" placeholder="数据库用户名">
                <p class="help-block"><span class="text-danger"></span></p>
              </div>
            </div>

            <div class="form-group">
              <label for="dbpw" class="col-sm-4 control-label">数据库密码</label>
              <div class="col-sm-5">
                <input type="password" class="form-control input" id="dbpw" name="dbpw">
                <p class="help-block"><span class="text-danger"></span></p>
              </div>
            </div>

            <div class="form-group">
              <label for="dbname" class="col-sm-4 control-label">数据库名</label>
              <div class="col-sm-5">
                <input type="text" id="dbname" name="dbname" value="edusoho" class="form-control input">
                <p class="help-block"><span class="text-danger"></span></p>
              </div>
            </div> 
            <?php
            if(isset($_GET['dataBaseExist']) && $_GET['dataBaseExist'] == 'yes'){
            ?>
            <h3 style="text-align:center" class="text-danger"> 
              数据库已经存在，创建失败，请在备份并删除数据库之后重新安装！
            </h3>
            <?php } ?>
            <div class="tac">
              <button type="submit" id="create-db" class="btn btn-primary btn-lg">创建数据库</button>
            </div>

          </form>

          </div>

        </div>

      </div>

  </div>

</div>
<script type="text/javascript">
  (function (btn) {
      function $(str) { return document.getElementById(str); }
      $(btn).onclick = function () {
          this.innerHTML = "正在创建数据库...";
          this.disabled = true;
          $("create-data-form").submit();
      }
  })("create-db");
</script>

</body>

</html>