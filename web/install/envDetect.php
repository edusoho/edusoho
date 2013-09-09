
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
<?php
$allowNext = 'yes';
?>
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
                        if(PHP_OS == 'Linux'){
                            echo "√".PHP_OS;
                        } else {
                            echo "X".PHP_OS;
                            $allowNext = 'no';
                        }
                      ?>
                    </strong>
                  </td>
                  <td>Linux </td>
                </tr>
                <tr>
                  <td>PHP版本</td>
                  <td>5.3.x</td>
                  <td>
                    <strong>
                      <?php
                      if(version_compare(PHP_VERSION, '5.3.17') >= 0){
                        echo "√".PHP_VERSION;
                      } else {
                        echo "X".PHP_VERSION;
                        $allowNext = 'no';
                      }
                      ?>
                    </strong>
                  </td>
                  <td>5.3.17</td>
                </tr>
                <tr>
                  <td>MySQL版本（client）</td>
                  <td>5.x.x</td>
                  <td>
                    <strong>
                   <?php
                     if (function_exists('mysqli_get_client_info')){
                            if (version_compare(mysql_get_client_info(), '5.0.0') >= 0) {
                               echo "√".mysql_get_client_info();
                            } else {
                               echo "X".mysql_get_client_info();
                               $allowNext = 'no';
                            }

                    } else {
                               echo "X 尚未安装MySQL客户端";
                               $allowNext = 'no';
                    }
                   ?>
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
                    echo "√已安装";
                   } else {
                    echo "X尚未安装MySQL_PDO";
                    $allowNext = 'no';
                   }
                   ?>
                    </strong>
                  </td>
                  <td>必须</td>
                </tr>
                <tr>
                  <td>附件上传</td>
                  <td>2M</td>
                  <td>
                    <strong>
                   <?php
                   if('2M' == ini_get('upload_max_filesize')){
                    echo '√'.ini_get('upload_max_filesize');
                   } else{
                    echo 'X'.ini_get('upload_max_filesize');
                    $allowNext = 'no';
                   }
                   ?>
                  </strong>
                  </td>
                  <td>不限制</td>
                </tr>
                <tr>
                  <td>磁盘空间</td>
                  <td>50M</td>
                  <td>
                    <strong>
                      <?php
                      if(intval(disk_free_space('/')/(1024*1024)) > 50){
                        echo "√".intval(disk_free_space('/')/(1024*1024)).'MB';
                      } else {
                        echo "X".intval(disk_free_space('/')/(1024*1024)).'MB';
                        $allowNext = 'no';
                      }
                      ?>
                    </strong>
                  </td>
                  <td>50M</td>
                </tr>
              </tbody>
            </table>

            <hr>

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
                        echo "√可写";
                    } else {
                        echo "X不可写";
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
                        echo "√可写";
                    } else {
                        echo "X不可写";
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
                        echo "√可写";
                    } else {
                        echo "X不可写";
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
                        echo "√可写";
                    } else {
                        echo "X不可写";
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
                        echo "√可写";
                    } else {
                        echo "X不可写";
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
                        echo "√可写";
                    } else {
                        echo "X不可写";
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
                        echo "√可写";
                    } else {
                        echo "X不可写";
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

          <hr>

            <div>
                  <div class="next">
                      <?php if($allowNext == 'yes'){ ?>
                      <a href="./dataBasepage.php" class="btn btn-primary btn-lg" role="button" >下一步</a>
                      <?php } elseif ($allowNext == 'no'){ ?>
                        <p class="text-warning"> 不好意思，安装环境检测没有通过，请设置环境之后，重新刷新检测！</p>
                      <?php } ?>
                  </div>
            </div>

          </div>

        </div>

      </div>

  </div>

</div>

</body>

</html>