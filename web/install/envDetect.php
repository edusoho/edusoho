
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class=""> <!--<![endif]-->
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> EDUSOHO -  Powered by EDUSOHO</title>
  <meta name="keywords" content="" />
  <meta name="description" content="" />
      <link href="/assets/libs/gallery2/bootstrap/3.0.0/css/bootstrap.css?2" rel="stylesheet" />
    <link rel="stylesheet" media="screen" href="/assets/css/common.css?2" />
    <link rel="stylesheet" media="screen" href="/bundles/topxiaweb/css/web.css?2" />

    <!--[if lt IE 9]>
    <script src="/assets/libs/bootstrap/3.0.0/html5shiv.js?2"></script>
    <script src="/assets/libs/bootstrap/3.0.0/respond.min.js?2"></script>
  <![endif]-->

</head>

<body>
<?php
$allowNext = 'yes';
?>
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
          
          <ul class="nav nav-pills nav-justified" style="font-weight:900;font-size:150%">
            <li class="active disabled"><a >1 环境检测</a></li>
            <li class="disabled"><a>2 创建数据库</a></li>
            <li class="disabled"><a>3 初始化系统</a></li>
            <li class="disabled"><a>4 进入首页</a></li>
          </ul>
          <hr>

          <div class="server">

            <table  class="table table-hover table-bordered">
              <tbody>
                <tr>
                  <td><h1>环境检测</h1></td>
                  <td width="25%"><h1>推荐配置</h1></td>
                  <td width="25%"><h1>当前状态</h1></td>
                  <td width="25%"><h1>最低要求</h1></td>
                </tr>
                <tr>
                  <td>操作系统</td>
                  <td>类UNIX</td>
                  <td>
                    <strong>
                      <?php 
                        if(PHP_OS == 'Linux' || PHP_OS == 'Darwin'){
                      ?>
                      <p class="text-success">√ Linux / Mac OS X </p>
                      <?php
                        } else {
                      ?>
                      <p class="text-danger">X 检测失败</p>
                      <?php
                          $allowNext = 'no';
                        }
                      ?>
                    </strong>
                  </td>
                  <td>Linux </td>
                </tr>
                <tr>
                  <td>PHP版本</td>
                  <td>5.3.17</td>
                  <td>
                    <strong>
                      <?php
                      if(version_compare(PHP_VERSION, '5.3.0') >= 0){
                      ?>
                       <p class="text-success">√ <?php  echo PHP_VERSION; ?> </p>
                     <?php 
                      } else {
                        ?>
                        <p class="text-danger">X <?php  echo PHP_VERSION; ?> </p>
                      <?php 
                        $allowNext = 'no';
                      }
                      ?>
                    </strong>
                  </td>
                  <td>5.3.0</td>
                </tr>
                <tr>
                  <td>MySQL版本（client）</td>
                  <td>5.4.x</td>
                  <td>
                    <strong>
                   <?php
                     if (function_exists('mysqli_get_client_info')){
                            if (version_compare(mysql_get_client_info(), '5.0.0') >= 0) {
                    ?>
                    <p class="text-success">√ <?php  echo mysql_get_client_info(); ?> </p>
                    <?php
                          } else {
                             $allowNext = 'no';
                    ?>
                    <p class="text-danger">x <?php  echo mysql_get_client_info(); ?> </p>
                    <?php
                          }
                    } else {
                             $allowNext = 'no';
                   ?>
                    <p class="text-danger">X 尚未安装MySQL客户端 </p>
                    <?php } ?>
                    </strong>
                  </td>
                  <td>5.0.0</td>
                </tr>
                <tr>
                  <td>PDO_MySQL</td>
                  <td>必须</td>
                  <td>
                    <strong>
                   <?php
                   if (extension_loaded('pdo_mysql')){
                    ?>
                    <p class="text-success">√已安装</p>
                    <?php } else { ?>
                    <p class="text-danger">X尚未安装MySQL_PDO</p>
                    <?php $allowNext = 'no'; } ?>
                    </strong>
                  </td>
                  <td>必须</td>
                </tr>
                <tr>
                  <td>附件上传</td>
                  <td>20MB</td>
                  <td>
                    <strong>
                   <?php
                   if(ini_get('upload_max_filesize') >= 2){
                    ?>
                     <p class="text-success">√<?php echo ini_get('upload_max_filesize'); ?></p>
                   <?php } else { ?>
                     <p class="text-danger">X<?php echo ini_get('upload_max_filesize'); ?></p>
                    <?php
                    $allowNext = 'no'; }
                    ?>
                  </strong>
                  </td>
                  <td>2MB</td>
                </tr>
                <tr>
                  <td>磁盘空间</td>
                  <td>>1G</td>
                  <td>
                    <strong>
                      <?php
                      if(intval(disk_free_space('/')/(1024*1024)) > 50){
                        ?>
                     <p class="text-success">√<?php echo intval(disk_free_space('/')/(1024*1024)).'MB'; ?></p>
                      <?php } else { ?>
                     <p class="text-danger">X<?php echo intval(disk_free_space('/')/(1024*1024)).'MB'; ?></p>
                        <?php $allowNext = 'no';  } ?>
                    </strong>
                  </td>
                  <td>50M</td>
                </tr>
              </tbody>
            </table>

            <table class="table table-hover table-bordered">
              <tbody>
                <tr>
                  <td class="td1">目录、文件权限检查</td>
                  <td class="td1" width="25%">当前状态</td>
                  <td class="td1" width="25%">所需状态</td>
                </tr>
                
                <tr>
                  <td>app/config/parameters.yml</td>
                  <td>
                    <strong>
                    <?php
                    $file = "/var/www/edusoho/app/config/parameters.yml";
                    if (is_executable($file) && is_writable($file) && is_readable($file)) {
                        echo "<p class='text-success'>√可写</p>";
                    } else {
                        echo "<p class='text-danger'>X不可写</p>";
                        $allowNext = 'no';
                    }
                    ?>
                    </strong>
                    </td>
                  <td>可写</td>
                </tr>

                <tr>
                  <td>app/data/udisk</td>
                  <td>
                    <strong>
                     <?php
                    $file = "/var/www/edusoho/app/data/udisk";
                    if (is_executable($file) && is_writable($file) && is_readable($file)) {
                      echo "<p class='text-success'>√可写</p>";
                    } else {
                      echo "<p class='text-danger'>X不可写</p>";
                        $allowNext = 'no';
                    }
                    ?>
                    </strong>
                    </td>
                  <td>可写</td>
                </tr>
                <tr>
                  <td>app/data/private_files</td>
                  <td>
                   <strong>
                   <?php
                    $file = "/var/www/edusoho/app/data/private_files";
                    if (is_executable($file) && is_writable($file) && is_readable($file)) {
                      echo "<p class='text-success'>√可写</p>";
                    } else {
                      echo "<p class='text-danger'>X不可写</p>";
                        $allowNext = 'no';
                    }
                    ?>
                    </strong>
                  </td>
                  <td>可写</td>
                </tr>

                <tr>
                  <td>web/files</td>
                  <td>
                    <strong>
                   <?php
                    $file = "/var/www/edusoho/web/files";
                    if (is_executable($file) && is_writable($file) && is_readable($file)) {
                      echo "<p class='text-success'>√可写</p>";
                    } else {
                      echo "<p class='text-danger'>X不可写</p>";
                        $allowNext = 'no';
                    }
                    ?>
                    </strong>
                  </td>
                  <td>可写</td>
                </tr>

                <tr>
                  <td>web/install</td>
                  <td>
                    <strong>
                    <?php
                    $file = "/var/www/edusoho/web/install";
                    if (is_executable($file) && is_writable($file) && is_readable($file)) {
                      echo "<p class='text-success'>√可写</p>";
                    } else {
                      echo "<p class='text-danger'>X不可写</p>";
                        $allowNext = 'no';
                    }
                    ?>
                    </strong>
                  </td>
                  <td>可写</td>
                </tr>

                <tr>
                  <td>app/cache</td>
                  <td>
                    <strong>
                    <?php
                    $file = "/var/www/edusoho/app/cache";
                    if (is_executable($file) && is_writable($file) && is_readable($file)) {
                       echo "<p class='text-success'>√可写</p>";
                    } else {
                      echo "<p class='text-danger'>X不可写</p>";
                        $allowNext = 'no';
                    }
                    ?>
                    </strong>
                  </td>
                  <td>可写</td>
                </tr>

                <tr>
                  <td>app/logs</td>
                  <td>
                     <strong>
                    <?php
                    $file = "/var/www/edusoho/app/logs";
                    if (is_executable($file) && is_writable($file) && is_readable($file)) {
                      echo "<p class='text-success'>√可写</p>";
                    } else {
                      echo "<p class='text-danger'>X不可写</p>";
                        $allowNext = 'no';
                    }
                    ?>
                    </strong>
                  </td>
                  <td>可写</td>
                </tr>

              </tbody>
            </table>

          </div>

          <div class="next" style="text-align:center">
              <?php if($allowNext == 'yes'){ ?>
              <a href="./dataBasepage.php" class="btn btn-primary btn-lg" role="button" >下一步</a>
              <?php } elseif ($allowNext == 'no'){ ?>
                <h3 class="text-warning"> 不好意思，安装环境检测没有通过，请正确设置环境之后，重新刷新检测！</h3>
              <?php } ?>
          </div>

          </div>

        </div>

      </div>

  </div>

</div>

</body>

</html>