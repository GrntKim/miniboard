<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script 
        src="https://code.jquery.com/jquery-4.0.0.min.js" 
        integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" 
        crossorigin="anonymous">
    </script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        ul {
            list-style: none;
            display: flex;
            padding: 0;
            margin: 0;
            gap: 0.5em;
        }

        a {
            text-decoration: none;
            color: black;
        }

        header {
            padding: 1em;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content {
            padding: 1em;
            flex: 1;
        }

        footer {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1em;
        }
    </style>
    <title>CMS</title>
</head>
<body>
    <header>
        <h1 class="site-logo">CMS</h1>
        <ul class="site-menus">
            <li><a href="#">posts</a></li>
            <li><a href="#">write</a></li>
            <li><a href="#">register</a></li>
        </ul>
    </header>
    <div class="content">
        hello, world!
    </div>
    <footer>
        Copyright &copy; <script>document.write(new Date().getFullYear());</script> JMK
    </footer>
    <script>
    </script>
</body>
</html>