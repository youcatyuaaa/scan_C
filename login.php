<?php
// 初始化 session
session_start();

// 检查用户是否已经登录，如果是，则重定向到首页
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

// 引入配置文件和功能文件
require_once "config.php";

// 定义变量并初始化为空
$username = $password = "";
$username_err = $password_err = "";

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 检查用户名是否为空
    if (empty(trim($_POST["username"]))) {
        $username_err = "请输入用户名.";
    } else {
        $username = trim($_POST["username"]);
    }

    // 检查密码是否为空
    if (empty(trim($_POST["password"]))) {
        $password_err = "请输入密码.";
    } else {
        $password = trim($_POST["password"]);
    }

    // 验证输入是否有误
    if (empty($username_err) && empty($password_err)) {
        // 准备 SQL 查询语句
        $sql = "SELECT id, username, password FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // 绑定变量到预处理语句作为参数
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // 设置参数
            $param_username = $username;
            
            // 尝试执行预处理语句
            if (mysqli_stmt_execute($stmt)) {
                // 存储结果
                mysqli_stmt_store_result($stmt);
                
                // 检查用户名是否存在，如果是，则验证密码
                if (mysqli_stmt_num_rows($stmt) == 1) {                    
                    // 绑定结果到变量
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // 密码正确，启动新会话，并保存用户数据
                            session_start();
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            

                            // 重定向用户到首页
                            header("location: index.php");
                        } else {
                            // 密码不正确，则显示错误消息
                            $password_err = "密码不正确.";
                        }
                    }
                } else {
                    // 用户名不存在，则显示错误消息
                    $username_err = "未找到此用户名.";
                }
            } else {
                echo "出现了一些问题，请稍后再试.";
            }

            // 关闭语句
            mysqli_stmt_close($stmt);
        }
    }
    
    // 关闭连接
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录</title>
    <!-- 引入所需的 CSS 文件 -->
    <link rel="stylesheet" href="styles.css">
    <style>
        /* 添加额外的样式 */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .form-group input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>登录</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>用户名：</label>
                <input type="text" name="username" value="<?php echo $username; ?>">
                <span class="error"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>密码：</label>
                <input type="password" name="password" value="<?php echo $password; ?>">
                <span class="error"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" value="登录">
            </div>
            <p>还没有账号？<a href="register.php">注册一个新账号</a></p>
        </form>
    </div>
</body>
</html>
